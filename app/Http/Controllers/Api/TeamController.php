<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiFootballService;
use App\Services\Wc2026ApiService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    // IDs API-Football des 48 équipes WC 2026 (vérifiés via WC 2022)
    // IDs API-Football des 48 équipes officielles WC 2026
    // Tirage au sort : 5 décembre 2025
    private const TEAM_IDS = [
        // Groupe A — Mexique (hôte)
        'MEX' => 16,   'RSA' => 73,   'KOR' => 18,   'CZE' => 63,
        // Groupe B — Canada (hôte)
        'CAN' => 96,   'BIH' => 20,   'QAT' => 35,   'SUI' => 17,
        // Groupe C
        'BRA' => 6,    'MAR' => 31,   'HAI' => 503,  'SCO' => 1020,
        // Groupe D — USA (hôte)
        'USA' => 2586, 'PAR' => 11,   'AUS' => 13,   'TUR' => 21,
        // Groupe E
        'GER' => 25,   'CUW' => 2608, 'CIV' => 40,   'ECU' => 56,
        // Groupe F
        'NED' => 1533, 'JPN' => 15,   'SWE' => 8,    'TUN' => 28,
        // Groupe G
        'BEL' => 1,    'EGY' => 23,   'IRN' => 29,   'NZL' => 145,
        // Groupe H
        'ESP' => 9,    'CPV' => 2545, 'KSA' => 36,   'URU' => 19,
        // Groupe I
        'FRA' => 2,    'SEN' => 34,   'IRQ' => 2531, 'NOR' => 5,
        // Groupe J
        'ARG' => 26,   'ALG' => 4,    'AUT' => 44,   'JOR' => 2532,
        // Groupe K
        'POR' => 27,   'COD' => 2538, 'UZB' => 2533, 'COL' => 12,
        // Groupe L
        'ENG' => 10,   'CRO' => 3,    'GHA' => 22,   'PAN' => 85,
    ];

    public function __construct(
        private readonly ApiFootballService $api,
        private readonly Wc2026ApiService   $wc26,
    ) {}

    // GET /api/wc26/stadiums
    public function stadiums(): JsonResponse
    {
        return response()->json($this->wc26->getStadiums());
    }

    // GET /api/wc26/teams — liste des équipes
    public function index(): JsonResponse
    {
        return response()->json([
            'count' => count(self::TEAM_IDS),
            'data'  => array_map(
                fn($code, $id) => ['code' => $code, 'api_id' => $id],
                array_keys(self::TEAM_IDS),
                array_values(self::TEAM_IDS)
            ),
        ]);
    }

    // GET /api/wc26/teams/{id} — fiche équipe
    public function show(string $id): JsonResponse
    {
        $apiId = self::TEAM_IDS[strtoupper($id)] ?? null;

        if (! $apiId) {
            return response()->json(['error' => "Équipe '$id' introuvable"], 404);
        }

        $teams = $this->api->searchTeam('');
        // Retourner les infos basiques
        return response()->json(['data' => ['code' => $id, 'api_id' => $apiId]]);
    }

    // GET /api/wc26/teams/{id}/players — joueurs de l'équipe
    public function players(string $id): JsonResponse
    {
        $apiId = self::TEAM_IDS[strtoupper($id)] ?? null;

        if (! $apiId) {
            return response()->json(['error' => "Équipe '$id' introuvable"], 404);
        }

        // 1. Squad (noms + numéros + positions)
        $squads = $this->api->getSquad($apiId);
        $squad  = $squads[0]['players'] ?? [];

        // 2. Stats individuelles pendant le tournoi
        $statsRaw = $this->api->getPlayerStats($apiId);

        // Indexer stats par player id
        $statsMap = collect($statsRaw)->keyBy(fn($p) => $p['player']['id'] ?? 0);

        $players = collect($squad)->map(function ($p) use ($statsMap) {
            $pid      = $p['id'] ?? 0;
            $stats    = $statsMap->get($pid);
            $s        = is_array($stats) ? ($stats['statistics'][0] ?? []) : [];
            $position = $this->mapPosition($p['position'] ?? 'Unknown');

            return [
                'id'          => $pid,
                'name'        => $p['name']   ?? '',
                'age'         => $p['age']    ?? null,
                'number'      => $p['number'] ?? null,
                'position'    => $position,
                'photo'       => $p['photo']  ?? null,
                'goals'       => (int)($s['goals']['total']       ?? 0),
                'assists'     => (int)($s['goals']['assists']     ?? 0),
                'appearances' => (int)($s['games']['appearences'] ?? 0),
                'minutes'     => (int)($s['games']['minutes']     ?? 0),
                'yellow_cards'=> (int)($s['cards']['yellow']      ?? 0),
                'red_cards'   => (int)($s['cards']['red']         ?? 0),
                'rating'      => isset($s['games']['rating']) && $s['games']['rating']
                                    ? round((float)$s['games']['rating'], 2)
                                    : null,
            ];
        })->sortBy(fn($p) => $this->posOrder($p['position']))->values();

        return response()->json([
            'team_code' => strtoupper($id),
            'api_id'    => $apiId,
            'count'     => $players->count(),
            'squad'     => $players,
        ]);
    }

    private function mapPosition(string $pos): string
    {
        return match(strtoupper($pos)) {
            'G', 'GK', 'GOALKEEPER' => 'Gardien',
            'D', 'DEF', 'DEFENDER'  => 'Défenseur',
            'M', 'MID', 'MIDFIELDER'=> 'Milieu',
            'F', 'ATT', 'ATTACKER', 'FORWARD' => 'Attaquant',
            default => $pos,
        };
    }

    private function posOrder(string $pos): int
    {
        return match($pos) {
            'Gardien'    => 1,
            'Défenseur'  => 2,
            'Milieu'     => 3,
            'Attaquant'  => 4,
            default      => 5,
        };
    }
}
