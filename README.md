# ⚽ World Cup 2026 — Application Web

Application SaaS publique de suivi de la FIFA World Cup 2026 (USA · Canada · Mexique).

**Stack:** Laravel 11 · Vue 3 · Tailwind CSS · Pinia · Vite

---

## 🚀 Fonctionnalités

| Module | Description |
|---|---|
| 🏠 Accueil | Hero match, matchs du jour, aperçu des groupes |
| 🗓️ Calendrier | Tous les 104 matchs avec scores live |
| 🏆 Groupes | 12 groupes, classements, mini-matchs |
| 👤 Joueurs | 1245 joueurs, photos, stats, filtres |
| 🏟️ Équipe | Page détail par équipe (staff, joueurs, matchs, stats) |
| 🇲🇦 Focus Maroc | Page dédiée Maroc (matchs, joueurs, groupe) |
| 🔮 Bracket | Phase éliminatoire interactive |
| 📰 Actualités | Carrousel news + page détail + Google Ads |
| 🔐 Auth | Multi-rôles : Admin / Editor / User via Laravel Sanctum |
| ⚙️ Admin | Gestion équipes, joueurs, scores, effectifs, utilisateurs |

---

## 🛠️ Installation

### Prérequis
- PHP 8.2+ · Composer · Node.js 18+ · MySQL · Laragon (Windows) ou serveur local

### 1. Cloner le projet

```bash
git clone https://github.com/VOTRE_USER/worldcup2026-app.git
cd worldcup2026-app
```

### 2. Backend Laravel

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Éditer `.env` :
```env
DB_DATABASE=worldcup2026
DB_USERNAME=root
DB_PASSWORD=

APIFOOTBALL_KEY=votre_cle
OPENWEATHER_KEY=votre_cle
ADMIN_PASSWORD=votre_mot_de_passe
```

```bash
php artisan migrate
php artisan storage:link
```

### 3. Frontend Vue

```bash
npm install
npm run dev      # développement (hot-reload)
npm run build    # production
```

### 4. Accès

| URL | Description |
|---|---|
| `http://localhost/worldcup2026-app/public/` | Application publique |
| `/#/login` | Connexion utilisateur |
| `/#/wc-admin` | Panel admin (mot de passe) |
| `/#/admin` | Panel admin (Sanctum) |

---

## 🔐 Sécurité — Clés API

> Toutes les clés API sont stockées **uniquement** dans `.env` côté serveur.  
> Le frontend Vue ne contient **aucune clé secrète**.  
> Les appels externes (API-Football, OpenWeather, Google News) passent par des **controllers Laravel**.

---

## 🗂️ Structure du projet

```
worldcup2026-app/
├── app/
│   ├── Http/Controllers/Api/     # Controllers API
│   │   ├── AuthController.php
│   │   ├── NewsController.php
│   │   ├── FixtureController.php
│   │   ├── PlayerPhotoController.php
│   │   └── ...
│   ├── Models/                   # User, Pronostic, Favorite
│   └── Services/                 # ApiFootballService, WeatherService...
├── database/migrations/
├── resources/
│   ├── css/app.css               # Tailwind CSS
│   ├── js/
│   │   ├── views/                # Pages Vue
│   │   ├── components/           # Composants réutilisables
│   │   ├── stores/               # Pinia (auth, app, admin)
│   │   ├── composables/          # usePlayers.js
│   │   ├── utils/flag.js         # flagImg() — flagcdn.com
│   │   └── router/index.js
│   └── views/app.blade.php       # SPA entry point
└── routes/
    ├── api.php
    └── web.php
```

---

## 📦 APIs utilisées

| API | Usage | Clé requise |
|---|---|---|
| [API-Football](https://api-football.com) | Matchs, scores, effectifs | ✅ Oui |
| [OpenWeatherMap](https://openweathermap.org) | Météo stades | ✅ Oui |
| [flagcdn.com](https://flagcdn.com) | Images drapeaux | ❌ Non |
| Google News RSS | Actualités | ❌ Non |
| TheSportsDB (key=3) | Photos joueurs | ❌ Non |

---

## 🧩 Google AdSense

Ajouter dans `.env` :
```env
VITE_ADSENSE_CLIENT=ca-pub-VOTRE_PUBLISHER_ID
```

Les emplacements publicitaires sont dans `GoogleAd.vue` — des placeholders s'affichent tant que l'ID n'est pas configuré.

---

## 📄 Licence

MIT — Projet personnel / éducatif. Non affilié à la FIFA.
