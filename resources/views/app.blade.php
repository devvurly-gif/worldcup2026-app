<!DOCTYPE html>
<html lang="fr" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ── Primary SEO ───────────────────────────── -->
    <title>Mondial 2026 – Scores, Groupes, Joueurs | mondial26score.com</title>
    <meta name="description" content="Suivez la Coupe du Monde FIFA 2026 en direct : scores, classements des groupes, calendrier des matchs, joueurs et actualités. USA, Canada, Mexique – 48 équipes, 104 matchs.">
    <meta name="keywords" content="coupe du monde 2026, world cup 2026, FIFA 2026, scores mondial 2026, calendrier coupe du monde, groupes coupe du monde, joueurs FIFA 2026, USA Canada Mexique 2026, mondial scores live">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="mondial26score.com">
    <link rel="canonical" href="https://mondial26score.com/">

    <!-- ── Open Graph (Facebook, WhatsApp, LinkedIn) ── -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Mondial 2026 Scores">
    <meta property="og:title" content="Mondial 2026 – Scores en Direct, Groupes & Actualités">
    <meta property="og:description" content="Suivez la Coupe du Monde FIFA 2026 : scores live, classements des groupes, calendrier, joueurs et toutes les actus. 48 équipes dans 3 pays hôtes.">
    <meta property="og:url" content="https://mondial26score.com/">
    <meta property="og:image" content="https://mondial26score.com/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Coupe du Monde FIFA 2026 – mondial26score.com">
    <meta property="og:locale" content="fr_FR">

    <!-- ── Twitter / X Card ──────────────────────── -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@mondial26score">
    <meta name="twitter:title" content="Mondial 2026 – Scores en Direct & Actualités">
    <meta name="twitter:description" content="Scores live, groupes, calendrier et joueurs de la Coupe du Monde FIFA 2026.">
    <meta name="twitter:image" content="https://mondial26score.com/og-image.jpg">

    <!-- ── Geo & Language ────────────────────────── -->
    <meta name="geo.region" content="MA">
    <meta name="geo.placename" content="Maroc">
    <link rel="alternate" hreflang="fr" href="https://mondial26score.com/">
    <link rel="alternate" hreflang="x-default" href="https://mondial26score.com/">

    <!-- ── Favicon ───────────────────────────────── -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏆</text></svg>">
    <link rel="apple-touch-icon" href="https://mondial26score.com/og-image.jpg">

    <!-- ── JSON-LD Structured Data ───────────────── -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "https://mondial26score.com/#website",
          "url": "https://mondial26score.com/",
          "name": "Mondial 2026 Scores",
          "description": "Scores, groupes et actualités de la Coupe du Monde FIFA 2026",
          "inLanguage": "fr",
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "https://mondial26score.com/#/joueurs?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "SportsEvent",
          "@id": "https://mondial26score.com/#worldcup2026",
          "name": "Coupe du Monde FIFA 2026",
          "alternateName": ["FIFA World Cup 2026", "Mundial 2026", "World Cup 2026"],
          "description": "La 23ème édition de la Coupe du Monde FIFA réunissant 48 équipes dans 3 pays hôtes : États-Unis, Canada et Mexique.",
          "startDate": "2026-06-11",
          "endDate": "2026-07-19",
          "sport": "Football",
          "url": "https://mondial26score.com/",
          "image": "https://mondial26score.com/og-image.jpg",
          "organizer": {
            "@type": "Organization",
            "name": "FIFA",
            "url": "https://www.fifa.com"
          },
          "location": [
            { "@type": "Country", "name": "États-Unis" },
            { "@type": "Country", "name": "Canada" },
            { "@type": "Country", "name": "Mexique" }
          ],
          "competitor": { "@type": "SportsTeam", "name": "48 équipes nationales" }
        },
        {
          "@type": "Organization",
          "@id": "https://mondial26score.com/#organization",
          "name": "Mondial 2026 Scores",
          "url": "https://mondial26score.com/",
          "logo": {
            "@type": "ImageObject",
            "url": "https://mondial26score.com/og-image.jpg"
          }
        }
      ]
    }
    </script>

    <!-- ── Performance ───────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://flagcdn.com">
    <link rel="dns-prefetch" href="https://pagead2.googlesyndication.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/main.js'])
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6881478405262528" crossorigin="anonymous"></script>
</head>
<body class="bg-[#050518] text-white min-h-screen">
    <div id="app"></div>
</body>
</html>
