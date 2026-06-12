<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiFootballService
{
    private const BASE_URL   = 'https://v3.football.api-sports.io';
    private const LEAGUE_ID  = 1;
    private const SEASON     = 2026;

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.apifootball.key', '');
    }

    // ──────────────────────────────────────────────────────────
    //  Requête générique avec cache
    // ──────────────────────────────────────────────────────────
    private function get(string $endpoint, array $params = [], int $ttl = 300): array
    {
        $cacheKey = 'apifootball_' . md5($endpoint . serialize($params));

        return Cache::remember($cacheKey, $ttl, function () use ($endpoint, $params) {
            $response = Http::withHeaders([
                'x-apisports-key' => $this->apiKey,
                'Accept'          => 'application/json',
            ])->get(self::BASE_URL . $endpoint, $params);

            if ($response->failed()) {
                Log::error("API-Football error: {$endpoint}", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            return $response->json('response', []);
        });
    }

    // ──────────────────────────────────────────────────────────
    //  Tous les matchs WC 2026
    // ──────────────────────────────────────────────────────────
    public function getAllFixtures(): array
    {
        $ttl = (int) config('cache.ttl.fixtures', 300);

        return $this->get('/fixtures', [
            'league' => self::LEAGUE_ID,
            'season' => self::SEASON,
        ], $ttl);
    }

    // ──────────────────────────────────────────────────────────
    //  Matchs en direct
    // ──────────────────────────────────────────────────────────
    public function getLiveFixtures(): array
    {
        $ttl = (int) config('cache.ttl.live', 60);

        // Cache court car on veut du quasi-temps-réel
        $cacheKey = 'apifootball_live_' . date('Y-m-d-H-i');
        Cache::forget($cacheKey);  // force refresh à chaque minute

        return $this->get('/fixtures', [
            'live'   => 'all',
            'league' => self::LEAGUE_ID,
        ], $ttl);
    }

    // ──────────────────────────────────────────────────────────
    //  Détail d'un match (events, lineups, stats)
    // ──────────────────────────────────────────────────────────
    public function getFixtureDetail(int $fixtureId): array
    {
        $data = $this->get('/fixtures', ['id' => $fixtureId], 120);
        return $data[0] ?? [];
    }

    // ──────────────────────────────────────────────────────────
    //  Classements des groupes
    // ──────────────────────────────────────────────────────────
    public function getStandings(): array
    {
        $ttl = (int) config('cache.ttl.standings', 300);

        return $this->get('/standings', [
            'league' => self::LEAGUE_ID,
            'season' => self::SEASON,
        ], $ttl);
    }

    // ──────────────────────────────────────────────────────────
    //  Joueurs d'une équipe (squad)
    // ──────────────────────────────────────────────────────────
    public function getSquad(int $teamId): array
    {
        $ttl = (int) config('cache.ttl.players', 86400); // 24h

        return $this->get('/players/squads', [
            'team' => $teamId,
        ], $ttl);
    }

    // ──────────────────────────────────────────────────────────
    //  Stats joueurs (buts, passes, cartons)
    // ──────────────────────────────────────────────────────────
    public function getPlayerStats(int $teamId): array
    {
        $ttl = (int) config('cache.ttl.players', 86400);

        return $this->get('/players', [
            'team'   => $teamId,
            'league' => self::LEAGUE_ID,
            'season' => self::SEASON,
        ], $ttl);
    }

    // ──────────────────────────────────────────────────────────
    //  Recherche d'une équipe par nom
    // ──────────────────────────────────────────────────────────
    public function searchTeam(string $name): array
    {
        return $this->get('/teams', ['search' => $name], 3600);
    }

    // ──────────────────────────────────────────────────────────
    //  Vider le cache (admin)
    // ──────────────────────────────────────────────────────────
    public function clearCache(): void
    {
        Cache::flush();
    }
}
