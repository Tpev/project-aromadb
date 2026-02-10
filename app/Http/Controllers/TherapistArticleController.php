<?php

namespace App\Http\Controllers;

use App\Models\TherapistArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TherapistArticleController extends Controller
{
	use AuthorizesRequests;
    /*
    |--------------------------------------------------------------------------
    | PUBLIC
    |--------------------------------------------------------------------------
    */

    public function publicIndex(User $therapist)
    {
        $articles = TherapistArticle::query()
            ->where('user_id', $therapist->id)
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('pro.articles.index', [
            'therapist' => $therapist,
            'articles' => $articles,
        ]);
    }

    public function publicShow(User $therapist, string $articleSlug)
    {
        $article = TherapistArticle::query()
            ->where('user_id', $therapist->id)
            ->where('slug', $articleSlug)
            ->published()
            ->firstOrFail();

        // Simple view counter (not unique)
        $article->increment('views');

        return view('pro.articles.show', [
            'therapist' => $therapist,
            'article' => $article,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD PRO (CRUD)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $this->authorize('viewAny', TherapistArticle::class);

        $q = TherapistArticle::query()->where('user_id', $request->user()->id);

        if ($status = $request->string('status')->toString()) {
            if (in_array($status, ['draft', 'published'], true)) {
                $q->where('status', $status);
            }
        }

        if ($search = trim($request->string('q')->toString())) {
            $q->where(function ($qq) use ($search) {
                $qq->where('title', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }

        $articles = $q->orderByDesc('updated_at')->paginate(20)->withQueryString();

        return view('dashboard-pro.articles.index', compact('articles'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', TherapistArticle::class);

        return view('dashboard-pro.articles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', TherapistArticle::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug'  => ['nullable', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content_html' => ['nullable', 'string'],
            'content_json' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string'], // comma separated
            'cover' => ['nullable', 'image', 'max:5120'],
        ]);

        $userId = $request->user()->id;

        $title = $validated['title'];
        $slugInput = trim((string)($validated['slug'] ?? ''));

        $slug = $slugInput !== ''
            ? TherapistArticle::makeUniqueSlugForUser($userId, $slugInput)
            : TherapistArticle::makeUniqueSlugForUser($userId, $title);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store("therapist_articles/{$userId}/covers", 'public');
        }

        $contentHtml = (string)($validated['content_html'] ?? '');
        $readingTime = TherapistArticle::estimateReadingTime($contentHtml);

        $publishedAt = null;
        if (($validated['status'] ?? 'draft') === 'published') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : now();
        }

        $article = TherapistArticle::create([
            'user_id' => $userId,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content_html' => $contentHtml,
            'content_json' => $validated['content_json'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'cover_path' => $coverPath,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
            'tags' => $this->parseTags($validated['tags'] ?? null),
            'reading_time' => $readingTime,
        ]);

        return redirect()->route('dashboardpro.articles.edit', $article)->with('success', 'Article créé.');
    }

    public function show(Request $request, TherapistArticle $article)
    {
        $this->authorize('view', $article);

        return view('dashboard-pro.articles.show', compact('article'));
    }

    public function edit(Request $request, TherapistArticle $article)
    {
        $this->authorize('update', $article);

        return view('dashboard-pro.articles.edit', compact('article'));
    }

    public function update(Request $request, TherapistArticle $article)
    {
        $this->authorize('update', $article);

        $userId = $request->user()->id;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug'  => ['nullable', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content_html' => ['nullable', 'string'],
            'content_json' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'max:5120'],
            'remove_cover' => ['nullable', 'boolean'],
        ]);

        $title = $validated['title'];
        $slugInput = trim((string)($validated['slug'] ?? ''));

        $slug = $slugInput !== ''
            ? TherapistArticle::makeUniqueSlugForUser($userId, $slugInput, $article->id)
            : TherapistArticle::makeUniqueSlugForUser($userId, $title, $article->id);

        if ($request->boolean('remove_cover') && $article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
            $article->cover_path = null;
        }

        if ($request->hasFile('cover')) {
            if ($article->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }
            $article->cover_path = $request->file('cover')->store("therapist_articles/{$userId}/covers", 'public');
        }

        $contentHtml = (string)($validated['content_html'] ?? '');
        $readingTime = TherapistArticle::estimateReadingTime($contentHtml);

        $publishedAt = $article->published_at;
        if (($validated['status'] ?? 'draft') === 'published') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : ($publishedAt ?? now());
        } else {
            $publishedAt = null;
        }

        $article->fill([
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content_html' => $contentHtml,
            'content_json' => $validated['content_json'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
            'tags' => $this->parseTags($validated['tags'] ?? null),
            'reading_time' => $readingTime,
        ]);

        $article->save();

        return redirect()->route('dashboardpro.articles.edit', $article)->with('success', 'Article mis à jour.');
    }

    public function destroy(Request $request, TherapistArticle $article)
    {
        $this->authorize('delete', $article);

        if ($article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
        }

        $article->delete();

        return redirect()->route('dashboardpro.articles.index')->with('success', 'Article supprimé.');
    }

    /*
    |--------------------------------------------------------------------------
    | QUILL IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    public function uploadImage(Request $request)
    {
        $this->authorize('create', TherapistArticle::class);

        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $userId = $request->user()->id;

        $path = $request->file('image')->store("therapist_articles/{$userId}/inline", 'public');
        $url  = Storage::disk('public')->url($path);

        return response()->json(['url' => $url]);
    }

    private function parseTags($raw): ?array
    {
        $str = trim((string) $raw);
        if ($str === '') return null;

        $parts = array_map('trim', explode(',', $str));
        $parts = array_filter($parts, fn($t) => $t !== '');
        $parts = array_values(array_unique($parts));

        return empty($parts) ? null : $parts;
    }
}
