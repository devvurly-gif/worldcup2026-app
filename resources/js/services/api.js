// ============================================================
//  API Service — Proxy vers Laravel backend sécurisé
//  Clés API gérées côté serveur uniquement
// ============================================================

const BASE = '/api/wc26'

async function call(endpoint, params = {}) {
  const url = new URL(endpoint, window.location.origin + BASE + '/')
  // Rebuild avec base correcte
  const fullUrl = `${BASE}${endpoint}`
  const qs = new URLSearchParams(params).toString()
  const res = await fetch(qs ? `${fullUrl}?${qs}` : fullUrl, {
    headers: { Accept: 'application/json' },
  })
  if (!res.ok) throw new Error(`HTTP ${res.status}`)
  return res.json()
}

export const api = {
  fixtures    : ()   => call('/fixtures'),
  live        : ()   => call('/fixtures/live'),
  fixture     : (id) => call(`/fixtures/${id}`),
  standings   : ()   => call('/standings'),
  teamPlayers : (code) => call(`/teams/${code}/players`),
  weather     : (key)  => call(`/weather/${key}`),
}

// Mapping noms API → codes internes
export const NAME_TO_CODE = {
  'Mexico':'MEX','South Africa':'RSA','Korea Republic':'KOR','South Korea':'KOR',
  'Czech Republic':'CZE','Czechia':'CZE',
  'Canada':'CAN','Bosnia':'BIH','Bosnia and Herzegovina':'BIH',
  'Qatar':'QAT','Switzerland':'SUI',
  'Brazil':'BRA','Morocco':'MAR','Haiti':'HAI','Scotland':'SCO',
  'United States':'USA','Paraguay':'PAR','Australia':'AUS',
  'Turkey':'TUR','Türkiye':'TUR',
  'Germany':'GER','Curaçao':'CUW','Curacao':'CUW',
  'Ivory Coast':'CIV',"Côte d'Ivoire":'CIV','Ecuador':'ECU',
  'Netherlands':'NED','Japan':'JPN','Sweden':'SWE','Tunisia':'TUN',
  'Belgium':'BEL','Egypt':'EGY','Iran':'IRN','New Zealand':'NZL',
  'Spain':'ESP','Cape Verde':'CPV','Saudi Arabia':'KSA','Uruguay':'URU',
  'France':'FRA','Senegal':'SEN','Iraq':'IRQ','Norway':'NOR',
  'Argentina':'ARG','Algeria':'ALG','Austria':'AUT','Jordan':'JOR',
  'Portugal':'POR','DR Congo':'COD','Congo DR':'COD',
  'Uzbekistan':'UZB','Colombia':'COL',
  'England':'ENG','Croatia':'CRO','Ghana':'GHA','Panama':'PAN',
}

// 48 équipes statiques (fallback + enrichissement)
export const TEAMS = [
  // A
  { code:'MEX', name:'Mexique',        flag:'🇲🇽', group:'A', confed:'CONCACAF', color:'#006847', ranking:15 },
  { code:'RSA', name:'Afrique du Sud', flag:'🇿🇦', group:'A', confed:'CAF',      color:'#007A4D', ranking:57 },
  { code:'KOR', name:'Corée du Sud',   flag:'🇰🇷', group:'A', confed:'AFC',      color:'#C60C30', ranking:23 },
  { code:'CZE', name:'Tchéquie',       flag:'🇨🇿', group:'A', confed:'UEFA',     color:'#D7141A', ranking:40 },
  // B
  { code:'CAN', name:'Canada',         flag:'🇨🇦', group:'B', confed:'CONCACAF', color:'#FF0000', ranking:48 },
  { code:'BIH', name:'Bosnie-Herz.',   flag:'🇧🇦', group:'B', confed:'UEFA',     color:'#002395', ranking:62 },
  { code:'QAT', name:'Qatar',          flag:'🇶🇦', group:'B', confed:'AFC',      color:'#8D1B3D', ranking:37 },
  { code:'SUI', name:'Suisse',         flag:'🇨🇭', group:'B', confed:'UEFA',     color:'#FF0000', ranking:19 },
  // C
  { code:'BRA', name:'Brésil',         flag:'🇧🇷', group:'C', confed:'CONMEBOL', color:'#009C3B', ranking:4  },
  { code:'MAR', name:'Maroc',          flag:'🇲🇦', group:'C', confed:'CAF',      color:'#C1272D', ranking:14 },
  { code:'HAI', name:'Haïti',          flag:'🇭🇹', group:'C', confed:'CONCACAF', color:'#00209F', ranking:95 },
  { code:'SCO', name:'Écosse',         flag:'🏴󠁧󠁢󠁳󠁣󠁴󠁿', group:'C', confed:'UEFA',     color:'#003F87', ranking:36 },
  // D
  { code:'USA', name:'États-Unis',     flag:'🇺🇸', group:'D', confed:'CONCACAF', color:'#002868', ranking:13 },
  { code:'PAR', name:'Paraguay',       flag:'🇵🇾', group:'D', confed:'CONMEBOL', color:'#D52B1E', ranking:52 },
  { code:'AUS', name:'Australie',      flag:'🇦🇺', group:'D', confed:'AFC',      color:'#00843D', ranking:23 },
  { code:'TUR', name:'Turquie',        flag:'🇹🇷', group:'D', confed:'UEFA',     color:'#E30A17', ranking:28 },
  // E
  { code:'GER', name:'Allemagne',      flag:'🇩🇪', group:'E', confed:'UEFA',     color:'#000000', ranking:12 },
  { code:'CUW', name:'Curaçao',        flag:'🇨🇼', group:'E', confed:'CONCACAF', color:'#003DA5', ranking:85 },
  { code:'CIV', name:"Côte d'Ivoire",  flag:'🇨🇮', group:'E', confed:'CAF',      color:'#F77F00', ranking:45 },
  { code:'ECU', name:'Équateur',       flag:'🇪🇨', group:'E', confed:'CONMEBOL', color:'#FFD100', ranking:38 },
  // F
  { code:'NED', name:'Pays-Bas',       flag:'🇳🇱', group:'F', confed:'UEFA',     color:'#FF4F00', ranking:7  },
  { code:'JPN', name:'Japon',          flag:'🇯🇵', group:'F', confed:'AFC',      color:'#BC002D', ranking:17 },
  { code:'SWE', name:'Suède',          flag:'🇸🇪', group:'F', confed:'UEFA',     color:'#006AA7', ranking:25 },
  { code:'TUN', name:'Tunisie',        flag:'🇹🇳', group:'F', confed:'CAF',      color:'#E70013', ranking:27 },
  // G
  { code:'BEL', name:'Belgique',       flag:'🇧🇪', group:'G', confed:'UEFA',     color:'#EF3340', ranking:3  },
  { code:'EGY', name:'Égypte',         flag:'🇪🇬', group:'G', confed:'CAF',      color:'#CE1126', ranking:33 },
  { code:'IRN', name:'Iran',           flag:'🇮🇷', group:'G', confed:'AFC',      color:'#239F40', ranking:24 },
  { code:'NZL', name:'Nlle-Zélande',   flag:'🇳🇿', group:'G', confed:'OFC',      color:'#00247D', ranking:99 },
  // H
  { code:'ESP', name:'Espagne',        flag:'🇪🇸', group:'H', confed:'UEFA',     color:'#AA151B', ranking:5  },
  { code:'CPV', name:'Cap-Vert',       flag:'🇨🇻', group:'H', confed:'CAF',      color:'#003893', ranking:80 },
  { code:'KSA', name:'Arabie Saoudite',flag:'🇸🇦', group:'H', confed:'AFC',      color:'#006C35', ranking:69 },
  { code:'URU', name:'Uruguay',        flag:'🇺🇾', group:'H', confed:'CONMEBOL', color:'#75AADB', ranking:16 },
  // I
  { code:'FRA', name:'France',         flag:'🇫🇷', group:'I', confed:'UEFA',     color:'#003189', ranking:2  },
  { code:'SEN', name:'Sénégal',        flag:'🇸🇳', group:'I', confed:'CAF',      color:'#00853F', ranking:22 },
  { code:'IRQ', name:'Irak',           flag:'🇮🇶', group:'I', confed:'AFC',      color:'#007A3D', ranking:75 },
  { code:'NOR', name:'Norvège',        flag:'🇳🇴', group:'I', confed:'UEFA',     color:'#EF2B2D', ranking:20 },
  // J
  { code:'ARG', name:'Argentine',      flag:'🇦🇷', group:'J', confed:'CONMEBOL', color:'#74ACDF', ranking:1  },
  { code:'ALG', name:'Algérie',        flag:'🇩🇿', group:'J', confed:'CAF',      color:'#006233', ranking:31 },
  { code:'AUT', name:'Autriche',       flag:'🇦🇹', group:'J', confed:'UEFA',     color:'#ED2939', ranking:26 },
  { code:'JOR', name:'Jordanie',       flag:'🇯🇴', group:'J', confed:'AFC',      color:'#007A3D', ranking:90 },
  // K
  { code:'POR', name:'Portugal',       flag:'🇵🇹', group:'K', confed:'UEFA',     color:'#006600', ranking:6  },
  { code:'COD', name:'Congo RD',       flag:'🇨🇩', group:'K', confed:'CAF',      color:'#007FFF', ranking:55 },
  { code:'UZB', name:'Ouzbékistan',    flag:'🇺🇿', group:'K', confed:'AFC',      color:'#1EB53A', ranking:70 },
  { code:'COL', name:'Colombie',       flag:'🇨🇴', group:'K', confed:'CONMEBOL', color:'#FCD116', ranking:21 },
  // L
  { code:'ENG', name:'Angleterre',     flag:'🏴󠁧󠁢󠁥󠁮󠁧󠁿', group:'L', confed:'UEFA',     color:'#003090', ranking:5  },
  { code:'CRO', name:'Croatie',        flag:'🇭🇷', group:'L', confed:'UEFA',     color:'#FF0000', ranking:11 },
  { code:'GHA', name:'Ghana',          flag:'🇬🇭', group:'L', confed:'CAF',      color:'#006B3F', ranking:60 },
  { code:'PAN', name:'Panama',         flag:'🇵🇦', group:'L', confed:'CONCACAF', color:'#CE1126', ranking:44 },
]

export function getTeam(code) {
  return TEAMS.find(t => t.code === code)
}

function resolveCode(name) {
  if (!name) return '?'
  // Déjà un code 3 lettres valide (données statiques PHP) ?
  if (/^[A-Z]{2,3}$/.test(name) && TEAMS.some(t => t.code === name)) return name
  // Mapping nom complet → code
  return NAME_TO_CODE[name] ?? name.substring(0,3).toUpperCase()
}

export function mapFixture(f) {
  const hCode = resolveCode(f.home?.name)
  const aCode = resolveCode(f.away?.name)
  return {
    id        : f.id,
    date      : f.date,
    time      : f.time,
    stadium   : f.stadium,
    city      : f.city,
    phase     : f.phase,
    group     : f.group,
    status    : f.status,
    elapsed   : f.elapsed,
    isLive    : f.is_live,
    homeCode  : hCode,
    awayCode  : aCode,
    homeName  : f.home?.name,
    awayName  : f.away?.name,
    homeLogo  : f.home?.logo,
    awayLogo  : f.away?.logo,
    homeWinner: f.home?.winner,
    awayWinner: f.away?.winner,
    scoreHome   : f.score?.home,
    scoreAway   : f.score?.away,
    isThirdPlace: f.round === '3rd Place Final',
  }
}
