<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    private const QUERIES = [
        'all'     => 'FIFA World Cup 2026',
        'maroc'   => 'Morocco World Cup 2026 Maroc',
        'equipes' => 'World Cup 2026 teams soccer',
        'scores'  => 'World Cup 2026 match results goals',
    ];

    /** GET /api/news?cat=all&lang=fr */
    public function index(): JsonResponse
    {
        $cat  = in_array(request('cat'), array_keys(self::QUERIES)) ? request('cat') : 'all';
        $lang = request('lang', 'fr');

        $cacheKey = "news_{$cat}_{$lang}";

        $articles = Cache::remember($cacheKey, 1800, function () use ($cat, $lang) {
            return $this->fetchNews(self::QUERIES[$cat], $lang);
        });

        return response()->json([
            'category' => $cat,
            'count'    => count($articles),
            'articles' => $articles,
        ]);
    }

    /** GET /api/news/:slug — single article by slug */
    public function show(string $slug): JsonResponse
    {
        // Search all categories for the slug
        foreach (array_keys(self::QUERIES) as $cat) {
            foreach (['fr', 'en'] as $lang) {
                $cacheKey = "news_{$cat}_{$lang}";
                $articles = Cache::get($cacheKey, []);
                $article  = collect($articles)->first(fn($a) => $a['slug'] === $slug);
                if ($article) {
                    return response()->json($article);
                }
            }
        }
        return response()->json(['error' => 'Article introuvable'], 404);
    }

    private function fetchNews(string $query, string $lang): array
    {
        try {
            $hl  = $lang === 'fr' ? 'fr' : 'en';
            $gl  = $lang === 'fr' ? 'FR' : 'US';
            $url = "https://news.google.com/rss/search?q=" . urlencode($query) . "&hl={$hl}&gl={$gl}&ceid={$gl}:{$hl}";

            $response = Http::timeout(10)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; WorldCup2026Bot/1.0)',
            ])->get($url);

            if (!$response->ok()) return [];

            $xml      = simplexml_load_string($response->body());
            $items    = $xml->channel->item ?? [];
            $articles = [];

            foreach ($items as $item) {
                $title       = (string) $item->title;
                $link        = (string) $item->link;
                $pubDate     = (string) $item->pubDate;
                $description = (string) $item->description;
                $source      = (string) ($item->source ?? '');

                // Extract image from description HTML
                $image = $this->extractImage($description);

                // Clean description text
                $cleanDesc = strip_tags($description);
                $cleanDesc = html_entity_decode($cleanDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cleanDesc = trim(preg_replace('/\s+/', ' ', $cleanDesc));

                // Source from title (Google News format: "Title - Source")
                if (!$source && str_contains($title, ' - ')) {
                    $parts  = explode(' - ', $title);
                    $source = array_pop($parts);
                    $title  = implode(' - ', $parts);
                }

                $slug = Str::slug(Str::limit($title, 80)) . '-' . substr(md5($link), 0, 6);

                $articles[] = [
                    'slug'        => $slug,
                    'title'       => $title,
                    'description' => Str::limit($cleanDesc, 200),
                    'content'     => $cleanDesc,
                    'image'       => $image ?: "https://source.unsplash.com/800x450/?soccer,worldcup&sig=" . crc32($link),
                    'source'      => $source ?: 'Google News',
                    'url'         => $link,
                    'published_at'=> $pubDate ? date('Y-m-d\TH:i:s\Z', strtotime($pubDate)) : now()->toIsoString(),
                    'category'    => 'Actualités',
                ];
            }

            return array_slice($articles, 0, 30);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function extractImage(string $html): ?string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $m)) {
            return $m[1];
        }
        return null;
    }
}
