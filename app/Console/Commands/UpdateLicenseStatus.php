<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class UpdateLicenseStatus extends Command
{
    protected $signature = 'license:update-status';

    protected $description = 'Met à jour le statut de licence des utilisateurs après la période d\'essai';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Obtenir la date limite (il y a 15 jours)
        $dateLimit = Carbon::now()->subDays(15);

        // Récupérer les utilisateurs concernés
        $users = User::where('is_therapist', true)
            ->where('license_product', 'essai')
            ->where('license_status', 'actif')
            ->where('created_at', '<=', $dateLimit)
            ->get();

        foreach ($users as $user) {
            $user->license_status = 'expirée';
            $user->save();

            // Optionnel : Envoyer une notification ou un e-mail à l'utilisateur
            // $user->notify(new LicenseExpiredNotification());
        }

        $this->info('Le statut de licence a été mis à jour pour ' . $users->count() . ' utilisateur(s).');
    }
}
