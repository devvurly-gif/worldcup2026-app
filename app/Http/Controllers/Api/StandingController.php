<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Wc2026ApiService;
use Illuminate\Http\JsonResponse;

class StandingController extends Controller
{
    public function __construct(private readonly Wc2026ApiService $api) {}

    // GET /api/wc26/standings
    public function index(): JsonResponse
    {
        $groups = $this->api->getStandings();

        return response()->json([
            'updated_at' => now()->toDateTimeString(),
            'groups'     => $groups,
        ]);
    }

    // GET /api/wc26/standings/{group} — ex: /standings/A
    public function group(string $group): JsonResponse
    {
        $groups = $this->api->getStandings();
        $key    = strtoupper($group);

        if (!isset($groups[$key])) {
            return response()->json(['error' => "Groupe '$key' introuvable"], 404);
        }

        return response()->json([
            'group' => $key,
            'table' => $groups[$key],
        ]);
    }
}
