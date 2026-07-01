<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\GiftVoucherRedeemRequest;
use App\Http\Requests\GiftVoucherStoreRequest;
use App\Jobs\SendGiftVoucherEmailsJob;
use App\Models\GiftVoucher;
use App\Models\User;
use App\Services\GiftVoucherBackgroundService;
use App\Services\GiftVoucherCodeGenerator;
use App\Services\GiftVoucherInvoiceService;
use App\Services\GiftVoucherPdfService;
use App\Services\GiftVoucherRedeemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileGiftVoucherController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $canUseGiftVouchers = $user?->canUseFeature('gift_vouchers') ?? false;
        $status = $request->query('status', 'all');

        $allVouchers = GiftVoucher::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        $vouchers = $allVouchers
            ->filter(fn (GiftVoucher $voucher) => $this->matchesStatus($voucher, $status))
            ->values();

        return view('mobile.gift-vouchers.index', [
            'vouchers' => $vouchers,
            'allVouchers' => $allVouchers,
            'status' => $status,
            'canUseGiftVouchers' => $canUseGiftVouchers,
            'user' => $user,
        ]);
    }

    public function create()
    {
        $this->authorizeGiftVoucherFeature(Auth::user());

        return view('mobile.gift-vouchers.form', [
            'title' => 'Nouveau bon cadeau',
            'action' => route('mobile.gift-vouchers.store'),
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(
        GiftVoucherStoreRequest $request,
        GiftVoucherCodeGenerator $codeGenerator,
        GiftVoucherInvoiceService $invoiceService
    ) {
        $user = Auth::user();
        $this->authorizeGiftVoucherFeature($user);

        $amountCents = (int) round(((float) $request->input('amount_eur')) * 100);
        $snapshot = GiftVoucherBackgroundService::snapshotForVoucher($user);

        $voucher = GiftVoucher::create([
            'user_id' => $user->id,
            'code' => $codeGenerator->generateUniqueCode(),
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
                'Vente bon cadeau (creation manuelle)'
            );

            if ($invoice) {
                $voucher->sale_invoice_id = $invoice->id;
                $voucher->save();
            }
        }

        SendGiftVoucherEmailsJob::dispatch($voucher->id);

        return redirect()
            ->route('mobile.gift-vouchers.show', $voucher)
            ->with('success', 'Bon cadeau cree. Les emails vont etre envoyes.');
    }

    public function show(GiftVoucher $voucher)
    {
        $this->authorizeGiftVoucherFeature(Auth::user());
        $this->ensureOwnsVoucher($voucher);

        $voucher->load([
            'saleInvoice',
            'redemptions' => fn ($query) => $query->orderByDesc('created_at'),
        ]);

        return view('mobile.gift-vouchers.show', [
            'voucher' => $voucher,
        ]);
    }

    public function downloadPdf(GiftVoucher $voucher, GiftVoucherPdfService $pdfService)
    {
        $this->authorizeGiftVoucherFeature(Auth::user());
        $this->ensureOwnsVoucher($voucher);

        $pdfBinary = $pdfService->renderPdf($voucher);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="bon-cadeau-' . $voucher->code . '.pdf"',
        ]);
    }

    public function resendEmails(GiftVoucher $voucher)
    {
        $this->authorizeGiftVoucherFeature(Auth::user());
        $this->ensureOwnsVoucher($voucher);

        SendGiftVoucherEmailsJob::dispatch($voucher->id);

        return redirect()
            ->route('mobile.gift-vouchers.show', $voucher)
            ->with('success', 'Emails relances.');
    }

    public function redeem(
        GiftVoucher $voucher,
        GiftVoucherRedeemRequest $request,
        GiftVoucherRedeemService $service
    ) {
        $this->authorizeGiftVoucherFeature(Auth::user());
        $this->ensureOwnsVoucher($voucher);

        $amountCents = (int) round(((float) $request->input('amount_eur')) * 100);

        $service->redeem(
            $voucher,
            $amountCents,
            $request->input('note'),
            Auth::id(),
            $request->integer('appointment_id') ?: null,
            $request->integer('invoice_id') ?: null,
            'manual',
            'applied'
        );

        return redirect()
            ->route('mobile.gift-vouchers.show', $voucher)
            ->with('success', 'Montant deduit du bon cadeau.');
    }

    public function disable(GiftVoucher $voucher)
    {
        $this->authorizeGiftVoucherFeature(Auth::user());
        $this->ensureOwnsVoucher($voucher);

        $voucher->update(['is_active' => false]);

        return redirect()
            ->route('mobile.gift-vouchers.show', $voucher)
            ->with('success', 'Bon cadeau desactive.');
    }

    private function matchesStatus(GiftVoucher $voucher, string $status): bool
    {
        return match ($status) {
            'active' => $voucher->isUsable(),
            'expired' => $voucher->isExpired(),
            'exhausted' => (int) $voucher->remaining_amount_cents <= 0,
            'disabled' => ! $voucher->is_active,
            default => true,
        };
    }

    private function authorizeGiftVoucherFeature(?User $user): void
    {
        abort_unless($user && $user->canUseFeature('gift_vouchers'), 403);
    }

    private function ensureOwnsVoucher(GiftVoucher $voucher): void
    {
        abort_unless((int) $voucher->user_id === (int) Auth::id(), 403);
    }
}
