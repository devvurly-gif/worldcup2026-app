<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\SquadImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SquadController extends Controller
{
    private const STORAGE_FILE = 'wc2026_squads.json';

    // ──────────────────────────────────────────────────────────
    //  GET /api/wc26/squads — liste toutes les équipes importées
    // ──────────────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $squads = $this->load();

        return response()->json([
            'teams'      => array_keys($squads),
            'total_players' => array_sum(array_map('count', $squads)),
            'data'       => $squads,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  GET /api/wc26/squads/{code} — joueurs d'une équipe
    // ──────────────────────────────────────────────────────────
    public function team(string $code): JsonResponse
    {
        $squads = $this->load();
        $code   = strtoupper($code);

        if (!isset($squads[$code])) {
            return response()->json([
                'team'  => $code,
                'squad' => [],
                'note'  => 'Aucun effectif importé pour cette équipe.',
            ]);
        }

        return response()->json([
            'team'  => $code,
            'count' => count($squads[$code]),
            'squad' => $squads[$code],
        ]);
    }

    // ──────────────────────────────────────────────────────────
    //  POST /api/wc26/squads/import — upload du fichier XLS
    // ──────────────────────────────────────────────────────────
    public function import(Request $request): JsonResponse
    {
        // Vérification mot de passe admin simple
        $adminPass = config('app.admin_password', 'wc2026admin');
        if ($request->header('X-Admin-Password') !== $adminPass) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new SquadImport();
            Excel::import($import, $request->file('file'));
            $newSquads = $import->getSquads();

            if (empty($newSquads)) {
                return response()->json([
                    'error' => 'Aucun joueur trouvé. Vérifiez les colonnes : team, name, position, number, age, club',
                ], 422);
            }

            // Fusionner avec les données existantes
            $existing = $this->load();
            $merged   = array_merge($existing, $newSquads);
            $this->save($merged);

            $summary = [];
            foreach ($newSquads as $team => $players) {
                $summary[] = ['team' => $team, 'players' => count($players)];
            }

            return response()->json([
                'success'       => true,
                'imported'      => count($newSquads),
                'total_players' => array_sum(array_map('count', $newSquads)),
                'teams'         => $summary,
                'message'       => count($newSquads) . ' équipe(s) importée(s) avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Erreur lors de la lecture du fichier.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    //  POST /api/wc26/squads/import-server — importe squads_wc2026.xlsx depuis public/
    // ──────────────────────────────────────────────────────────
    public function importFromServer(Request $request): JsonResponse
    {
        $adminPass = config('app.admin_password', 'wc2026admin');
        if ($request->header('X-Admin-Password') !== $adminPass) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $path = public_path('../worldcup2026-vue/public/squads_wc2026.xlsx');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Fichier squads_wc2026.xlsx introuvable sur le serveur.'], 404);
        }

        try {
            $import = new SquadImport();
            Excel::import($import, $path);
            $newSquads = $import->getSquads();

            if (empty($newSquads)) {
                return response()->json(['error' => 'Aucun joueur trouvé dans le fichier.'], 422);
            }

            $existing = $this->load();
            $merged   = array_merge($existing, $newSquads);
            $this->save($merged);

            $summary = [];
            foreach ($newSquads as $team => $players) {
                $summary[] = ['team' => $team, 'players' => count($players)];
            }

            return response()->json([
                'success'       => true,
                'imported'      => count($newSquads),
                'total_players' => array_sum(array_map('count', $newSquads)),
                'teams'         => $summary,
                'message'       => count($newSquads) . ' équipe(s) importées depuis le fichier serveur.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    //  DELETE /api/wc26/squads/{code} — supprimer une équipe
    // ──────────────────────────────────────────────────────────
    public function deleteTeam(Request $request, string $code): JsonResponse
    {
        $adminPass = config('app.admin_password', 'wc2026admin');
        if ($request->header('X-Admin-Password') !== $adminPass) {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        $squads = $this->load();
        $code   = strtoupper($code);

        if (!isset($squads[$code])) {
            return response()->json(['error' => "Équipe '$code' non trouvée"], 404);
        }

        unset($squads[$code]);
        $this->save($squads);

        return response()->json(['success' => true, 'deleted' => $code]);
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────
    private function load(): array
    {
        if (!Storage::exists(self::STORAGE_FILE)) return [];
        return json_decode(Storage::get(self::STORAGE_FILE), true) ?? [];
    }

    private function save(array $data): void
    {
        Storage::put(self::STORAGE_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
