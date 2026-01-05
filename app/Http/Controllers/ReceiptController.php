<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Receipt::where('user_id', Auth::id());

        if ($request->filled('from')) {
            $query->whereDate('encaissement_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('encaissement_date', '<=', $request->date('to'));
        }

        $receipts = (clone $query)->orderBy('encaissement_date')->paginate(50);

        $total = (clone $query)
            ->selectRaw("SUM(CASE WHEN direction='credit' THEN amount_ttc ELSE -amount_ttc END) as net")
            ->value('net') ?? 0;

        return view('receipts.index', compact('receipts', 'total'));
    }

    public function create()
    {
        return view('receipts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'encaissement_date' => ['required', 'date'],
            'direction'         => ['required', 'in:credit,debit'],
            'amount_ttc'        => ['required', 'numeric', 'min:0.01'],
            'amount_ht'         => ['nullable', 'numeric', 'min:0'],
            'payment_method'    => ['required', 'in:transfer,card,check,cash,other'],
            'nature'            => ['required', 'in:service,goods,other'],
            'client_name'       => ['nullable', 'string', 'max:255'],
            'invoice_number'    => ['nullable', 'string', 'max:255'],
            'note'              => ['nullable', 'string', 'max:255'],
        ]);

        $amountHt = $validated['amount_ht'];
        if ($amountHt === null || $amountHt === '') {
            $amountHt = $validated['amount_ttc'];
        }

        Receipt::create([
            'user_id'           => Auth::id(),
            'invoice_id'        => null,
            'invoice_number'    => filled($validated['invoice_number'] ?? null) ? $validated['invoice_number'] : null,
            'encaissement_date' => $validated['encaissement_date'],
            'client_name'       => filled($validated['client_name'] ?? null) ? $validated['client_name'] : null,
            'nature'            => $validated['nature'],
            'amount_ht'         => round((float)$amountHt, 2),
            'amount_ttc'        => round((float)$validated['amount_ttc'], 2),
            'payment_method'    => $validated['payment_method'],
            'direction'         => $validated['direction'],
            'source'            => 'manual',
            'note'              => $validated['note'] ?? null,
            'locked_at'         => now(),
        ]);

        return redirect()
            ->route('receipts.index')
            ->with('success', 'Écriture manuelle ajoutée au livre de recettes.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Receipt::where('user_id', Auth::id());
        if ($request->filled('from')) $query->whereDate('encaissement_date', '>=', $request->date('from'));
        if ($request->filled('to'))   $query->whereDate('encaissement_date', '<=', $request->date('to'));

        $filename = 'livre_recettes_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $PAYMENT_METHOD_FR = [
            'transfer' => 'Virement',
            'card'     => 'Carte',
            'check'    => 'Chèque',
            'cash'     => 'Espèces',
            'other'    => 'Autre',
        ];

        $NATURE_FR = [
            'service' => 'Service / Prestation',
            'goods'   => 'Vente de biens',
            'other'   => 'Autre',
        ];

        $SOURCE_FR = [
            'payment'    => 'Paiement',
            'correction' => 'Correction',
            'refund'     => 'Remboursement',
            'manual'     => 'Saisie manuelle',
        ];

        return response()->stream(function () use ($query, $PAYMENT_METHOD_FR, $NATURE_FR, $SOURCE_FR) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'Date encaissement','N° facture','Client','Nature','Montant HT','Montant TTC',
                'Mode règlement','Direction','Source','Note'
            ], ';');

            $query->orderBy('encaissement_date')->chunk(500, function ($rows) use ($out, $PAYMENT_METHOD_FR, $NATURE_FR, $SOURCE_FR) {
                foreach ($rows as $r) {
                    $modeFr   = $PAYMENT_METHOD_FR[$r->payment_method] ?? ucfirst((string)$r->payment_method);
                    $natureFr = $NATURE_FR[$r->nature] ?? ucfirst((string)$r->nature);
                    $sourceFr = $SOURCE_FR[$r->source] ?? (string)$r->source;

                    fputcsv($out, [
                        \Carbon\Carbon::parse($r->encaissement_date)->format('d/m/Y'),
                        $r->invoice_number,
                        $r->client_name,
                        $natureFr,
                        number_format($r->amount_ht, 2, ',', ' '),
                        number_format($r->amount_ttc, 2, ',', ' '),
                        $modeFr,
                        ucfirst((string)$r->direction),
                        $sourceFr,
                        $r->note,
                    ], ';');
                }
            });

            fclose($out);
        }, 200, $headers);
    }

    public function caMonthly(Request $request)
    {
        $year = (int)($request->input('year') ?: now()->year);

        $rows = Receipt::selectRaw("
                MONTH(encaissement_date) m,
                SUM(CASE WHEN direction='credit' THEN amount_ttc ELSE -amount_ttc END) ca_ttc
            ")
            ->where('user_id', Auth::id())
            ->whereYear('encaissement_date', $year)
            ->groupBy('m')
            ->orderBy('m')
            ->get();

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[$i] = 0.0;
        }
        foreach ($rows as $r) {
            $data[(int)$r->m] = (float)$r->ca_ttc;
        }

        return view('receipts.ca-monthly', compact('data', 'year'));
    }

    public function reverse(Request $request, Receipt $receipt)
    {
        abort_if($receipt->user_id !== Auth::id(), 403);

        // déjà contre-passée / ou c'est une contre-passation
        if (($receipt->is_reversal ?? false) || ($receipt->reversal_of_id ?? null)) {
            return back()->with('error', 'Cette ligne a déjà été contre-passée ou est une contre-passation.');
        }

        $validated = $request->validate([
            'encaissement_date' => ['required', 'date'],
            'amount_ttc'        => ['nullable', 'numeric', 'min:0.01'],
            'note'              => ['nullable', 'string', 'max:255'],
        ]);

        $amountTtcToReverse = $validated['amount_ttc'] ?? $receipt->amount_ttc;

        if ($amountTtcToReverse <= 0) {
            return back()->with('error', 'Montant invalide pour la contre-passation.');
        }
        if ($amountTtcToReverse > $receipt->amount_ttc) {
            return back()->with('error', 'Impossible de contre-passer un montant supérieur à l’original.');
        }

        $ratio = ((float)$receipt->amount_ttc) > 0
            ? ((float)$receipt->amount_ht / (float)$receipt->amount_ttc)
            : 1.0;

        $amountHtToReverse = round($amountTtcToReverse * $ratio, 2);

        $reversalDirection = $receipt->direction === 'credit' ? 'debit' : 'credit';

        Receipt::create([
            'user_id'           => $receipt->user_id,
            'invoice_id'        => $receipt->invoice_id,
            'encaissement_date' => $validated['encaissement_date'],
            'invoice_number'    => $receipt->invoice_number,
            'client_name'       => $receipt->client_name,
            'nature'            => $receipt->nature,
            'amount_ht'         => $amountHtToReverse,
            'amount_ttc'        => $amountTtcToReverse,
            'payment_method'    => $receipt->payment_method,
            'direction'         => $reversalDirection,
            'source'            => 'correction',
            'note'              => trim(($validated['note'] ?? '') . ' (CP de #' . $receipt->id . ')'),
            'is_reversal'       => true,
            'reversal_of_id'    => $receipt->id,
            'locked_at'         => now(),
        ]);

        return back()->with('success', 'Contre-passation enregistrée.');
    }
}
