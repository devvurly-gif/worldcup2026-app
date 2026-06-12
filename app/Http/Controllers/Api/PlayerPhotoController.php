<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlayerPhotoController extends Controller
{
    private function checkAdmin(Request $request): bool
    {
        $pw = config('app.admin_password', env('ADMIN_PASSWORD', 'wc2026admin'));
        return $request->header('X-Admin-Password') === $pw;
    }

    // ── Upload manuel ─────────────────────────────────────

    public function upload(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $request->validate([
            'file' => 'required|image|max:2048',
            'team' => 'required|string|max:100',
            'name' => 'required|string|max:100',
        ]);
        $slug     = Str::slug($request->team) . '_' . Str::slug($request->name);
        $ext      = $request->file('file')->getClientOriginalExtension() ?: 'jpg';
        $filename = $slug . '_' . time() . '.' . $ext;
        $path     = $request->file('file')->storeAs('players', $filename, 'public');
        return response()->json(['url' => Storage::url($path)]);
    }

    // ── Supprimer fichier ─────────────────────────────────

    public function delete(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $url  = $request->input('url', '');
        $path = preg_replace('#^/storage/#', '', $url);
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        return response()->json(['ok' => true]);
    }

    // ── Auto-téléchargement (multi-sources) ──────────────
    //   1. TheSportsDB  (libre, complet, photos HD)
    //   2. Wikipedia EN (fallback)

    public function fetchBatch(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        set_time_limit(120);

        $players      = $request->input('players', []);
        $skipExisting = $request->boolean('skip_existing', true);
        $results      = [];

        foreach ($players as $player) {
            $name = trim($player['name'] ?? '');
            $team = trim($player['team'] ?? '');
            $code = trim($player['code'] ?? '');
            if (!$name) continue;

            $slug     = Str::slug($code ?: $team) . '_' . Str::slug($name);
            $existing = $this->findExisting($slug);

            if ($skipExisting && $existing) {
                $results[] = ['name' => $name, 'team' => $team, 'url' => Storage::url($existing), 'status' => 'existing'];
                continue;
            }

            $url = $this->fetchPhotoMultiSource($name, $team, $slug);

            $results[] = [
                'name'   => $name,
                'team'   => $team,
                'url'    => $url,
                'status' => $url ? 'ok' : 'not_found',
            ];

            usleep(200000); // 200 ms entre chaque joueur
        }

        return response()->json(['results' => $results]);
    }

    // ── Helpers privés ────────────────────────────────────

    private function findExisting(string $slug): ?string
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $path = "players/{$slug}.{$ext}";
            if (Storage::disk('public')->exists($path)) return $path;
        }
        return null;
    }

    private function fetchPhotoMultiSource(string $name, string $team, string $slug): ?string
    {
        return $this->fetchFromTheSportsDB($name, $team, $slug);
    }

    /**
     * TheSportsDB — API publique gratuite, couvre 95%+ des joueurs pro
     * Doc : https://www.thesportsdb.com/api.php
     */
    private function fetchFromTheSportsDB(string $name, string $team, string $slug): ?string
    {
        try {
            // Clé "3" = tier gratuit (lecture seule, non-commercial)
            $res = Http::timeout(8)
                ->withHeaders(['User-Agent' => 'WC2026App/1.0'])
                ->get('https://www.thesportsdb.com/api/v1/json/3/searchplayers.php', [
                    'p' => $name,
                ]);

            if (!$res->successful()) return null;

            $players = $res->json('player') ?? [];
            if (empty($players)) return null;

            // Prendre le premier joueur avec une miniature
            $thumb = null;
            foreach ($players as $p) {
                $t = $p['strThumb'] ?? $p['strCutout'] ?? null;
                if ($t) { $thumb = $t; break; }
            }
            if (!$thumb) return null;

            return $this->downloadAndStore($thumb, $slug, 'tsdb');

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Télécharge l'image et la stocke dans storage/public/players/
     */
    private function downloadAndStore(string $imageUrl, string $slug, string $source): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'WC2026App/1.0'])
                ->get($imageUrl);

            if (!$response->successful()) return null;

            $ext  = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
            $ext  = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';
            if (strlen($ext) > 4) $ext = 'jpg';

            $path = "players/{$slug}.{$ext}";
            Storage::disk('public')->put($path, $response->body());

            return Storage::url($path);

        } catch (\Exception $e) {
            return null;
        }
    }
}
