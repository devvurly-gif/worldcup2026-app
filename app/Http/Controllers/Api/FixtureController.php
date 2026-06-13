<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Wc2026ApiService;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    public function __construct(private readonly Wc2026ApiService $api) {}

    // GET /api/wc26/fixtures — tous les matchs
    public function index(): JsonResponse
    {
        $fixtures = $this->api->getAllFixtures();

        return response()->json([
            'count'  => count($fixtures),
            'source' => 'worldcup26.ir',
            'data'   => array_values($fixtures),
        ]);
    }

    // GET /api/wc26/fixtures/live — matchs en cours
    public function live(): JsonResponse
    {
        $live = $this->api->getLiveFixtures();

        return response()->json([
            'count'      => count($live),
            'is_live'    => count($live) > 0,
            'updated_at' => now()->toDateTimeString(),
            'data'       => array_values($live),
        ]);
    }

    // GET /api/wc26/fixtures/{id} — détail d'un match avec events
    public function show(int $id): JsonResponse
    {
        $detail = $this->api->getMatchDetail($id);

        if (!$detail) {
            return response()->json(['error' => 'Match introuvable'], 404);
        }

        return response()->json(['data' => $detail]);
    }
}
