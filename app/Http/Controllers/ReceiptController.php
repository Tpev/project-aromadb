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
        //$this->authorize('viewAny', \App\Models\Invoice::class); // même logique de visibilité
        $query = Receipt::where('user_id', Auth::id());

        if ($request->filled('from')) {
            $query->whereDate('encaissement_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('encaissement_date', '<=', $request->date('to'));
        }

        $receipts = $query->orderBy('encaissement_date')->paginate(50);

        // total période
        $total = $query->clone()
            ->selectRaw("SUM(CASE WHEN direction='credit' THEN amount_ttc ELSE -amount_ttc END) as net")
            ->value('net') ?? 0;

        return view('receipts.index', compact('receipts','total'));
    }

public function exportCsv(Request $request): StreamedResponse
{
    //$this->authorize('viewAny', \App\Models\Invoice::class);

    $query = Receipt::where('user_id', Auth::id());
    if ($request->filled('from')) $query->whereDate('encaissement_date', '>=', $request->date('from'));
    if ($request->filled('to'))   $query->whereDate('encaissement_date', '<=', $request->date('to'));

    $filename = 'livre_recettes_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    // Mapping des modes de règlement en français
    $PAYMENT_METHOD_FR = [
        'transfer' => 'Virement',
        'card'     => 'Carte',
        'check'    => 'Chèque',
        'cash'     => 'Espèces',
        'other'    => 'Autre',
    ];

    return response()->stream(function() use ($query, $PAYMENT_METHOD_FR) {
        $out = fopen('php://output', 'w');

        // Ajouter BOM UTF-8 pour compatibilité Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // En-têtes CSV
        fputcsv($out, [
            'Date encaissement','N° facture','Client','Nature','Montant HT','Montant TTC',
            'Mode règlement','Direction','Source','Note'
        ], ';');

        // Export en chunks
        $query->orderBy('encaissement_date')->chunk(500, function($rows) use ($out, $PAYMENT_METHOD_FR) {
            foreach ($rows as $r) {
                $modeFr = $PAYMENT_METHOD_FR[$r->payment_method] ?? ucfirst($r->payment_method);

                fputcsv($out, [
                    \Carbon\Carbon::parse($r->encaissement_date)->format('d/m/Y'),
                    $r->invoice_number,
                    $r->client_name,
                    ucfirst($r->nature),
                    number_format($r->amount_ht, 2, ',', ' '),
                    number_format($r->amount_ttc, 2, ',', ' '),
                    $modeFr,
                    ucfirst($r->direction),
                    $r->source,
                    $r->note,
                ], ';');
            }
        });

        fclose($out);
    }, 200, $headers);
}


    public function caMonthly(Request $request)
    {
        //$this->authorize('viewAny', \App\Models\Invoice::class);

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

        // fabrique un tableau [1..12]
        $data = [];
        for ($i=1; $i<=12; $i++) {
            $data[$i] = 0.0;
        }
        foreach ($rows as $r) {
            $data[(int)$r->m] = (float) $r->ca_ttc;
        }

        return view('receipts.ca-monthly', compact('data','year'));
    }
// app/Http/Controllers/ReceiptController.php

public function reverse(Request $request, Receipt $receipt)
{
    abort_if($receipt->user_id !== Auth::id(), 403);

    if ($receipt->is_reversal || $receipt->reversal_of_id) {
        return back()->with('error', 'Cette ligne a déjà été contre-passée ou est une contre-passation.');
    }

    $validated = $request->validate([
        'encaissement_date' => ['required','date'],
        'amount_ttc'        => ['nullable','numeric','min:0.01'],
        'note'              => ['nullable','string','max:255'],
    ]);

    $amountTtcToReverse = $validated['amount_ttc'] ?? $receipt->amount_ttc;
    if ($amountTtcToReverse <= 0) {
        return back()->with('error', 'Montant invalide pour la contre-passation.');
    }
    if ($amountTtcToReverse > $receipt->amount_ttc) {
        return back()->with('error', 'Impossible de contre-passer un montant supérieur à l’original.');
    }

    // Conserver le ratio HT/TTC de la ligne d’origine
    $ratio = ($receipt->amount_ttc ?? 0.0) > 0 ? ($receipt->amount_ht / $receipt->amount_ttc) : 1.0;
    $amountHtToReverse = round($amountTtcToReverse * $ratio, 2);

    // ✅ Votre enum direction: credit|debit
    $reversalDirection = $receipt->direction === 'credit' ? 'debit' : 'credit';

    $reversal = new Receipt();
    $reversal->fill([
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
        // ✅ FIX: enum autorisé par votre schéma
        'source'            => 'correction',
        'note'              => trim(($validated['note'] ?? '').' (CP de #'.$receipt->id.')'),
        'is_reversal'       => true,
        'reversal_of_id'    => $receipt->id,
        'locked_at'         => now(),
    ]);
    $reversal->save();

    return back()->with('success', 'Contre-passation enregistrée.');
}

}
