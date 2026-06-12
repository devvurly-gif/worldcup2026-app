<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base  = 'https://mondial26score.com';
        $today = now()->toDateString();

        $static = [
            ['url' => $base . '/#/',           'priority' => '1.0', 'freq' => 'daily'],
            ['url' => $base . '/#/groupes',    'priority' => '0.9', 'freq' => 'daily'],
            ['url' => $base . '/#/calendrier', 'priority' => '0.9', 'freq' => 'daily'],
            ['url' => $base . '/#/joueurs',    'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => $base . '/#/actualites', 'priority' => '0.9', 'freq' => 'hourly'],
            ['url' => $base . '/#/maroc',      'priority' => '0.8', 'freq' => 'daily'],
            ['url' => $base . '/#/bracket',    'priority' => '0.7', 'freq' => 'daily'],
        ];

        $teams = [
            'MEX','RSA','KOR','CZE','CAN','BIH','QAT','SUI','BRA','MAR',
            'HAI','SCO','USA','PAR','AUS','TUR','GER','CUW','CIV','ECU',
            'NED','JPN','SWE','TUN','BEL','EGY','IRN','NZL','ESP','CPV',
            'KSA','URU','FRA','SEN','IRQ','NOR','ARG','ALG','AUT','JOR',
            'POR','COD','UZB','COL','ENG','CRO','GHA','PAN',
        ];

        $news = NewsArticle::select('slug', 'updated_at')
            ->orderByDesc('published_at')->take(200)->get();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($static as $p) {
            $xml .= $this->entry($p['url'], $today, $p['freq'], $p['priority']);
        }
        foreach ($teams as $code) {
            $xml .= $this->entry("{$base}/#/equipe/{$code}", $today, 'weekly', '0.6');
        }
        foreach ($news as $a) {
            $xml .= $this->entry("{$base}/#/actualites/{$a->slug}", $a->updated_at->toDateString(), 'monthly', '0.5');
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    private function entry(string $loc, string $date, string $freq, string $priority): string
    {
        return "  <url><loc>{$loc}</loc><lastmod>{$date}</lastmod>"
            . "<changefreq>{$freq}</changefreq><priority>{$priority}</priority></url>\n";
    }
}
