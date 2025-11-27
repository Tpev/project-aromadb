<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class TherapistSearchController extends Controller
{
    /**
     * Main search endpoint (POST from mobile form).
     * Returns the mobile results view.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'name'      => 'nullable|string',
            'specialty' => 'nullable|string',
            'location'  => 'nullable|string',
        ]);

        $base = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true);

        // -------- Location filter (User + all practiceLocations) --------
        if (!empty($data['location'])) {
            $loc = trim($data['location']);

            $base->where(function ($q) use ($loc) {
                $q->where('city_setByAdmin', 'like', '%' . $loc . '%')
                  ->orWhere('state_setByAdmin', 'like', '%' . $loc . '%')
                  ->orWhereHas('practiceLocations', function ($lq) use ($loc) {
                      $lq->where('city', 'like', '%' . $loc . '%')
                         ->orWhere('postal_code', 'like', '%' . $loc . '%')
                         ->orWhere('address_line1', 'like', '%' . $loc . '%')
                         ->orWhere('address_line2', 'like', '%' . $loc . '%')
                         ->orWhere('country', 'like', '%' . $loc . '%')
                         ->orWhere('label', 'like', '%' . $loc . '%');
                  });
            });
        }

        // -------- Specialty filter (JSON or plain text) --------
        if (!empty($data['specialty'])) {
            $spec = trim($data['specialty']);
            $base->where(function ($q) use ($spec) {
                $q->orWhereJsonContains('services', $spec)
                  ->orWhere('services', 'like', '%' . $spec . '%');
            });
        }

        $specialty = $data['specialty'] ?? null;
        $region    = $data['location'] ?? null;

        // -------- No name provided => simple filtered list --------
        if (empty($data['name'])) {
            $therapists = $base->get();

            return view('mobile.therapists.results', [
                'therapists' => $therapists,
                'specialty'  => $specialty,
                'region'     => $region,
            ]);
        }

        // -------- Fuzzy Name/Company Search --------
        $nameTerm = trim($data['name']);
        $normTerm = $this->normalize($nameTerm);

        // Prefilter for performance
        $prefilter = (clone $base)
            ->where(function ($q) use ($nameTerm) {
                $q->where('name', 'like', '%' . $nameTerm . '%')
                  ->orWhere('company_name', 'like', '%' . $nameTerm . '%')
                  ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$nameTerm])
                  ->orWhereRaw('SOUNDEX(company_name) = SOUNDEX(?)', [$nameTerm]);
            })
            ->limit(250)
            ->get();

        // If empty, widen
        if ($prefilter->isEmpty()) {
            $prefilter = (clone $base)->limit(500)->get();
        }

        // Compute fuzzy score
        $scored = $prefilter->map(function ($t) use ($normTerm) {
            $fullName    = $t->name ?? '';
            $companyName = $t->company_name ?? '';

            $normFull    = $this->normalize($fullName);
            $normCompany = $this->normalize($companyName);

            $distances = [];

            if ($normFull !== '')    $distances[] = $this->lev($normFull, $normTerm);
            if ($normCompany !== '') $distances[] = $this->lev($normCompany, $normTerm);

            foreach (preg_split('/\s+/', $normFull) ?: [] as $tok) {
                if ($tok !== '') $distances[] = $this->lev($tok, $normTerm);
            }
            foreach (preg_split('/\s+/', $normCompany) ?: [] as $tok) {
                if ($tok !== '') $distances[] = $this->lev($tok, $normTerm);
            }

            if (empty($distances)) {
                $distances[] = 999;
            }

            $minDist = min($distances);

            $startsBoost   = 0;
            $containsBoost = 0;

            if ($normFull !== '') {
                if (Str::startsWith($normFull, $normTerm)) $startsBoost -= 2;
                if (Str::contains($normFull, $normTerm))   $containsBoost -= 1;
            }
            if ($normCompany !== '') {
                if (Str::startsWith($normCompany, $normTerm)) $startsBoost -= 2;
                if (Str::contains($normCompany, $normTerm))   $containsBoost -= 1;
            }

            $t->am_fuzzy_score = $minDist + $startsBoost + $containsBoost;

            return $t;
        });

        $len       = max(1, mb_strlen($normTerm));
        $threshold = max(1, (int) floor($len * 0.4)); // ~40% edits

        $therapists = $scored
            ->filter(fn ($t) => $t->am_fuzzy_score <= $threshold)
            ->sortBy('am_fuzzy_score')
            ->values();

        if ($therapists->isEmpty()) {
            $therapists = $scored->sortBy('am_fuzzy_score')->take(20)->values();
        }

        return view('mobile.therapists.results', [
            'therapists' => $therapists,
            'specialty'  => $specialty,
            'region'     => $region,
        ]);
    }

    /**
     * Mobile public profile.
     */
    public function show($slug)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        // You can create a dedicated mobile profile view later:
        return view('mobile.therapists.show', compact('therapist'));
    }

    // ----------------- Helpers -----------------

    /** Normalize to lowercase ASCII, collapse spaces */
    private function normalize(string $s): string
    {
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false && $t !== null) $s = $t;
        $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        return trim($s);
    }

    /** Levenshtein with guards */
    private function lev(string $a, string $b): int
    {
        if ($a === '' || $b === '') {
            return max(strlen($a), strlen($b));
        }

        return levenshtein($a, $b);
    }
}
