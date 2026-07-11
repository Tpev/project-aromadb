<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationNode;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationAction;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Models\OfferJourneyContactActivity;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Mail\OfferJourneyMessageMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Throwable;

class OfferJourneyAutomationProcessor
{
    public function __construct(private readonly OfferJourneyMessageGuard $guard)
    {
    }

    public function process(int $runId): void
    {
        $run = OfferJourneyAutomationRun::query()->with([
            'automation.journey.user', 'version.nodes', 'journeyVersion.pages', 'contact',
        ])->find($runId);

        if (! $run || $run->status !== 'running' || $run->next_action_at?->isFuture()) {
            return;
        }

        $node = $run->version->nodes->firstWhere('node_key', $run->current_node_key);
        if (! $node) {
            $this->exit($run, 'invalid_node');
            return;
        }

        $config = $node->config_json ?? [];
        if (! ($config['is_enabled'] ?? false)) {
            $this->advance($run, $node);
            return;
        }

        $journey = $run->automation->journey;
        if ($journey->conversions()->where('offer_journey_contact_id', $run->contact->id)->where('status', 'confirmed')->exists()) {
            $this->exit($run, 'converted');
            return;
        }
        if ($journey->status === 'archived') {
            $this->exit($run, 'archived');
            return;
        }
        if ($journey->status !== 'published'
            || $run->automation->status !== 'active'
            || $run->version->status !== 'published'
            || ! app(OfferJourneyAccess::class)->automationAvailableFor($journey->user)) {
            $run->update(['next_action_at' => now()->addMinutes(15)]);
            return;
        }

        if ($node->type === 'end') {
            $this->exit($run, 'completed');
            return;
        }

        if ($node->type === 'wait') {
            $this->advance($run, $node);
            return;
        }

        if ($node->type === 'condition') {
            $this->followCondition($run, $node);
            return;
        }

        if ($node->type === 'action') {
            $this->executeAction($run, $node);
            return;
        }

        if ($node->type !== 'email') {
            $this->exit($run, 'unsupported_node');
            return;
        }

        $allowedAt = $this->nextAllowedAt($run);
        if ($allowedAt) {
            $run->update(['next_action_at' => $allowedAt]);
            return;
        }

        $category = ($config['category'] ?? 'marketing') === 'transactional' ? 'transactional' : 'marketing';
        $reason = $this->guard->reason($run, $category);

        if (in_array($reason, ['temporarily_paused', 'temporarily_disabled'], true)) {
            $run->update(['next_action_at' => now()->addMinutes(15)]);
            return;
        }

        if (in_array($reason, ['converted', 'archived', 'source_unavailable', 'contact_inactive'], true)) {
            $this->exit($run, $reason);
            return;
        }

        if ($reason) {
            $this->recordSkipped($run, $node, $category, $reason);
            $this->advance($run, $node);
            return;
        }

        $this->send($run, $node, $category);
    }

    private function send(OfferJourneyAutomationRun $run, OfferJourneyAutomationNode $node, string $category): void
    {
        $config = $node->config_json ?? [];
        $values = $this->variables($run);
        $subject = $this->render((string) $config['subject'], $values);
        $body = $this->render((string) $config['body'], $values);
        $key = 'oj:run:'.$run->id.':'.$node->node_key;
        $delivery = OfferJourneyMessageDelivery::query()->firstOrCreate([
            'idempotency_key' => $key,
        ], [
            'user_id' => $run->automation->journey->user_id,
            'offer_journey_id' => $run->automation->offer_journey_id,
            'offer_journey_contact_id' => $run->contact->id,
            'offer_journey_automation_run_id' => $run->id,
            'node_key' => $node->node_key,
            'category' => $category,
            'status' => 'sending',
            'recipient_email' => $run->contact->email,
            'subject' => $subject,
            'scheduled_at' => $run->next_action_at,
            'metadata' => ['automation_version_id' => $run->offer_journey_automation_version_id],
        ]);

        if (! $delivery->wasRecentlyCreated && $delivery->status !== 'retry_pending') {
            if (in_array($delivery->status, ['sent', 'skipped'], true)) {
                $this->advance($run, $node);
            }
            return;
        }

        try {
            $unsubscribeUrl = URL::signedRoute('offer-journeys.unsubscribe.show', ['contact' => $run->contact]);
            $sentMessage = Mail::to($run->contact->email)->send(new OfferJourneyMessageMail(
                $run->automation->journey->user,
                $subject,
                $body,
                $unsubscribeUrl,
                $category,
                $delivery->id
            ));
            $delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
                'provider_message_id' => $sentMessage?->getMessageId(),
            ]);
            OfferJourneyContactActivity::query()->create([
                'offer_journey_contact_id' => $run->contact->id,
                'offer_journey_id' => $run->automation->offer_journey_id,
                'type' => 'email_sent',
                'title' => 'Message envoyé : '.$subject,
                'metadata' => ['delivery_id' => $delivery->id, 'category' => $category],
                'occurred_at' => now(),
            ]);
            $this->advance($run, $node);
        } catch (Throwable $exception) {
            $delivery->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => $exception->getMessage()]);
            $run->update(['status' => 'failed', 'last_error' => $exception->getMessage()]);
            Log::error('Offer journey email failed.', ['run_id' => $run->id, 'delivery_id' => $delivery->id, 'exception' => $exception::class]);
        }
    }

    private function recordSkipped(OfferJourneyAutomationRun $run, OfferJourneyAutomationNode $node, string $category, string $reason): void
    {
        OfferJourneyMessageDelivery::query()->firstOrCreate([
            'idempotency_key' => 'oj:run:'.$run->id.':'.$node->node_key,
        ], [
            'user_id' => $run->automation->journey->user_id,
            'offer_journey_id' => $run->automation->offer_journey_id,
            'offer_journey_contact_id' => $run->contact->id,
            'offer_journey_automation_run_id' => $run->id,
            'node_key' => $node->node_key,
            'category' => $category,
            'status' => 'skipped',
            'recipient_email' => $run->contact->email ?: 'invalid@example.invalid',
            'subject' => (string) ($node->config_json['subject'] ?? 'Message non envoyé'),
            'scheduled_at' => $run->next_action_at,
            'skipped_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    private function advance(OfferJourneyAutomationRun $run, OfferJourneyAutomationNode $node): void
    {
        if (! $node->next_node_key) {
            $this->exit($run, 'completed');
            return;
        }

        $next = $run->version->nodes->firstWhere('node_key', $node->next_node_key);
        if (! $next) {
            $this->exit($run, 'invalid_next_node');
            return;
        }

        $scheduled = ($next->config_json['relative_delay'] ?? false)
            ? now()->addMinutes((int) ($next->config_json['delay_minutes'] ?? 0))
            : $run->started_at->copy()->addMinutes((int) ($next->config_json['delay_minutes'] ?? 0));
        $run->update([
            'current_node_key' => $next->node_key,
            'next_action_at' => $scheduled->isFuture() ? $scheduled : now(),
            'last_error' => null,
        ]);
    }

    private function followCondition(OfferJourneyAutomationRun $run, OfferJourneyAutomationNode $node): void
    {
        $config = $node->config_json ?? [];
        $matches = match ($config['condition_type'] ?? null) {
            'marketing_consent' => $run->contact->consents()->where('purpose', 'marketing_follow_up')->where('status', 'granted')->whereNull('withdrawn_at')->exists(),
            'converted' => $run->automation->journey->conversions()->where('offer_journey_contact_id', $run->contact->id)->where('status', 'confirmed')->exists(),
            'has_tag' => $run->contact->tags()->whereKey((int) ($config['value'] ?? 0))->exists(),
            'inactive_days' => $run->contact->last_activity_at?->lte(now()->subDays(max(1, (int) ($config['value'] ?? 30)))) ?? true,
            default => false,
        };
        $targetKey = $matches ? $node->yes_node_key : $node->no_node_key;
        if (! $targetKey) {
            $this->exit($run, 'condition_without_target');
            return;
        }
        $target = $run->version->nodes->firstWhere('node_key', $targetKey);
        if (! $target) {
            $this->exit($run, 'invalid_condition_target');
            return;
        }
        $run->update(['current_node_key' => $target->node_key, 'next_action_at' => now()]);
    }

    private function executeAction(OfferJourneyAutomationRun $run, OfferJourneyAutomationNode $node): void
    {
        $config = $node->config_json ?? [];
        $type = (string) ($config['action_type'] ?? '');
        $action = OfferJourneyAutomationAction::query()->firstOrCreate([
            'idempotency_key' => 'oj:run:'.$run->id.':action:'.$node->node_key,
        ], [
            'offer_journey_automation_run_id' => $run->id,
            'node_key' => $node->node_key,
            'action_type' => $type,
            'status' => 'processing',
            'payload_json' => ['value' => $config['value'] ?? null],
        ]);
        if (! $action->wasRecentlyCreated) {
            if ($action->status === 'executed') {
                $this->advance($run, $node);
            }
            return;
        }

        try {
            match ($type) {
                'add_tag' => $this->addTag($run, (int) ($config['value'] ?? 0)),
                'set_status' => $this->setStatus($run, (string) ($config['value'] ?? 'qualifying')),
                'create_task' => $run->contact->tasks()->create([
                    'user_id' => $run->automation->journey->user_id,
                    'offer_journey_id' => $run->automation->offer_journey_id,
                    'title' => (string) ($config['value'] ?: 'Recontacter ce contact'),
                    'priority' => 'normal',
                    'status' => 'open',
                    'due_at' => now(),
                ]),
                default => throw new \RuntimeException('Action non prise en charge.'),
            };
            $action->update(['status' => 'executed', 'executed_at' => now()]);
            $this->advance($run, $node);
        } catch (Throwable $exception) {
            $action->update(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => $exception->getMessage()]);
            $run->update(['status' => 'failed', 'last_error' => $exception->getMessage()]);
        }
    }

    private function addTag(OfferJourneyAutomationRun $run, int $tagId): void
    {
        $tag = OfferJourneyTag::query()->whereKey($tagId)->where('user_id', $run->automation->journey->user_id)->firstOrFail();
        $run->contact->tags()->syncWithoutDetaching([$tag->id]);
    }

    private function setStatus(OfferJourneyAutomationRun $run, string $status): void
    {
        if (! in_array($status, ['new', 'qualifying', 'contacted', 'converted', 'not_now'], true)) {
            throw new \RuntimeException('Statut de contact invalide.');
        }
        $run->contact->update(['status' => $status, 'last_activity_at' => now()]);
    }

    private function exit(OfferJourneyAutomationRun $run, string $reason): void
    {
        $run->update(['status' => 'completed', 'exit_reason' => $reason, 'next_action_at' => null, 'exited_at' => now()]);
    }

    private function nextAllowedAt(OfferJourneyAutomationRun $run): ?Carbon
    {
        $timezone = $run->automation->journey->timezone ?: 'Europe/Paris';
        $localNow = now($timezone);
        $start = $run->automation->quiet_hours_start ?: config('offer_journeys.quiet_hours.start', '20:00');
        $end = $run->automation->quiet_hours_end ?: config('offer_journeys.quiet_hours.end', '08:00');
        $time = $localNow->format('H:i');

        if ($start <= $end) {
            $isQuiet = $time >= $start && $time < $end;
            $allowed = $localNow->copy()->setTimeFromTimeString($end);
        } else {
            $isQuiet = $time >= $start || $time < $end;
            $allowed = $localNow->copy()->setTimeFromTimeString($end);
            if ($time >= $start) {
                $allowed->addDay();
            }
        }

        return $isQuiet ? $allowed->utc() : null;
    }

    private function variables(OfferJourneyAutomationRun $run): array
    {
        $journey = $run->automation->journey;
        $therapist = $journey->user;
        $version = $run->journeyVersion;
        $resourcePage = $version?->pages->first(function ($pageVersion) {
            $content = $pageVersion->content_json ?? [];

            return filled($content['resource_url'] ?? null) || ! empty($content['resource_file']);
        });
        $resourceUrl = $resourcePage?->content_json['resource_url'] ?? null;
        if (! $resourceUrl && ! empty($resourcePage?->content_json['resource_file'])) {
            $resourceUrl = URL::temporarySignedRoute(
                'offer-journeys.resources.download',
                now()->addMinutes((int) config('offer_journeys.resource_link_minutes', 10080)),
                ['pageVersion' => $resourcePage]
            );
        }

        return [
            '{{prenom}}' => $run->contact->first_name ?: 'bonjour',
            '{{offre}}' => $journey->name,
            '{{nom_praticien}}' => $therapist->company_name ?: trim(($therapist->first_name ?? '').' '.($therapist->last_name ?? '')),
            '{{lien_offre}}' => route('offer-journeys.public.show', ['therapist' => $therapist, 'journeySlug' => $journey->slug]),
            '{{lien_ressource}}' => $resourceUrl ?: route('offer-journeys.public.show', ['therapist' => $therapist, 'journeySlug' => $journey->slug]),
        ];
    }

    private function render(string $template, array $values): string
    {
        return str_replace(array_keys($values), array_values($values), $template);
    }
}
