<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    public function __construct(private readonly WeatherService $weather) {}

    // GET /api/wc26/weather/{stadiumKey}
    // Ex: /api/wc26/weather/sofi  ou  /api/wc26/weather/metlife
    public function show(string $stadiumKey): JsonResponse
    {
        $data = $this->weather->getByStadiumKey($stadiumKey);

        if (isset($data['error'])) {
            // Retourner la liste des stades disponibles en cas d'erreur
            $available = collect($this->weather->getAllStadiums())
                ->pluck('key')
                ->values();

            return response()->json([
                'error'     => $data['error'],
                'available' => $available,
            ], 404);
        }

        return response()->json(['data' => $data]);
    }
}
