<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'API worldcup26.ir
 * Gratuite, sans clé, dédiée WC 2026
 * Endpoints : /get/games, /get/teams, /get/groups, /get/stadiums
 */
class Wc2026ApiService
{
    private const BASE = 'https://worldcup26.ir/get';

    private const PHASE_MAP = [
        'group'  => 'Groupes',
        'r32'    => 'Huitièmes',
        'r16'    => 'Quarts',
        'qf'     => 'Demi-finale',
        'sf'     => 'Finale',
        'third'  => 'Finale',
        'final'  => 'Finale',
    ];

    // ──────────────────────────────────────────────────────────
    //  Requête générique avec cache
    // ──────────────────────────────────────────────────────────
    private function get(string $endpoint, int $ttl = 300): array
    {
        $cacheKey = 'wc26api_' . $endpoint;

        return Cache::remember($cacheKey, $ttl, function () use ($endpoint) {
            $res = Http::timeout(10)->get(self::BASE . '/' . $endpoint);

            if ($res->failed()) {
                Log::warning("wc26 API error: {$endpoint}", ['status' => $res->status()]);
                return [];
            }

            return $res->json() ?? [];
        });
    }

    // ──────────────────────────────────────────────────────────
    //  Tous les matchs
    // ──────────────────────────────────────────────────────────
    public function getAllFixtures(): array
    {
        $raw      = $this->get('games', 180);
        $stadiums = $this->getStadiumsMap();
        $games    = $raw['games'] ?? [];

        return array_map(fn($g) => $this->formatGame($g, $stadiums), $games);
    }

    // ──────────────────────────────────────────────────────────
    //  Matchs en cours
    // ──────────────────────────────────────────────────────────
    public function getLiveFixtures(): array
    {
        // Force refresh court : 30s
        Cache::forget('wc26api_games');
        $raw      = $this->get('games', 30);
        $stadiums = $this->getStadiumsMap();
        $games    = $raw['games'] ?? [];

        return array_filter(
            array_map(fn($g) => $this->formatGame($g, $stadiums), $games),
            fn($g) => $g['is_live']
        );
    }

    // ──────────────────────────────────────────────────────────
    //  Classements par groupe
    // ──────────────────────────────────────────────────────────
    public function getStandings(): array
    {
        $raw   = $this->get('groups', 300);
        $teams = $this->getTeamsMap();
        $grps  = $raw['groups'] ?? [];

        $result = [];
        foreach ($grps as $grp) {
            $letter = $grp['name'];
            $result[$letter] = array_map(function ($entry) use ($teams) {
                $team = $teams[$entry['team_id']] ?? [];
                return [
                    'team'   => [
                        'name'     => $team['name_en'] ?? '',
                        'fifa_code'=> $team['fifa_code'] ?? '',
                        'flag'     => $team['flag'] ?? '',
                    ],
                    'all'    => [
                        'played' => (int) $entry['mp'],
                        'win'    => (int) $entry['w'],
                        'draw'   => (int) $entry['d'],
                        'lose'   => (int) $entry['l'],
                        'goals'  => ['for' => (int) $entry['gf'], 'against' => (int) $entry['ga']],
                    ],
                    'goalsDiff' => (int) $entry['gd'],
                    'points'    => (int) $entry['pts'],
                ];
            }, $grp['teams'] ?? []);
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────
    //  Équipes
    // ──────────────────────────────────────────────────────────
    public function getTeams(): array
    {
        $raw = $this->get('teams', 86400);
        return $raw['teams'] ?? [];
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers internes
    // ──────────────────────────────────────────────────────────
    private function getTeamsMap(): array
    {
        $raw = $this->get('teams', 86400);
        $map = [];
        foreach ($raw['teams'] ?? [] as $t) {
            $map[$t['id']] = $t;
        }
        return $map;
    }

    public function getStadiums(): array
    {
        $raw = $this->get('stadiums', 86400);
        return ['stadiums' => $raw['stadiums'] ?? []];
    }

    private function getStadiumsMap(): array
    {
        $raw = $this->get('stadiums', 86400);
        $map = [];
        foreach ($raw['stadiums'] ?? [] as $s) {
            $map[$s['id']] = $s;
        }
        return $map;
    }

    private function formatGame(array $g, array $stadiums): array
    {
        $finished    = strtoupper($g['finished'] ?? 'FALSE') !== 'FALSE';
        $elapsed     = $g['time_elapsed'] ?? 'notstarted';
        $isLive      = !$finished && !in_array($elapsed, ['notstarted', 'finished', '']);
        $type        = $g['type'] ?? 'group';
        $phase       = self::PHASE_MAP[$type] ?? 'Groupes';
        $group       = ($type === 'group') ? ($g['group'] ?? null) : null;

        // Heure locale depuis local_date (format MM/DD/YYYY HH:mm)
        $dateRaw  = $g['local_date'] ?? '';
        $dateParsed = date_create_from_format('m/d/Y H:i', $dateRaw);
        $date     = $dateParsed ? $dateParsed->format('Y-m-d') : null;
        $time     = $dateParsed ? $dateParsed->format('H:i')   : null;

        $stadium  = $stadiums[$g['stadium_id']] ?? [];

        // Scores : '0' quand pas commencé — on retourne null dans ce cas
        $homeScore = $finished || $isLive ? (int) $g['home_score'] : null;
        $awayScore = $finished || $isLive ? (int) $g['away_score'] : null;

        $roundLabel = match($type) {
            'r32'   => 'Round of 32',
            'r16'   => 'Quarter-final',
            'qf'    => 'Semi-final',
            'sf'    => 'Semi-final',
            'third' => '3rd Place Final',
            'final' => 'Final',
            default => 'Group ' . ($g['group'] ?? ''),
        };

        return [
            'id'      => (int) $g['id'],
            'date'    => $date,
            'time'    => $time,
            'stadium' => $stadium['name_en']   ?? ($stadium['name'] ?? null),
            'city'    => $stadium['city_en']   ?? ($stadium['city'] ?? null),
            'phase'   => $phase,
            'group'   => $group,
            'round'   => $roundLabel,
            'status'  => $finished ? 'FT' : ($isLive ? '1H' : 'NS'),
            'elapsed' => is_numeric($elapsed) ? (int) $elapsed : null,
            'is_live' => $isLive,
            'is_third_place' => $type === 'third',
            'home'    => [
                'id'     => (int) $g['home_team_id'],
                'name'   => $g['home_team_name_en'] ?? null,
                'logo'   => null,
                'winner' => $finished ? ($homeScore > $awayScore) : null,
            ],
            'away'    => [
                'id'     => (int) $g['away_team_id'],
                'name'   => $g['away_team_name_en'] ?? null,
                'logo'   => null,
                'winner' => $finished ? ($awayScore > $homeScore) : null,
            ],
            'score'   => [
                'home' => $homeScore,
                'away' => $awayScore,
            ],
        ];
    }
}
