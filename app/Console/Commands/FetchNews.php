<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchNews extends Command
{
    protected $signature = 'news:fetch {--force : Re-fetch even recently fetched sources}';
    protected $description = 'AI News Agent — fetches World Cup 2026 articles from multiple RSS sources';

    // RSS sources: [url, source_name, category, lang]
    private const SOURCES = [
        // Google News FR — World Cup 2026
        ['https://news.google.com/rss/search?q=Coupe+du+monde+2026&hl=fr&gl=FR&ceid=FR:fr', 'Google News', 'general', 'fr'],
        ['https://news.google.com/rss/search?q=FIFA+World+Cup+2026&hl=fr&gl=FR&ceid=FR:fr', 'Google News', 'general', 'fr'],
        ['https://news.google.com/rss/search?q=Maroc+football+2026&hl=fr&gl=FR&ceid=FR:fr', 'Google News', 'maroc', 'fr'],
        ['https://news.google.com/rss/search?q=equipe+nationale+football+2026&hl=fr&gl=FR&ceid=FR:fr', 'Google News', 'equipes', 'fr'],

        // L'Équipe RSS
        ['https://www.lequipe.fr/rss/actu_rss_Football.xml', "L'Équipe", 'general', 'fr'],

        // BBC Sport RSS
        ['https://feeds.bbci.co.uk/sport/football/rss.xml', 'BBC Sport', 'general', 'en'],

        // Goal.com RSS
        ['https://www.goal.com/feeds/en/news', 'Goal.com', 'general', 'en'],

        // Marca RSS
        ['https://e00-marca.uecdn.es/rss/futbol/selecciones.xml', 'Marca', 'equipes', 'es'],
    ];

    public function handle(): int
    {
        $this->info('🤖 News Agent starting...');
        $total = 0;
        $new   = 0;

        foreach (self::SOURCES as [$url, $sourceName, $category, $lang]) {
            try {
                $articles = $this->fetchRss($url, $sourceName, $category, $lang);
                foreach ($articles as $article) {
                    $total++;
                    $saved = $this->saveArticle($article);
                    if ($saved) $new++;
                }
                $this->line("  ✓ {$sourceName} ({$lang}) — " . count($articles) . " articles");
            } catch (\Throwable $e) {
                $this->warn("  ✗ {$sourceName}: " . $e->getMessage());
            }
        }

        // Cleanup: delete articles older than 30 days
        $deleted = NewsArticle::where('published_at', '<', now()->subDays(30))->delete();

        $this->info("✅ Done — {$new} new articles (total fetched: {$total}, cleaned: {$deleted} old)");
        return Command::SUCCESS;
    }

    private function fetchRss(string $url, string $source, string $category, string $lang): array
    {
        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NewsBot/1.0)'])
            ->get($url);

        if (!$response->successful()) return [];

        $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml) return [];

        $articles = [];
        $items    = $xml->channel->item ?? $xml->entry ?? [];

        foreach ($items as $item) {
            $title   = trim((string) ($item->title ?? ''));
            $link    = trim((string) ($item->link ?? $item->id ?? ''));
            $desc    = trim(strip_tags((string) ($item->description ?? $item->summary ?? '')));
            $pubDate = (string) ($item->pubDate ?? $item->published ?? $item->updated ?? '');
            $image   = $this->extractImage($item, $desc);

            if (!$title || !$link) continue;

            // Filter: only WC2026-relevant articles for non-Google sources
            if (!str_contains($url, 'news.google.com')) {
                $relevant = ['world cup', 'coupe du monde', 'mundial', 'wc2026', 'wc26',
                             'fifa', '2026', 'maroc', 'morocco', 'equipe de france',
                             'selection', 'selecci'];
                $haystack = strtolower($title . ' ' . $desc);
                if (!collect($relevant)->contains(fn($k) => str_contains($haystack, $k))) {
                    continue;
                }
            }

            $articles[] = [
                'title'        => $title,
                'url'          => $link,
                'description'  => Str::limit($desc, 300),
                'image'        => $image,
                'source'       => $source,
                'category'     => $category,
                'lang'         => $lang,
                'published_at' => $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : now(),
            ];
        }

        return $articles;
    }

    private function extractImage(\SimpleXMLElement $item, string $desc): ?string
    {
        // 1. media:content
        $media = $item->children('media', true);
        if ($media && isset($media->content)) {
            $url = (string) $media->content->attributes()['url'] ?? '';
            if ($url) return $url;
        }

        // 2. enclosure
        if (isset($item->enclosure)) {
            $url = (string) $item->enclosure->attributes()['url'] ?? '';
            if ($url && str_contains($url, 'http')) return $url;
        }

        // 3. First <img> in description
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) ($item->description ?? ''), $m)) {
            return $m[1];
        }

        // 4. og:image from Google News redirect (skip — too slow)
        return null;
    }

    private function saveArticle(array $data): bool
    {
        $slug = $this->makeSlug($data['title'], $data['published_at']);

        // Skip duplicates by URL or slug
        if (NewsArticle::where('url', $data['url'])->orWhere('slug', $slug)->exists()) {
            return false;
        }

        NewsArticle::create([...$data, 'slug' => $slug]);
        return true;
    }

    private function makeSlug(string $title, ?string $date): string
    {
        $base = Str::slug(Str::limit($title, 80));
        $suffix = $date ? '-' . date('ymd', strtotime($date)) : '';
        return $base . $suffix;
    }
}
