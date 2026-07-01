<?php

use App\Support\UploadLimit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($request->routeIs('mobile.client.*') || $request->is('mobile/client*')) {
                return route('mobile.client.login');
            }

            if ($request->routeIs('mobile.*') || $request->is('mobile/*')) {
                return route('mobile.login');
            }

            return route('login');
        });

        $middleware->alias([
            'mobile.app' => \App\Http\Middleware\EnsureMobileApp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $e, $request) {
            $message = 'Le fichier envoye depasse la limite actuelle du serveur (' . UploadLimit::trainingVideoLimitLabel() . '). Reduisez la taille du fichier ou demandez une augmentation de la limite d\'upload.';

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
