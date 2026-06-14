<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchOverride;
use App\Services\Wc2026ApiService;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    public function __construct(private readonly Wc2026ApiService $api) {}

    public function index(): JsonResponse
    {
        $fixtures  = $this->api->getAllFixtures();
        $overrides = MatchOverride::all()->keyBy('fixture_id');

        $fixtures = array_map(function ($f) use ($overrides) {
            return $this->applyOverride($f, $overrides->get($f['id']));
        }, $fixtures);

        return response()->json([
            'count'  => count($fixtures),
            'source' => 'worldcup26.ir',
            'data'   => array_values($fixtures),
        ]);
    }

    public function live(): JsonResponse
    {
        $live      = $this->api->getLiveFixtures();
        $overrides = MatchOverride::all()->keyBy('fixture_id');

        $live = array_map(fn($f) => $this->applyOverride($f, $overrides->get($f['id'])), $live);

        return response()->json([
            'count'      => count($live),
            'is_live'    => count($live) > 0,
            'updated_at' => now()->toDateTimeString(),
            'data'       => array_values($live),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $detail = $this->api->getMatchDetail($id);

        if (!$detail) {
            return response()->json(['error' => 'Match introuvable'], 404);
        }

        $override = MatchOverride::where('fixture_id', $id)->first();
        $detail   = $this->applyOverride($detail, $override);

        return response()->json(['data' => $detail]);
    }

    private function applyOverride(array $fixture, ?MatchOverride $override): array
    {
        if (!$override) return $fixture;

        if ($override->home_score !== null) {
            $fixture['score']['home'] = $override->home_score;
            $fixture['score']['away'] = $override->away_score;
        }
        if ($override->status) {
            $fixture['status'] = $override->status;
            if ($override->status === 'FT') {
                $fixture['is_live'] = false;
            } elseif (in_array($override->status, ['1H','2H','ET','PEN'])) {
                $fixture['is_live'] = true;
            }
        }
        if ($override->notes) {
            $fixture['notes'] = $override->notes;
        }

        return $fixture;
    }
}
