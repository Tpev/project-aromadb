<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class GoogleTokenFile
{
    /** Dossier racine (`storage/app/google-calendar/tokens`) */
    protected static function dir(): string
    {
        $dir = storage_path('app/google-calendar/tokens');
        File::ensureDirectoryExists($dir, 0700, true);   // crée si absent
        return $dir;
    }

    /** Chemin absolu du token pour l’utilisateur. */
    public static function path(int $userId): string
    {
        return self::dir()."/token-{$userId}.json";
    }

    /** Écrit le token et renvoie le chemin. */
    public static function put(int $userId, array $token): string
    {
        $path = self::path($userId);
        File::put($path, json_encode($token, JSON_PRETTY_PRINT));
        File::chmod($path, 0600);
        return $path;
    }

    /** Supprime le fichier s’il existe. */
    public static function forget(int $userId): void
    {
        File::delete(self::path($userId));
    }
}
