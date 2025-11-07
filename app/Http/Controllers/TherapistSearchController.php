<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

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

        // Location filter
        if (!empty($data['location'])) {
            $loc = trim($data['location']);
            $base->where(function ($q) use ($loc) {
                $q->where('city_setByAdmin', 'like', '%' . $loc . '%')
                  ->orWhere('state_setByAdmin', 'like', '%' . $loc . '%');
            });
        }

        // Specialty filter (supports JSON array or plain text)
        if (!empty($data['specialty'])) {
            $spec = trim($data['specialty']);
            $base->where(function ($q) use ($spec) {
                $q->orWhereJsonContains('services', $spec)
                  ->orWhere('services', 'like', '%' . $spec . '%');
            });
        }

        // No name provided => fetch as-is
        if (empty($data['name'])) {
            $therapists = $base->get();
            $specialty  = $data['specialty'] ?? null;
            $region     = $data['location'] ?? null;
            return view('results', compact('therapists', 'specialty', 'region'));
        }

        // -------- Fuzzy Name Search (small typos tolerated) --------
        $nameTerm = trim($data['name']);
        $normTerm = $this->normalize($nameTerm);

        // Prefilter in DB for performance: LIKE + SOUNDEX
        $prefilter = (clone $base)
            ->where(function ($q) use ($nameTerm) {
                $q->where('name', 'like', '%' . $nameTerm . '%')
                  ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$nameTerm]);
            })
            ->limit(250)
            ->get();

        // If prefilter missed (rare), widen to avoid empty results
        if ($prefilter->isEmpty()) {
            $prefilter = (clone $base)->limit(500)->get();
        }

        // Compute distances (Levenshtein) on normalized strings
        $scored = $prefilter->map(function ($t) use ($normTerm) {
            $name = $t->name ?? '';
            $normName = $this->normalize($name);

            // token min distance handles "Dr Jean Martin" vs "Martin"
            $tokens = preg_split('/\s+/', $normName) ?: [];
            $distances = [$this->lev($normName, $normTerm)];
            foreach ($tokens as $tok) {
                if ($tok !== '') {
                    $distances[] = $this->lev($tok, $normTerm);
                }
            }
            $minDist = min($distances);

            // small boosts for starts-with / contains
            $startsBoost   = Str::startsWith($normName, $normTerm) ? -2 : 0;
            $containsBoost = Str::contains($normName, $normTerm) ? -1 : 0;

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
        $specialtySearch = str_replace('-', ' ', $specialty);

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function ($q) use ($specialtySearch) {
                $q->orWhereJsonContains('services', $specialtySearch)
                  ->orWhere('services', 'like', '%' . $specialtySearch . '%');
            })
            ->get();

        return view('results', compact('therapists', 'specialty'));
    }

    /**
     * /region-{region}
     */
    public function filterByRegion($region)
    {
        $regionSearch = mb_convert_case(str_replace('-', ' ', $region), MB_CASE_TITLE, 'UTF-8');

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function ($q) use ($regionSearch) {
                $q->where('city_setByAdmin', 'like', '%' . $regionSearch . '%')
                  ->orWhere('state_setByAdmin', 'like', '%' . $regionSearch . '%');
            })
            ->get();

        return view('results', compact('therapists', 'region'));
    }

    /**
     * /practicien-{specialty}-region-{region}
     */
    public function filterBySpecialtyRegion($specialty, $region)
    {
        $specialtySearch = str_replace('-', ' ', $specialty);
        $regionSearch = mb_convert_case(str_replace('-', ' ', $region), MB_CASE_TITLE, 'UTF-8');

        $therapists = User::query()
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true)
            ->where(function ($q) use ($specialtySearch) {
                $q->orWhereJsonContains('services', $specialtySearch)
                  ->orWhere('services', 'like', '%' . $specialtySearch . '%');
            })
            ->where(function ($q) use ($regionSearch) {
                $q->where('city_setByAdmin', 'like', '%' . $regionSearch . '%')
                  ->orWhere('state_setByAdmin', 'like', '%' . $regionSearch . '%');
            })
            ->get();

        return view('results', compact('therapists', 'specialty', 'region'));
    }

    // ----------------- Helpers -----------------

    /** Normalize to lowercase ASCII, collapse spaces */
    private function normalize(string $s): string
    {
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $t = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
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
