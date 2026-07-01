<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\SuperPdpReceivedInvoice;
use App\Services\SuperPdp\SuperPdpApiClient;
use App\Services\SuperPdp\SuperPdpOAuthService;
use App\Services\SuperPdp\SuperPdpReceivedInvoiceSyncService;
use App\Support\SuperPdpFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileReceivedInvoiceController extends Controller
{
    public function index(
        Request $request,
        SuperPdpOAuthService $oauthService,
        SuperPdpReceivedInvoiceSyncService $syncService
    ) {
        $user = $request->user();
        $featureEnabled = SuperPdpFeature::enabledFor($user);
        $connection = null;
        $receivedInvoices = collect();

        if ($featureEnabled) {
            $connection = $oauthService->connectionFor($user);

            if ($request->boolean('sync')) {
                try {
                    $count = $syncService->sync($connection);

                    return redirect()
                        ->route('mobile.received-invoices.index')
                        ->with('success', $count . ' facture(s) recue(s) synchronisee(s).');
                } catch (\Throwable $e) {
                    Log::warning('SUPER PDP mobile received invoice sync failed.', [
                        'user_id' => $user->id,
                        'connection_id' => $connection->id,
                        'error' => $e->getMessage(),
                    ]);

                    $connection->forceFill([
                        'last_error' => $e->getMessage(),
                    ])->save();

                    return redirect()
                        ->route('mobile.received-invoices.index')
                        ->with('error', 'La synchronisation SUPER PDP a echoue. Reessayez dans quelques instants.');
                }
            }

            $receivedInvoices = $connection->receivedInvoices()
                ->latest('invoice_date')
                ->latest('id')
                ->limit(60)
                ->get();
        }

        return view('mobile.received-invoices.index', [
            'featureEnabled' => $featureEnabled,
            'connection' => $connection,
            'receivedInvoices' => $receivedInvoices,
        ]);
    }

    public function show(Request $request, SuperPdpReceivedInvoice $receivedInvoice)
    {
        SuperPdpFeature::abortUnlessEnabledFor($request->user());
        $this->ensureOwnsInvoice($request, $receivedInvoice);

        $receivedInvoice->load('connection');

        return view('mobile.received-invoices.show', [
            'invoice' => $receivedInvoice,
        ]);
    }

    public function download(
        Request $request,
        SuperPdpReceivedInvoice $receivedInvoice,
        SuperPdpApiClient $client
    ) {
        SuperPdpFeature::abortUnlessEnabledFor($request->user());
        $this->ensureOwnsInvoice($request, $receivedInvoice);

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

    private function ensureOwnsInvoice(Request $request, SuperPdpReceivedInvoice $receivedInvoice): void
    {
        abort_unless((int) $receivedInvoice->user_id === (int) $request->user()->id, 404);
    }
}
