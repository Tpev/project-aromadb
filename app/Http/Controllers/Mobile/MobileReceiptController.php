<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileReceiptController extends Controller
{
    public function index(Request $request)
    {
        $canUseReceipts = $this->canUseReceipts();
        $query = Receipt::query()->where('user_id', Auth::id());

        if (! $canUseReceipts) {
            $receipts = Receipt::query()
                ->whereRaw('1 = 0')
                ->paginate(30)
                ->withQueryString();

            return view('mobile.receipts.index', [
                'receipts' => $receipts,
                'total' => 0.0,
                'creditTotal' => 0.0,
                'debitTotal' => 0.0,
                'lineCount' => 0,
                'correctionCount' => 0,
                'canUseReceipts' => false,
            ]);
        }

        if ($request->filled('from')) {
            $query->whereDate('encaissement_date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('encaissement_date', '<=', $request->date('to'));
        }

        $receipts = (clone $query)
            ->withCount('reversals')
            ->latest('encaissement_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $total = (clone $query)
            ->selectRaw("SUM(CASE WHEN direction='credit' THEN amount_ttc ELSE -amount_ttc END) as net")
            ->value('net') ?? 0;

        $creditTotal = (clone $query)->where('direction', 'credit')->sum('amount_ttc');
        $debitTotal = (clone $query)->where('direction', 'debit')->sum('amount_ttc');
        $lineCount = (clone $query)->count();
        $correctionCount = (clone $query)->where('is_reversal', true)->count();

        return view('mobile.receipts.index', [
            'receipts' => $receipts,
            'total' => (float) $total,
            'creditTotal' => (float) $creditTotal,
            'debitTotal' => (float) $debitTotal,
            'lineCount' => $lineCount,
            'correctionCount' => $correctionCount,
            'canUseReceipts' => $canUseReceipts,
        ]);
    }

    public function create()
    {
        abort_unless($this->canUseReceipts(), 403);

        return view('mobile.receipts.form');
    }

    public function store(Request $request)
    {
        abort_unless($this->canUseReceipts(), 403);

        $validated = $this->validateManualReceipt($request);

        $amountHt = $validated['amount_ht'] ?? null;
        if ($amountHt === null || $amountHt === '') {
            $amountHt = $validated['amount_ttc'];
        }

        Receipt::create([
            'user_id' => Auth::id(),
            'invoice_id' => null,
            'invoice_number' => filled($validated['invoice_number'] ?? null) ? $validated['invoice_number'] : null,
            'encaissement_date' => $validated['encaissement_date'],
            'client_name' => filled($validated['client_name'] ?? null) ? $validated['client_name'] : null,
            'nature' => $validated['nature'],
            'amount_ht' => round((float) $amountHt, 2),
            'amount_ttc' => round((float) $validated['amount_ttc'], 2),
            'payment_method' => $validated['payment_method'],
            'direction' => $validated['direction'],
            'source' => 'manual',
            'note' => $validated['note'] ?? null,
            'locked_at' => now(),
        ]);

        return redirect()
            ->route('mobile.receipts.index')
            ->with('success', 'Ecriture ajoutee au livre de recettes.');
    }

    public function monthly(Request $request)
    {
        abort_unless($this->canUseReceipts(), 403);

        $year = (int) ($request->input('year') ?: now()->year);

        $rows = Receipt::query()
            ->where('user_id', Auth::id())
            ->whereYear('encaissement_date', $year)
            ->orderBy('encaissement_date')
            ->get(['encaissement_date', 'nature', 'amount_ttc', 'direction']);

        $data = $this->emptyMonthlyReceiptData();

        foreach ($rows as $row) {
            $month = (int) $row->encaissement_date->format('n');
            $amount = $row->direction === 'credit'
                ? (float) $row->amount_ttc
                : -1 * (float) $row->amount_ttc;
            $nature = in_array($row->nature, ['service', 'goods', 'other'], true) ? $row->nature : 'other';

            $data[$month]['total'] += $amount;
            $data[$month][$nature] += $amount;
        }

        return view('mobile.receipts.monthly', compact('data', 'year'));
    }

    public function reverse(Request $request, Receipt $receipt)
    {
        abort_unless($this->canUseReceipts(), 403);
        abort_unless((int) $receipt->user_id === (int) Auth::id(), 403);

        if (($receipt->is_reversal ?? false) || ($receipt->reversal_of_id ?? null) || $receipt->reversals()->exists()) {
            return back()->with('error', 'Cette ligne a deja ete contre-passee.');
        }

        $validated = $request->validate([
            'encaissement_date' => ['required', 'date'],
            'amount_ttc' => ['nullable', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amountTtcToReverse = $validated['amount_ttc'] ?? $receipt->amount_ttc;

        if ((float) $amountTtcToReverse <= 0) {
            return back()->with('error', 'Montant invalide pour la contre-passation.');
        }

        if ((float) $amountTtcToReverse > (float) $receipt->amount_ttc) {
            return back()->with('error', 'Impossible de contre-passer un montant superieur a l original.');
        }

        $ratio = ((float) $receipt->amount_ttc) > 0
            ? ((float) $receipt->amount_ht / (float) $receipt->amount_ttc)
            : 1.0;

        $reversalDirection = $receipt->direction === 'credit' ? 'debit' : 'credit';

        Receipt::create([
            'user_id' => $receipt->user_id,
            'invoice_id' => $receipt->invoice_id,
            'encaissement_date' => $validated['encaissement_date'],
            'invoice_number' => $receipt->invoice_number,
            'client_name' => $receipt->client_name,
            'nature' => $receipt->nature,
            'amount_ht' => round(((float) $amountTtcToReverse) * $ratio, 2),
            'amount_ttc' => round((float) $amountTtcToReverse, 2),
            'payment_method' => $receipt->payment_method,
            'direction' => $reversalDirection,
            'source' => 'correction',
            'note' => trim(($validated['note'] ?? '') . ' (CP de #' . $receipt->id . ')'),
            'is_reversal' => true,
            'reversal_of_id' => $receipt->id,
            'locked_at' => now(),
        ]);

        return back()->with('success', 'Contre-passation enregistree.');
    }

    protected function validateManualReceipt(Request $request): array
    {
        return $request->validate([
            'encaissement_date' => ['required', 'date'],
            'direction' => ['required', 'in:credit,debit'],
            'amount_ttc' => ['required', 'numeric', 'min:0.01'],
            'amount_ht' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:transfer,card,check,cash,other'],
            'nature' => ['required', 'in:service,goods,other'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function emptyMonthlyReceiptData(): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $data[$month] = [
                'total' => 0.0,
                'service' => 0.0,
                'goods' => 0.0,
                'other' => 0.0,
            ];
        }

        return $data;
    }

    protected function canUseReceipts(): bool
    {
        return (bool) Auth::user()?->canUseFeature('livre_recettes');
    }
}
