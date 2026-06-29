<?php

namespace App\Http\Controllers;

use App\Models\SuperPdpReceivedInvoice;
use App\Services\SuperPdp\SuperPdpApiClient;
use App\Services\SuperPdp\SuperPdpOAuthService;
use App\Services\SuperPdp\SuperPdpReceivedInvoiceSyncService;
use App\Support\SuperPdpFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperPdpReceivedInvoiceController extends Controller
{
    public function index(
        Request $request,
        SuperPdpOAuthService $oauthService,
        SuperPdpReceivedInvoiceSyncService $syncService
    ) {
        $user = $request->user();
        SuperPdpFeature::abortUnlessEnabledFor($user);

        $connection = $oauthService->connectionFor($user);

        if ($request->boolean('sync')) {
            try {
                $count = $syncService->sync($connection);

                return redirect()
                    ->route('super-pdp.received-invoices.index')
                    ->with('success', $count . ' facture(s) reçue(s) synchronisée(s).');
            } catch (\Throwable $e) {
                Log::warning('SUPER PDP received invoice sync failed.', [
                    'user_id' => $user->id,
                    'connection_id' => $connection->id,
                    'error' => $e->getMessage(),
                ]);

                $connection->forceFill([
                    'last_error' => $e->getMessage(),
                ])->save();

                return redirect()
                    ->route('super-pdp.received-invoices.index')
                    ->with('error', 'La synchronisation SUPER PDP a échoué. Réessayez dans quelques instants.');
            }
        }

        $receivedInvoices = $connection->receivedInvoices()
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(20);

        return view('super-pdp.received-invoices', compact('connection', 'receivedInvoices'));
    }

    public function download(
        Request $request,
        SuperPdpReceivedInvoice $receivedInvoice,
        SuperPdpApiClient $client
    ) {
        $user = $request->user();
        SuperPdpFeature::abortUnlessEnabledFor($user);

        abort_unless((int) $receivedInvoice->user_id === (int) $user->id, 404);

        $format = $request->input('format', 'factur-x');
        abort_unless(in_array($format, ['factur-x', 'original', 'cii', 'ubl'], true), 422);

        $response = $client->invoiceDocument($receivedInvoice->connection, $receivedInvoice->super_pdp_invoice_id, $format);
        $extension = $format === 'factur-x' ? 'pdf' : 'xml';
        $number = $receivedInvoice->invoice_number ?: $receivedInvoice->super_pdp_invoice_id;

        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type', $extension === 'pdf' ? 'application/pdf' : 'application/xml'),
            'Content-Disposition' => 'attachment; filename="super-pdp-facture-' . $number . '.' . $extension . '"',
        ]);
    }
}
