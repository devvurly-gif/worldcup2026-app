<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    private const BASE_URL = 'https://api.openweathermap.org/data/2.5/weather';

    // Coordonnées GPS des 16 stades WC 2026
    private const STADIUMS = [
        'sofi'        => ['name' => 'SoFi Stadium',             'city' => 'Los Angeles',   'lat' => 33.9534,  'lon' => -118.3392, 'tz' => 'America/Los_Angeles'],
        'metlife'     => ['name' => 'MetLife Stadium',           'city' => 'New York',      'lat' => 40.8135,  'lon' => -74.0744,  'tz' => 'America/New_York'],
        'att'         => ['name' => 'AT&T Stadium',              'city' => 'Dallas',        'lat' => 32.7480,  'lon' => -97.0927,  'tz' => 'America/Chicago'],
        'nrg'         => ['name' => 'NRG Stadium',               'city' => 'Houston',       'lat' => 29.6847,  'lon' => -95.4107,  'tz' => 'America/Chicago'],
        'hardrock'    => ['name' => 'Hard Rock Stadium',         'city' => 'Miami',         'lat' => 25.9580,  'lon' => -80.2389,  'tz' => 'America/New_York'],
        'levis'       => ['name' => "Levi's Stadium",            'city' => 'San Francisco', 'lat' => 37.4032,  'lon' => -121.9698, 'tz' => 'America/Los_Angeles'],
        'gillette'    => ['name' => 'Gillette Stadium',          'city' => 'Boston',        'lat' => 42.0909,  'lon' => -71.2643,  'tz' => 'America/New_York'],
        'empower'     => ['name' => 'Empower Field',             'city' => 'Denver',        'lat' => 39.7439,  'lon' => -105.0201, 'tz' => 'America/Denver'],
        'arrowhead'   => ['name' => 'Arrowhead Stadium',         'city' => 'Kansas City',   'lat' => 39.0489,  'lon' => -94.4839,  'tz' => 'America/Chicago'],
        'mercedesbenz'=> ['name' => 'Mercedes-Benz Stadium',     'city' => 'Atlanta',       'lat' => 33.7553,  'lon' => -84.4006,  'tz' => 'America/New_York'],
        'bcplace'     => ['name' => 'BC Place',                  'city' => 'Vancouver',     'lat' => 49.2768,  'lon' => -123.1115, 'tz' => 'America/Vancouver'],
        'bmo'         => ['name' => 'BMO Field',                 'city' => 'Toronto',       'lat' => 43.6332,  'lon' => -79.4178,  'tz' => 'America/Toronto'],
        'azteca'      => ['name' => 'Estadio Azteca',            'city' => 'Mexico City',   'lat' => 19.3029,  'lon' => -99.1505,  'tz' => 'America/Mexico_City'],
        'bbva'        => ['name' => 'Estadio BBVA',              'city' => 'Monterrey',     'lat' => 25.6694,  'lon' => -100.2441, 'tz' => 'America/Monterrey'],
        'universitario'=> ['name'=> 'Estadio Universitario',     'city' => 'Monterrey',     'lat' => 25.7269,  'lon' => -100.3118, 'tz' => 'America/Monterrey'],
        'cuauhtemoc'  => ['name' => 'Estadio Cuauhtémoc',        'city' => 'Puebla',        'lat' => 19.0208,  'lon' => -98.2145,  'tz' => 'America/Mexico_City'],
    ];

    // Mapping nom de stade → clé
    private const NAME_MAP = [
        'SoFi Stadium'           => 'sofi',
        'MetLife Stadium'        => 'metlife',
        'AT&T Stadium'           => 'att',
        'NRG Stadium'            => 'nrg',
        'Hard Rock Stadium'      => 'hardrock',
        "Levi's Stadium"         => 'levis',
        'Gillette Stadium'       => 'gillette',
        'Empower Field'          => 'empower',
        'Arrowhead Stadium'      => 'arrowhead',
        'Mercedes-Benz Stadium'  => 'mercedesbenz',
        'BC Place'               => 'bcplace',
        'BMO Field'              => 'bmo',
        'Estadio Azteca'         => 'azteca',
        'Estadio BBVA'           => 'bbva',
        'Estadio Universitario'  => 'universitario',
        'Estadio Cuauhtémoc'     => 'cuauhtemoc',
    ];

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key', '');
    }

    // ──────────────────────────────────────────────────────────
    //  Météo par clé de stade
    // ──────────────────────────────────────────────────────────
    public function getByStadiumKey(string $key): array
    {
        $stadium = self::STADIUMS[strtolower($key)] ?? null;

        if (! $stadium) {
            return ['error' => "Stade '$key' introuvable"];
        }

        return $this->fetchWeather($stadium);
    }

    // ──────────────────────────────────────────────────────────
    //  Météo par nom de stade
    // ──────────────────────────────────────────────────────────
    public function getByStadiumName(string $name): array
    {
        $key     = self::NAME_MAP[$name] ?? null;
        $stadium = $key ? self::STADIUMS[$key] : null;

        if (! $stadium) {
            // Fallback : chercher par lat/lon si stade inconnu
            return ['error' => "Stade '$name' non trouvé dans la liste"];
        }

        return $this->fetchWeather($stadium);
    }

    // ──────────────────────────────────────────────────────────
    //  Liste de tous les stades
    // ──────────────────────────────────────────────────────────
    public function getAllStadiums(): array
    {
        return array_map(fn($k, $s) => array_merge($s, ['key' => $k]), array_keys(self::STADIUMS), self::STADIUMS);
    }

    // ──────────────────────────────────────────────────────────
    //  Appel API OpenWeatherMap
    // ──────────────────────────────────────────────────────────
    private function fetchWeather(array $stadium): array
    {
        if (empty($this->apiKey)) {
            return $this->mockWeather($stadium);
        }

        $ttl      = (int) config('cache.ttl.weather', 1800); // 30 min
        $cacheKey = 'weather_' . $stadium['city'] . '_' . round($stadium['lat'], 1) . '_' . round($stadium['lon'], 1);

        return Cache::remember($cacheKey, $ttl, function () use ($stadium) {
            $response = Http::get(self::BASE_URL, [
                'lat'   => $stadium['lat'],
                'lon'   => $stadium['lon'],
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang'  => 'fr',
            ]);

            if ($response->failed()) {
                Log::warning("OpenWeather error for {$stadium['city']}");
                return $this->mockWeather($stadium);
            }

            $data = $response->json();

            return [
                'stadium'     => $stadium['name'],
                'city'        => $stadium['city'],
                'timezone'    => $stadium['tz'],
                'temp'        => round($data['main']['temp'] ?? 0),
                'feels_like'  => round($data['main']['feels_like'] ?? 0),
                'humidity'    => $data['main']['humidity'] ?? 0,
                'wind_speed'  => round(($data['wind']['speed'] ?? 0) * 3.6, 1), // m/s → km/h
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon'        => $this->mapIcon($data['weather'][0]['icon'] ?? '01d'),
                'icon_url'    => "https://openweathermap.org/img/wn/{$data['weather'][0]['icon']}@2x.png",
                'visibility'  => ($data['visibility'] ?? 10000) / 1000 . ' km',
                'updated_at'  => now()->toDateTimeString(),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────
    //  Météo fictive si pas de clé OWM
    // ──────────────────────────────────────────────────────────
    private function mockWeather(array $stadium): array
    {
        return [
            'stadium'     => $stadium['name'],
            'city'        => $stadium['city'],
            'timezone'    => $stadium['tz'],
            'temp'        => rand(18, 32),
            'feels_like'  => rand(17, 30),
            'humidity'    => rand(40, 80),
            'wind_speed'  => round(rand(5, 30) / 10 * 3.6, 1),
            'description' => 'Données météo non disponibles',
            'icon'        => '🌤️',
            'icon_url'    => null,
            'visibility'  => '10 km',
            'mock'        => true,
            'updated_at'  => now()->toDateTimeString(),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  OWM icon code → emoji
    // ──────────────────────────────────────────────────────────
    private function mapIcon(string $code): string
    {
        return match(true) {
            str_starts_with($code, '01') => '☀️',
            str_starts_with($code, '02') => '🌤️',
            str_starts_with($code, '03') => '⛅',
            str_starts_with($code, '04') => '☁️',
            str_starts_with($code, '09') => '🌧️',
            str_starts_with($code, '10') => '🌦️',
            str_starts_with($code, '11') => '⛈️',
            str_starts_with($code, '13') => '❄️',
            str_starts_with($code, '50') => '🌫️',
            default                       => '🌡️',
        };
    }
}
