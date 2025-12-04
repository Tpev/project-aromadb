<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TherapistSearchController extends Controller
{
    /**
     * Main search endpoint (POST from form).
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

        // ----------------- Location filter (User + all practiceLocations) -----------------
        if (!empty($data['location'])) {
            // Normalize to lowercase
            $loc = mb_strtolower(trim($data['location']), 'UTF-8');
            $likeLoc = '%' . $loc . '%';

            $base->where(function ($q) use ($likeLoc) {
                // Match therapist main city/state set by admin (case-insensitive)
                $q->whereRaw('LOWER(city_setByAdmin) LIKE ?', [$likeLoc])
                  ->orWhereRaw('LOWER(state_setByAdmin) LIKE ?', [$likeLoc])
                  // ALSO match on all practice locations (cabinets)
                  ->orWhereHas('practiceLocations', function ($lq) use ($likeLoc) {
                      $lq->whereRaw('LOWER(city) LIKE ?', [$likeLoc])
                         ->orWhereRaw('LOWER(postal_code) LIKE ?', [$likeLoc])
                         ->orWhereRaw('LOWER(address_line1) LIKE ?', [$likeLoc])
                         ->orWhereRaw('LOWER(address_line2) LIKE ?', [$likeLoc])
                         ->orWhereRaw('LOWER(country) LIKE ?', [$likeLoc])
                         ->orWhereRaw('LOWER(label) LIKE ?', [$likeLoc]);
                  });
            });
        }

        // Specialty filter (supports JSON array or plain text)
        if (!empty($data['specialty'])) {
            // Normalize to lowercase
            $spec = mb_strtolower(trim($data['specialty']), 'UTF-8');
            $likeSpec = '%' . $spec . '%';

            $base->where(function ($q) use ($spec, $likeSpec) {
                // JSON contains is case-sensitive, so we try with normalized value
                $q->orWhereJsonContains('services', $spec)
                  // Fallback: cast JSON to string and lowercase for LIKE match
                  ->orWhereRaw('LOWER(CAST(services AS CHAR)) LIKE ?', [$likeSpec]);
            });
        }

        // No name provided => fetch as-is
        if (empty($data['name'])) {
            $therapists = $base->get();
            $specialty  = $data['specialty'] ?? null;
            $region     = $data['location'] ?? null;
            return view('results', compact('therapists', 'specialty', 'region'));
        }

        // -------- Fuzzy Name/Company Search (small typos tolerated) --------
        $nameTerm = trim($data['name']);
        $normTerm = $this->normalize($nameTerm);

        // Prefilter in DB for performance: LIKE + SOUNDEX on both name and company_name
        $prefilter = (clone $base)
            ->where(function ($q) use ($nameTerm) {
                $q->where('name', 'like', '%' . $nameTerm . '%')
                  ->orWhere('company_name', 'like', '%' . $nameTerm . '%')
                  ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$nameTerm])
                  ->orWhereRaw('SOUNDEX(company_name) = SOUNDEX(?)', [$nameTerm]);
            })
            ->limit(250)
            ->get();

        // If prefilter missed (rare), widen to avoid empty results
        if ($prefilter->isEmpty()) {
            $prefilter = (clone $base)->limit(500)->get();
        }

        // Compute distances (Levenshtein) on normalized strings (name + company_name)
        $scored = $prefilter->map(function ($t) use ($normTerm) {
            $fullName    = $t->name ?? '';
            $companyName = $t->company_name ?? '';

            $normFull    = $this->normalize($fullName);
            $normCompany = $this->normalize($companyName);

            // Token-based min distance helps "Dr Jean Martin" vs "Martin" and for company_name too
            $distances = [];

            // full strings
            if ($normFull !== '')    $distances[] = $this->lev($normFull, $normTerm);
            if ($normCompany !== '') $distances[] = $this->lev($normCompany, $normTerm);

            // tokens of name
            foreach (preg_split('/\s+/', $normFull) ?: [] as $tok) {
                if ($tok !== '') $distances[] = $this->lev($tok, $normTerm);
            }
            // tokens of company
            foreach (preg_split('/\s+/', $normCompany) ?: [] as $tok) {
                if ($tok !== '') $distances[] = $this->lev($tok, $normTerm);
            }

            // if still empty (both fields empty), set large distance
            if (empty($distances)) {
                $distances[] = 999;
            }

            $minDist = min($distances);

            // Small boosts if either field starts with / contains the term
            $startsBoost = 0;
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

        // Keep "close enough" matches
        $len = max(1, mb_strlen($normTerm));
        $threshold = max(1, (int) floor($len * 0.4)); // ~40% edits tolerated
        $therapists = $scored
            ->filter(fn ($t) => $t->am_fuzzy_score <= $threshold)
            ->sortBy('am_fuzzy_score')
            ->values();

        // If none passes threshold, still show best few
        if ($therapists->isEmpty()) {
            $therapists = $scored->sortBy('am_fuzzy_score')->take(20)->values();
        }

        $specialty = $data['specialty'] ?? null;
        $region    = $data['location'] ?? null;

        return view('results', compact('therapists', 'specialty', 'region'));
    }

    /**
     * Public profile.
     */
    public function show($slug)
    {
        $therapist = User::where('slug', $slug)
            ->where('is_therapist', true)
            ->firstOrFail();

        return view('therapists.show', compact('therapist'));
    }

    /**
     * /practicien-{specialty}
     */
    public function filterBySpecialty($specialty)
    {
        // param is slug-like: "naturopathe", "osteopathe-energeticien", etc.
        $specialtySearchRaw   = str_replace('-', ' ', $specialty);
        $specialtySearch      = mb_strtolower($specialtySearchRaw, 'UTF-8');
        $likeSpecialtySearch  = '%' . $specialtySearch . '%';

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function ($q) use ($specialtySearch, $likeSpecialtySearch) {
                $q->orWhereJsonContains('services', $specialtySearch)
                  ->orWhereRaw('LOWER(CAST(services AS CHAR)) LIKE ?', [$likeSpecialtySearch]);
            })
            ->get();

        $specialty = $specialtySearchRaw; // for display if you want original case-ish
        return view('results', compact('therapists', 'specialty'));
    }

    /**
     * /region-{region}
     */
    public function filterByRegion($region)
    {
        // Original region string (with spaces instead of '-') for display
        $regionRaw     = str_replace('-', ' ', $region);
        $regionSearch  = mb_strtolower($regionRaw, 'UTF-8');
        $likeRegion    = '%' . $regionSearch . '%';

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function ($q) use ($likeRegion) {
                $q->whereRaw('LOWER(city_setByAdmin) LIKE ?', [$likeRegion])
                  ->orWhereRaw('LOWER(state_setByAdmin) LIKE ?', [$likeRegion])
                  // match region via practice locations too
                  ->orWhereHas('practiceLocations', function ($lq) use ($likeRegion) {
                      $lq->whereRaw('LOWER(city) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(postal_code) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(address_line1) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(address_line2) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(country) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(label) LIKE ?', [$likeRegion]);
                  });
            })
            ->get();

        $region = $regionRaw; // for display
        return view('results', compact('therapists', 'region'));
    }

    /**
     * /practicien-{specialty}-region-{region}
     */
    public function filterBySpecialtyRegion($specialty, $region)
    {
        $specialtySearchRaw   = str_replace('-', ' ', $specialty);
        $specialtySearch      = mb_strtolower($specialtySearchRaw, 'UTF-8');
        $likeSpecialtySearch  = '%' . $specialtySearch . '%';

        $regionRaw     = str_replace('-', ' ', $region);
        $regionSearch  = mb_strtolower($regionRaw, 'UTF-8');
        $likeRegion    = '%' . $regionSearch . '%';

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function ($q) use ($specialtySearch, $likeSpecialtySearch) {
                $q->orWhereJsonContains('services', $specialtySearch)
                  ->orWhereRaw('LOWER(CAST(services AS CHAR)) LIKE ?', [$likeSpecialtySearch]);
            })
            ->where(function ($q) use ($likeRegion) {
                $q->whereRaw('LOWER(city_setByAdmin) LIKE ?', [$likeRegion])
                  ->orWhereRaw('LOWER(state_setByAdmin) LIKE ?', [$likeRegion])
                  // also region via practice locations
                  ->orWhereHas('practiceLocations', function ($lq) use ($likeRegion) {
                      $lq->whereRaw('LOWER(city) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(postal_code) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(address_line1) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(address_line2) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(country) LIKE ?', [$likeRegion])
                         ->orWhereRaw('LOWER(label) LIKE ?', [$likeRegion]);
                  });
            })
            ->get();

        $specialty = $specialtySearchRaw;
        $region    = $regionRaw;

        return view('results', compact('therapists', 'specialty', 'region'));
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
        if ($a === '' || $b === '') return max(strlen($a), strlen($b));
        return levenshtein($a, $b);
    }
}
