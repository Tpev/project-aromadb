<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\InformationRequestMail;
use App\Models\InformationRequest;
use App\Models\DigitalTraining;

class PublicTherapistController extends Controller
{
    /**
     * Affiche la page publique du thérapeute.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
public function show($slug)
{
    $therapist = User::where('slug', $slug)
        ->where('is_therapist', true)
        ->firstOrFail();

    $therapist->increment('view_count');

    $testimonials = $therapist->testimonials()
        ->orderByRaw('COALESCE(external_created_at, created_at) DESC')
        ->paginate(5);

    // ✅ Prestations unitaires (Product)
    $prestations = $therapist->products()
        ->orderBy('display_order')
        ->get();

    // ✅ Packs (PackProduct) + contenu (items + product)
    $packProducts = \App\Models\PackProduct::where('user_id', $therapist->id)
        ->with(['items.product'])
        ->orderBy('name')
        ->get();

    // ✅ Calcul “prix à l’unité” + “économie” sur chaque pack (en mémoire)
    $packProducts->each(function ($pack) {
        $items = $pack->items ?? collect();

        $unitTotalTtc = (float) $items->sum(function ($it) {
            $qty = (int) ($it->quantity ?? 1);

            $p = $it->product;
            if (!$p) return 0;

            // Product::getPriceInclTaxAttribute() existe déjà chez toi
            $unitPriceTtc = (float) ($p->price_incl_tax ?? 0);

            return $qty * $unitPriceTtc;
        });

        $packPriceTtc = (float) ($pack->price_incl_tax ?? 0);

        $pack->unit_total_price = $unitTotalTtc;
        $pack->saving_amount    = max(0, $unitTotalTtc - $packPriceTtc);
        $pack->saving_percent   = ($unitTotalTtc > 0 && $pack->saving_amount > 0)
            ? round(($pack->saving_amount / $unitTotalTtc) * 100)
            : 0;
    });

	$trainings = DigitalTraining::query()
		->where('user_id', $therapist->id)
		->where('status', 'published') // adjust to your real value if different
		->orderByDesc('created_at')
		->get();

    $events = Event::where('user_id', $therapist->id)
        ->where('start_date_time', '>=', Carbon::now())
        ->where('showOnPortail', true)
        ->orderBy('start_date_time', 'asc')
        ->with('associatedProduct')
        ->get();

    return view('public.therapist.show', compact(
        'therapist',
        'testimonials',
        'prestations',
        'packProducts',
        'events',
		'trainings'
    ));
}




public function sendInformationRequest(Request $request, $slug)
{
    $request->validate([
        'first_name' => 'required|string|max:100',
        'last_name'  => 'required|string|max:100',
        'email'      => 'required|email',
        'phone'      => ['nullable','regex:/^[0-9\-\+\(\)\s]+$/','min:8'],
        'message'    => 'required|string|max:2000',
    ]);

    // Retrieve therapist
    $therapist = User::where('slug', $slug)
                     ->where('is_therapist', true)
                     ->firstOrFail();

    // 1) Store the request in DB
    InformationRequest::create([
        'therapist_id' => $therapist->id,
        'first_name'   => $request->first_name,
        'last_name'    => $request->last_name,
        'email'        => $request->email,
        'phone'        => $request->phone,
        'message'      => $request->message,
    ]);

    // 2) Send the email
    Mail::to($therapist->email)->send(
        new InformationRequestMail(
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->message
        )
    );

    // (Optional) Send a confirmation email to the user as well

    return redirect()->back()->with('success', 'Votre demande a bien été envoyée !');
}


}
