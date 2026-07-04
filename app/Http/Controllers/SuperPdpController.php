<?php

namespace App\Http\Controllers;

use App\Models\SuperPdpConnection;
use App\Services\SuperPdp\SuperPdpOAuthService;
use App\Support\SuperPdpFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperPdpController extends Controller
{
    public function connect(Request $request, SuperPdpOAuthService $oauthService)
    {
        $user = $request->user();
        SuperPdpFeature::abortUnlessEnabledFor($user);

        if (! $oauthService->isConfigured()) {
            return back()->with('error', 'La connexion SUPER PDP sandbox n\'est pas encore configurée côté Olithea.');
        }

        $receiveInApp = $request->boolean('receive_in_app');
        $oauthService->markAuthorizationStarted($user, $receiveInApp);

        return redirect()->away($oauthService->authorizationUrl($user, $receiveInApp));
    }

    public function callback(Request $request, SuperPdpOAuthService $oauthService)
    {
        $user = $request->user();
        SuperPdpFeature::abortUnlessEnabledFor($user);

        if ($request->filled('error')) {
            $message = (string) $request->input('error_description', $request->input('error'));
            $oauthService->markError($user, $message);

            return redirect()
                ->route('profile.editCompanyInfo')
                ->with('error', 'Connexion SUPER PDP interrompue : ' . $message);
        }

        $validated = $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        try {
            $oauthService->exchangeAuthorizationCode($user, $validated['code'], $validated['state']);
        } catch (\Throwable $e) {
            Log::warning('SUPER PDP OAuth callback failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            $oauthService->markError($user, $e->getMessage());

            return redirect()
                ->route('profile.editCompanyInfo')
                ->with('error', 'Impossible de finaliser la connexion SUPER PDP. Vérifiez l’application sandbox et réessayez.');
        }

        return redirect()
            ->route('profile.editCompanyInfo')
            ->with('success', 'SUPER PDP sandbox est connecté. Vous pouvez maintenant tester le service.');
    }

    public function updatePreferences(Request $request, SuperPdpOAuthService $oauthService)
    {
        $user = $request->user();
        SuperPdpFeature::abortUnlessEnabledFor($user);

        $connection = $oauthService->connectionFor($user);
        $connection->forceFill([
            'receiving_invoices_enabled' => $request->boolean('receiving_invoices_enabled'),
        ])->save();

        return back()->with('success', 'Préférences SUPER PDP mises à jour.');
    }

    public function disconnect(Request $request, SuperPdpOAuthService $oauthService)
    {
        $user = $request->user();
        SuperPdpFeature::abortUnlessEnabledFor($user);

        $connection = SuperPdpConnection::query()
            ->where('user_id', $user->id)
            ->where('environment', 'sandbox')
            ->first();

        if ($connection) {
            $oauthService->disconnect($connection);
        }

        return back()->with('success', 'SUPER PDP sandbox a été déconnecté.');
    }
}
