<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchOverride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchOverrideController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => MatchOverride::all()]);
    }

    public function show(int $fixtureId): JsonResponse
    {
        $o = MatchOverride::where('fixture_id', $fixtureId)->first();
        if (!$o) return response()->json(['data' => null], 404);
        return response()->json(['data' => $o]);
    }

    public function upsert(Request $request, int $fixtureId): JsonResponse
    {
        $validated = $request->validate([
            'home_score' => 'nullable|integer|min:0|max:99',
            'away_score' => 'nullable|integer|min:0|max:99',
            'status'     => 'nullable|string|max:10',
            'notes'      => 'nullable|string|max:500',
        ]);

        $override = MatchOverride::updateOrCreate(
            ['fixture_id' => $fixtureId],
            $validated
        );

        return response()->json(['data' => $override]);
    }

    public function destroy(int $fixtureId): JsonResponse
    {
        MatchOverride::where('fixture_id', $fixtureId)->delete();
        return response()->json(['deleted' => true]);
    }
}
