<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiftVoucherSettingsUpdateRequest;
use App\Http\Requests\GiftVoucherRedeemRequest;
use App\Http\Requests\GiftVoucherStoreRequest;
use App\Jobs\SendGiftVoucherEmailsJob;
use App\Models\GiftVoucher;
use App\Services\GiftVoucherBackgroundService;
use App\Services\GiftVoucherCodeGenerator;
use App\Services\GiftVoucherInvoiceService;
use App\Services\GiftVoucherPdfService;
use App\Services\GiftVoucherRedeemService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GiftVoucherController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();

        $status = $request->query('status', 'all'); // all|active|expired|exhausted|disabled

        $query = GiftVoucher::where('user_id', $user->id)->orderByDesc('created_at');

        if ($status === 'active') {
            $query->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->where('remaining_amount_cents', '>', 0);
        } elseif ($status === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<', now());
        } elseif ($status === 'exhausted') {
            $query->where('remaining_amount_cents', '<=', 0);
        } elseif ($status === 'disabled') {
            $query->where('is_active', false);
        }

        $vouchers = $query->paginate(20)->withQueryString();

        return view('dashboard-pro/gift-vouchers/index', compact('vouchers', 'status', 'user'));
    }

    public function create()
    {
        $user = auth()->user();
        return view('dashboard-pro/gift-vouchers/create', compact('user'));
    }

    public function store(
        GiftVoucherStoreRequest $request,
        GiftVoucherCodeGenerator $gen,
        GiftVoucherInvoiceService $invoiceService
    ) {
        $user = auth()->user();

        $amountEur = (float) $request->input('amount_eur');
        $amountCents = (int) round($amountEur * 100);
        $snapshot = GiftVoucherBackgroundService::snapshotForVoucher($user);

        $voucher = GiftVoucher::create([
            'user_id' => $user->id,
            'code' => $gen->generateUniqueCode(),
            'original_amount_cents' => $amountCents,
            'remaining_amount_cents' => $amountCents,
            'currency' => 'EUR',
            'is_active' => true,
            'expires_at' => $request->input('expires_at') ?: null,

            'buyer_name' => $request->input('buyer_name'),
            'buyer_email' => $request->input('buyer_email'),
            'buyer_phone' => $request->input('buyer_phone'),

            'recipient_name' => $request->input('recipient_name'),
            'recipient_email' => $request->input('recipient_email'),

            'message' => $request->input('message'),
            'source' => 'manual',
            'sale_channel' => 'offline_manual',
            'sale_status' => 'paid',
            'background_mode_snapshot' => $snapshot['mode'],
            'background_path_snapshot' => $snapshot['path'],
        ]);

        if ($request->boolean('create_sale_invoice')) {
            $invoice = $invoiceService->createSaleInvoice(
                $voucher,
                (string) $request->input('payment_method', 'other'),
                'Vente bon cadeau (création manuelle)'
            );

            if ($invoice) {
                $voucher->sale_invoice_id = $invoice->id;
                $voucher->save();
            }
        }

        // Send emails async (recommended)
        SendGiftVoucherEmailsJob::dispatch($voucher->id);

        return redirect()
            ->route('pro.gift-vouchers.show', $voucher->id)
            ->with('success', 'Bon cadeau créé. Les emails vont être envoyés.');
    }

    public function updateSettings(
        GiftVoucherSettingsUpdateRequest $request
    ) {
        $user = auth()->user();

        $user->gift_voucher_online_enabled = $request->boolean('gift_voucher_online_enabled');

        $mode = $request->input('gift_voucher_background_mode', 'default');
        $removeBackground = $request->boolean('remove_gift_voucher_background');

        if ($removeBackground || $mode === 'default') {
            GiftVoucherBackgroundService::removeGlobalBackground($user);
            $user->gift_voucher_background_mode = 'default';
            $user->gift_voucher_background_path = null;
            $user->gift_voucher_background_updated_at = now();
            $user->save();

            return back()->with('success', 'Paramètres bon cadeau mis à jour.');
        }

        if ($mode === 'custom_upload' && $request->hasFile('gift_voucher_background')) {
            $path = GiftVoucherBackgroundService::storeGlobalBackground($user, $request->file('gift_voucher_background'));
            $user->gift_voucher_background_mode = 'custom_upload';
            $user->gift_voucher_background_path = $path;
            $user->gift_voucher_background_updated_at = now();
            $user->save();
        }

        return back()->with('success', 'Paramètres bon cadeau mis à jour.');
    }

    public function show(GiftVoucher $voucher)
    {
        $this->authorize('view', $voucher);

        $voucher->load(['redemptions' => function ($q) {
            $q->orderByDesc('created_at');
        }]);

        return view('dashboard-pro/gift-vouchers/show', compact('voucher'));
    }

    public function downloadPdf(GiftVoucher $voucher, GiftVoucherPdfService $pdfService)
    {
        $this->authorize('view', $voucher);

        $pdfBinary = $pdfService->renderPdf($voucher);

        $filename = 'bon-cadeau-' . $voucher->code . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function resendEmails(GiftVoucher $voucher)
    {
        $this->authorize('update', $voucher);

        SendGiftVoucherEmailsJob::dispatch($voucher->id);

        return back()->with('success', 'Emails relancés.');
    }

    public function redeem(GiftVoucher $voucher, GiftVoucherRedeemRequest $request, GiftVoucherRedeemService $service)
    {
        $this->authorize('update', $voucher);

        $amountEur = (float) $request->input('amount_eur');
        $amountCents = (int) round($amountEur * 100);

        $service->redeem(
            $voucher,
            $amountCents,
            $request->input('note'),
            auth()->id(),
            $request->integer('appointment_id') ?: null,
            $request->integer('invoice_id') ?: null,
            'manual',
            'applied'
        );

        return back()->with('success', 'Montant déduit du bon cadeau.');
    }

    public function disable(GiftVoucher $voucher)
    {
        $this->authorize('update', $voucher);

        $voucher->is_active = false;
        $voucher->save();

        return back()->with('success', 'Bon cadeau désactivé.');
    }
}
