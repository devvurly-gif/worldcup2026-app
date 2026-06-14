<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MatchStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchStreamController extends Controller
{
    // GET /api/wc26/streams/{fixtureId}
    public function show(int $fixtureId): JsonResponse
    {
        $stream = MatchStream::where('fixture_id', $fixtureId)
                             ->where('is_active', true)
                             ->first();

        return response()->json([
            'fixture_id' => $fixtureId,
            'stream_url' => $stream?->stream_url ?? '',
            'is_active'  => $stream?->is_active ?? false,
        ]);
    }

    // GET /api/wc26/streams — tous les streams actifs
    public function index(): JsonResponse
    {
        $streams = MatchStream::where('is_active', true)->get();
        return response()->json(['data' => $streams]);
    }

    // POST /api/wc26/streams/{fixtureId}  (admin)
    public function upsert(Request $request, int $fixtureId): JsonResponse
    {
        $validated = $request->validate([
            'stream_url' => 'required|string|max:1000',
            'is_active'  => 'boolean',
        ]);

        $stream = MatchStream::updateOrCreate(
            ['fixture_id' => $fixtureId],
            [
                'stream_url' => $validated['stream_url'],
                'is_active'  => $validated['is_active'] ?? true,
            ]
        );

        return response()->json(['data' => $stream], 200);
    }

    // DELETE /api/wc26/streams/{fixtureId}  (admin)
    public function destroy(int $fixtureId): JsonResponse
    {
        MatchStream::where('fixture_id', $fixtureId)->delete();
        return response()->json(['deleted' => true]);
    }
}
