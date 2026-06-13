<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\SquadController;
use App\Http\Controllers\Api\StandingController;
use App\Http\Controllers\Api\PlayerPhotoController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\NewsController;

// Auth (public)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Auth (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout',         [AuthController::class, 'logout']);
    Route::get('/auth/profile',         [AuthController::class, 'profile']);
    Route::put('/auth/profile',         [AuthController::class, 'updateProfile']);

    // Admin: user management
    Route::get('/admin/users',          [AuthController::class, 'users']);
    Route::put('/admin/users/{user}',   [AuthController::class, 'updateUser']);
});

// World Cup data (public)
Route::prefix('wc26')->group(function () {
    Route::get('/teams',           [TeamController::class, 'index']);
    Route::get('/teams/{code}',    [TeamController::class, 'show']);
    Route::get('/fixtures',        [FixtureController::class, 'index']);
    Route::get('/fixtures/live',   [FixtureController::class, 'live']);
    Route::get('/fixtures/{id}',   [FixtureController::class, 'show']);
    Route::get('/standings',       [StandingController::class, 'index']);
    Route::get('/squad/{code}',    [SquadController::class, 'show']);
    Route::get('/squads',          [SquadController::class, 'index']);
    Route::get('/weather',         [WeatherController::class, 'index']);

    // Players CRUD
    Route::get('/players',         [SquadController::class, 'index']);
    Route::post('/players',        [SquadController::class, 'store']);
    Route::put('/players/{id}',    [SquadController::class, 'update']);
    Route::delete('/players/{id}', [SquadController::class, 'destroy']);

    // Player photos
    Route::post('/players/photo',        [PlayerPhotoController::class, 'upload']);
    Route::delete('/players/photo',      [PlayerPhotoController::class, 'delete']);
    Route::post('/players/fetch-photos', [PlayerPhotoController::class, 'fetchBatch']);
});

// News (public)
Route::get('/news',        [NewsController::class, 'index']);
Route::get('/news/{slug}', [NewsController::class, 'show']);
