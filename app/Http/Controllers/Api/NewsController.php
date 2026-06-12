<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    private const CATEGORIES = ['all', 'maroc', 'equipes', 'scores', 'general'];

    /** GET /api/news?cat=all&lang=fr&page=1 */
    public function index(): JsonResponse
    {
        $cat    = request('cat', 'all');
        $lang   = request('lang', 'fr');
        $page   = max(1, (int) request('page', 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        $query = NewsArticle::orderByDesc('published_at');

        if ($cat !== 'all' && in_array($cat, self::CATEGORIES)) {
            $query->where('category', $cat);
        }

        // Show French + English articles (bilingual feed)
        // $query->where('lang', $lang);

        $total    = $query->count();
        $articles = $query->skip($offset)->take($limit)->get();

        return response()->json([
            'category' => $cat,
            'count'    => $total,
            'page'     => $page,
            'articles' => $articles->map(fn($a) => $this->format($a)),
        ]);
    }

    /** GET /api/news/{slug} */
    public function show(string $slug): JsonResponse
    {
        $article = NewsArticle::where('slug', $slug)->first();

        if (!$article) {
            return response()->json(['error' => 'Article introuvable'], 404);
        }

        $related = NewsArticle::where('category', $article->category)
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->take(4)
            ->get();

        return response()->json([
            ...$this->format($article),
            'content' => $article->description,
            'related' => $related->map(fn($a) => $this->format($a)),
        ]);
    }

    private function format(NewsArticle $a): array
    {
        return [
            'slug'         => $a->slug,
            'title'        => $a->title,
            'description'  => $a->description,
            'image'        => $a->image ?: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80',
            'source'       => $a->source,
            'url'          => $a->url,
            'category'     => $a->category,
            'lang'         => $a->lang,
            'published_at' => $a->published_at?->toIsoString(),
        ];
    }
}
