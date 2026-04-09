<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use App\Support\UploadLimit;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 👇 Add your custom middleware alias here.
        // This does NOT remove Laravel’s default aliases/groups.
        $middleware->alias([
            'mobile.app' => \App\Http\Middleware\EnsureMobileApp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $e, $request) {
            $message = 'Le fichier envoyé dépasse la limite actuelle du serveur (' . UploadLimit::trainingVideoLimitLabel() . '). Réduisez la taille du fichier ou demandez une augmentation de la limite d’upload.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'max_upload_size' => UploadLimit::trainingVideoLimitLabel(),
                ], 413);
            }

            return response($message, 413);
        });
    })
    ->create();
