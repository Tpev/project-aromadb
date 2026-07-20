<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountDataExportService;
use Illuminate\Console\Command;
use Throwable;

class ExportAccountData extends Command
{
    protected $signature = 'account:export
        {user : Identifiant du compte praticien}
        {--dry-run : Affiche les volumes sans creer d archive}
        {--confirm-email= : Adresse email exacte du compte, obligatoire pour creer l archive}
        {--chunk=500 : Nombre de lignes traitees par lot}';

    protected $description = 'Exporte les donnees d un compte praticien dans une archive privee, sans donnees des autres comptes.';

    public function handle(AccountDataExportService $exporter): int
    {
        $user = User::query()->find((int) $this->argument('user'));

        if (! $user) {
            $this->error('Compte introuvable.');

            return self::FAILURE;
        }

        $this->table(
            ['ID', 'Nom', 'Email'],
            [[$user->id, $user->name, $user->email]]
        );

        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! hash_equals((string) $user->email, (string) $this->option('confirm-email'))) {
            $this->error('Securite: --confirm-email doit correspondre exactement a l adresse du compte.');

            return self::FAILURE;
        }

        $chunkSize = max(50, min(5000, (int) $this->option('chunk')));

        try {
            $result = $dryRun
                ? $exporter->inspect($user)
                : $exporter->export($user, $chunkSize);
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Export impossible: '.$exception->getMessage());

            return self::FAILURE;
        }

        $rows = collect($result->datasetCounts)
            ->filter(fn (int $count): bool => $count > 0)
            ->map(fn (int $count, string $dataset): array => [$dataset, $count])
            ->values()
            ->all();

        $this->table(['Jeu de donnees', 'Lignes'], $rows ?: [['Aucune donnee', 0]]);
        $this->line('Total: '.$result->totalRows().' ligne(s), '.$result->exportedFileCount.' fichier(s).');

        foreach ($result->warnings as $warning) {
            $this->warn($warning);
        }

        if ($dryRun) {
            $this->info('Simulation terminee. Aucun fichier n a ete cree.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Archive creee dans le stockage prive:');
        $this->line((string) $result->absolutePath);
        $this->line('Taille: '.number_format(((int) $result->sizeBytes) / 1024 / 1024, 2, ',', ' ').' Mo');
        $this->warn('Transmettez cette archive par un canal securise, puis supprimez-la apres remise au client.');

        return self::SUCCESS;
    }
}
