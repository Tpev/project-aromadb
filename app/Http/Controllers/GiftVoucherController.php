<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiftVoucherRedeemRequest;
use App\Http\Requests\GiftVoucherStoreRequest;
use App\Jobs\SendGiftVoucherEmailsJob;
use App\Models\GiftVoucher;
use App\Services\GiftVoucherCodeGenerator;
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

        return view('dashboard-pro/gift-vouchers/index', compact('vouchers', 'status'));
    }

    public function create()
    {
        return view('dashboard-pro/gift-vouchers/create');
    }

    public function store(GiftVoucherStoreRequest $request, GiftVoucherCodeGenerator $gen)
    {
        $user = auth()->user();

        $amountEur = (float) $request->input('amount_eur');
        $amountCents = (int) round($amountEur * 100);

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

            'recipient_name' => $request->input('recipient_name'),
            'recipient_email' => $request->input('recipient_email'),

            'message' => $request->input('message'),
            'source' => 'manual',
        ]);

        // Send emails async (recommended)
        SendGiftVoucherEmailsJob::dispatch($voucher->id);

        return redirect()
            ->route('pro.gift-vouchers.show', $voucher->id)
            ->with('success', 'Bon cadeau créé. Les emails vont être envoyés.');
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

        $service->redeem($voucher, $amountCents, $request->input('note'));

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
