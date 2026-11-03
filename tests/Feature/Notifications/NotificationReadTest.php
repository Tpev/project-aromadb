<?php

use App\Models\User;
use Illuminate\Support\Str;

function storedNotification(User $owner, array $data = [], ?string $readAt = null)
{
    return $owner->notifications()->create([
        'id' => (string) Str::uuid(), 'type' => 'LegacyNotification',
        'data' => array_merge(['message' => 'Notification existante', 'url' => '/notifications'], $data),
        'read_at' => $readAt,
    ]);
}

beforeEach(function () {
    $this->owner = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $this->actingAs($this->owner);
});

test('opening a notification marks only that notification read and follows its stored link', function () {
    $opened = storedNotification($this->owner, ['url' => '/notifications?page=2']);
    $unopened = storedNotification($this->owner);
    $originalData = $opened->data;
    $this->post(route('notifications.open', $opened->id))->assertRedirect('/notifications?page=2');
    expect($opened->fresh()->read_at)->not->toBeNull()
        ->and($opened->fresh()->data)->toBe($originalData)
        ->and($unopened->fresh()->read_at)->toBeNull();
    $readAt = $opened->fresh()->read_at;
    $this->travel(1)->hour();
    $this->post(route('notifications.open', $opened->id))->assertRedirect('/notifications?page=2');
    expect($opened->fresh()->read_at->equalTo($readAt))->toBeTrue();
    $this->getJson(route('notifications.fetch'))->assertOk()->assertJsonPath('unreadCount', 1)
        ->assertJsonPath('notifications.0.id', $unopened->id);
});

test('opening a notification preserves historical http links', function () {
    $url = 'https://ancien-domaine.example.test/appointments/123?source=notification';
    $notification = storedNotification($this->owner, ['url' => $url]);
    $this->post(route('notifications.open', $notification->id))->assertRedirect($url);
});

test('legacy missing or unsafe links fall back to the notification list', function ($url) {
    $notification = storedNotification($this->owner, ['url' => $url]);
    $this->post(route('notifications.open', $notification->id))->assertRedirect(route('notifications.index'));
    expect($notification->fresh()->read_at)->not->toBeNull();
})->with([null, '', 'javascript:alert(1)', '//example.test', '/\\example.test', ['invalid']]);

test('reading one notification returns fresh json counts and retains ordinary form redirects', function () {
    $first = storedNotification($this->owner);
    $second = storedNotification($this->owner);
    $this->postJson(route('notifications.markAsRead', $first->id))->assertOk()
        ->assertJsonPath('unreadCount', 1)->assertJsonCount(1, 'notifications');
    $this->from(route('notifications.index'))->post(route('notifications.markAsRead', $second->id))
        ->assertRedirect(route('notifications.index'));
    $this->getJson(route('notifications.fetch'))->assertExactJson(['unreadCount' => 0, 'notifications' => []]);
});

test('marking all read returns json and changes only the current users unread records', function () {
    $first = storedNotification($this->owner);
    $second = storedNotification($this->owner);
    $old = storedNotification($this->owner, [], '2025-01-01 10:00:00');
    $foreign = storedNotification(User::factory()->create());
    $this->postJson(route('notifications.markAllAsRead'))->assertOk()
        ->assertExactJson(['unreadCount' => 0, 'notifications' => []]);
    expect($first->fresh()->read_at)->not->toBeNull()->and($second->fresh()->read_at)->not->toBeNull()
        ->and($old->fresh()->read_at->toDateTimeString())->toBe('2025-01-01 10:00:00')
        ->and($foreign->fresh()->read_at)->toBeNull()->and($this->owner->notifications()->count())->toBe(3);
    $this->from(route('notifications.index'))->post(route('notifications.markAllAsRead'))->assertRedirect(route('notifications.index'));
});

test('foreign and missing notification ids cannot be read or opened', function ($route) {
    $foreign = storedNotification(User::factory()->create());
    $this->postJson(route($route, $foreign->id))->assertNotFound();
    $this->postJson(route($route, (string) Str::uuid()))->assertNotFound();
    expect($foreign->fresh()->read_at)->toBeNull();
})->with(['notifications.open', 'notifications.markAsRead']);

test('notification endpoints require authentication', function () {
    $notification = storedNotification($this->owner);
    auth()->logout();
    $this->getJson(route('notifications.fetch'))->assertUnauthorized();
    foreach (['notifications.open', 'notifications.markAsRead'] as $route) {
        $this->postJson(route($route, $notification->id))->assertUnauthorized();
    }
    $this->postJson(route('notifications.markAllAsRead'))->assertUnauthorized();
    expect($notification->fresh()->read_at)->toBeNull();
});

test('fetch counts all unread records and returns only the newest five with opening actions', function () {
    for ($i = 1; $i <= 7; $i++) {
        $notification = storedNotification($this->owner, ['message' => 'Message '.$i]);
        $notification->forceFill(['created_at' => now()->subMinutes(8 - $i)])->save();
    }
    storedNotification($this->owner, [], now()->toDateTimeString());
    storedNotification(User::factory()->create());
    $response = $this->getJson(route('notifications.fetch'))->assertOk()
        ->assertJsonPath('unreadCount', 7)->assertJsonCount(5, 'notifications')
        ->assertJsonPath('notifications.0.data.message', 'Message 7');
    foreach ($response->json('notifications') as $notification) {
        expect($notification['open_url'])->toBe(route('notifications.open', $notification['id']))
            ->and($notification['read_at'])->toBeNull();
    }
    expect($this->owner->unreadNotifications()->count())->toBe(7);
});

test('the list and dropdown expose read on open forms and listing alone does not dismiss notifications', function () {
    $notification = storedNotification($this->owner, ['message' => '<img src=x onerror=alert(1)>']);
    $this->get(route('notifications.index'))->assertOk()
        ->assertSee(route('notifications.open', $notification->id), false)
        ->assertSee('data-notification-count', false)->assertSee('markAllNotificationsAsReadForm', false)
        ->assertSee('data-notification-read-state', false)->assertSee('&lt;img', false)
        ->assertDontSee('<img src=x', false);
    expect($notification->fresh()->read_at)->toBeNull();
    $this->post(route('notifications.open', $notification->id))->assertRedirect(route('notifications.index'));
    $this->get(route('notifications.index'))->assertOk()->assertDontSee('data-notification-count', false);
});
