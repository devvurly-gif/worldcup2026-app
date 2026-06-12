<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Sitemap & robots.txt
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt', function () {
    return response(
        "User-agent: *\nAllow: /\n\nSitemap: https://mondial26score.com/sitemap.xml\n",
        200,
        ['Content-Type' => 'text/plain']
    );
});

// Vue SPA — catch all
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
