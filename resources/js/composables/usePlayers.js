/**
 * usePlayers — source de vérité unique pour les 1245 joueurs
 *  - Base : PLAYERS_DATA (noms FR + dates de naissance)
 *  - Enrichissement : clubs depuis /api/wc26/squads
 *  - CRUD local + persistance API
 */
import { ref, computed } from 'vue'
import { PLAYERS_DATA } from '@/data/players'

// Auto-generate player photo URL from team code + player name
function buildPhotoUrl(code, name) {
  if (!code || !name) return null
  const slug = name
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '') // remove accents
    .replace(/[^a-z0-9\s-]/g, '')                     // remove special chars
    .trim()
    .replace(/\s+/g, '-')                             // spaces → hyphens
  return `/storage/players/${code.toLowerCase()}_${slug}.jpg`
}

// ── Mapping FR ↔ code FIFA ────────────────────────────────
export const FR_TO_CODE = {
  'Mexique':'MEX','Afrique du Sud':'RSA','République de Corée':'KOR','Tchéquie':'CZE',
  'Canada':'CAN','Bosnie-Herzégovine':'BIH','Qatar':'QAT','Suisse':'SUI',
  'Brésil':'BRA','Maroc':'MAR','Haïti':'HAI','Écosse':'SCO',
  'États-Unis':'USA','Paraguay':'PAR','Australie':'AUS','Türkiye':'TUR',
  'Allemagne':'GER','Curaçao':'CUW',"Côte d'Ivoire":'CIV','Équateur':'ECU',
  'Pays-Bas':'NED','Japon':'JPN','Suède':'SWE','Tunisie':'TUN',
  'Belgique':'BEL','Égypte':'EGY','Iran':'IRN','Nouvelle-Zélande':'NZL',
  'Espagne':'ESP','Cabo Verde':'CPV','Arabie saoudite':'KSA','Uruguay':'URU',
  'France':'FRA','Sénégal':'SEN','Iraq':'IRQ','Norvège':'NOR',
  'Argentine':'ARG','Algérie':'ALG','Autriche':'AUT','Jordanie':'JOR',
  'Portugal':'POR','RD Congo':'COD','Ouzbékistan':'UZB','Colombie':'COL',
  'Angleterre':'ENG','Croatie':'CRO','Ghana':'GHA','Panama':'PAN',
}
export const CODE_TO_FR = Object.fromEntries(Object.entries(FR_TO_CODE).map(([fr, c]) => [c, fr]))

export const CODE_FLAGS = {
  MEX:'🇲🇽',RSA:'🇿🇦',KOR:'🇰🇷',CZE:'🇨🇿',CAN:'🇨🇦',BIH:'🇧🇦',QAT:'🇶🇦',SUI:'🇨🇭',
  BRA:'🇧🇷',MAR:'🇲🇦',HAI:'🇭🇹',SCO:'🏴󠁧󠁢󠁳󠁣󠁴󠁿',USA:'🇺🇸',PAR:'🇵🇾',AUS:'🇦🇺',TUR:'🇹🇷',
  GER:'🇩🇪',CUW:'🇨🇼',CIV:'🇨🇮',ECU:'🇪🇨',NED:'🇳🇱',JPN:'🇯🇵',SWE:'🇸🇪',TUN:'🇹🇳',
  BEL:'🇧🇪',EGY:'🇪🇬',IRN:'🇮🇷',NZL:'🇳🇿',ESP:'🇪🇸',CPV:'🇨🇻',KSA:'🇸🇦',URU:'🇺🇾',
  FRA:'🇫🇷',SEN:'🇸🇳',IRQ:'🇮🇶',NOR:'🇳🇴',ARG:'🇦🇷',ALG:'🇩🇿',AUT:'🇦🇹',JOR:'🇯🇴',
  POR:'🇵🇹',COD:'🇨🇩',UZB:'🇺🇿',COL:'🇨🇴',ENG:'🏴󠁧󠁢󠁥󠁮󠁧󠁿',CRO:'🇭🇷',GHA:'🇬🇭',PAN:'🇵🇦',
}

// ── Store partagé (singleton) ─────────────────────────────
const _overrides = ref({})   // { "team|name": { club, birth_date, … } }
const _extras    = ref([])   // joueurs ajoutés manuellement
const _apiClubs  = ref({})   // { "team|name": club }
let   _loaded    = false

export function usePlayers() {

  // Charge les clubs depuis l'API (une seule fois)
  async function loadApiClubs() {
    if (_loaded) return
    _loaded = true
    try {
      const d = await fetch('/api/wc26/squads').then(r => r.json())
      const map = {}
      for (const [code, squad] of Object.entries(d.data ?? {})) {
        const fr = CODE_TO_FR[code]
        if (!fr) continue
        for (const p of (squad ?? [])) {
          if (p.name) map[`${fr}|${p.name}`] = p.club ?? null
        }
      }
      _apiClubs.value = map
    } catch {}
  }

  // Liste complète dédupliquée
  const players = computed(() => {
    const seen = new Set()
    const base = [...PLAYERS_DATA, ..._extras.value]
      .filter(p => {
        const key = `${p.team}|${p.name}`
        if (seen.has(key)) return false
        seen.add(key)
        return true
      })
      .map(p => {
        const key = `${p.team}|${p.name}`
        const ov  = _overrides.value[key] ?? {}
        return {
          ...p,
          code:       FR_TO_CODE[p.team] ?? '',
          flag:       CODE_FLAGS[FR_TO_CODE[p.team] ?? ''] ?? '🏳️',
          club:       ov.club       ?? _apiClubs.value[key] ?? p.club       ?? null,
          birth_date: ov.birth_date ?? p.birth_date ?? null,
          number:     ov.number     ?? p.number     ?? null,
          photo:      ov.photo      ?? p.photo      ?? buildPhotoUrl(FR_TO_CODE[p.team], p.name),
        }
      })
    return base
  })

  // ── CRUD ────────────────────────────────────────────────
  function addPlayer(data) {
    const key = `${data.team}|${data.name}`
    if (players.value.find(p => `${p.team}|${p.name}` === key)) {
      // Exists — update override
      updatePlayer(data)
      return
    }
    _extras.value = [..._extras.value, { ...data }]
    _persist()
  }

  function updatePlayer(data) {
    const key = `${data.team}|${data.name}`
    _overrides.value = { ..._overrides.value, [key]: { ...data } }
    _persist()
  }

  function deletePlayer(team, name) {
    const key = `${team}|${name}`
    // Remove from extras if present
    _extras.value = _extras.value.filter(p => `${p.team}|${p.name}` !== key)
    // Mark as deleted in overrides
    const { [key]: _, ...rest } = _overrides.value
    _overrides.value = rest
    _persist()
  }

  // Persist overrides/extras to localStorage
  function _persist() {
    try {
      localStorage.setItem('wc26_player_overrides', JSON.stringify(_overrides.value))
      localStorage.setItem('wc26_player_extras',    JSON.stringify(_extras.value))
    } catch {}
  }

  // Load from localStorage
  function _restore() {
    try {
      const ov = localStorage.getItem('wc26_player_overrides')
      const ex = localStorage.getItem('wc26_player_extras')
      if (ov) _overrides.value = JSON.parse(ov)
      if (ex) _extras.value    = JSON.parse(ex)
    } catch {}
  }

  _restore()

  return { players, loadApiClubs, addPlayer, updatePlayer, deletePlayer }
}

// ── Helpers exportés ─────────────────────────────────────
export function calcAge(dob) {
  if (!dob) return null
  const [d, m, y] = dob.split('/').map(Number)
  const today = new Date()
  let age = today.getFullYear() - y
  const passed = today.getMonth() + 1 > m || (today.getMonth() + 1 === m && today.getDate() >= d)
  return passed ? age : age - 1
}
export function posShort(pos) {
  return { Gardien:'GK', Défenseur:'DF', Milieu:'MF', Attaquant:'FW' }[pos] ?? pos
}
export function posBg(pos) {
  return {
    Gardien:'bg-yellow-500', Défenseur:'bg-blue-500',
    Milieu:'bg-green-500',   Attaquant:'bg-red-500',
  }[pos] ?? 'bg-gray-500'
}
export function posBgLight(pos) {
  return {
    Gardien:'bg-yellow-500/10', Défenseur:'bg-blue-500/10',
    Milieu:'bg-green-500/10',   Attaquant:'bg-red-500/10',
  }[pos] ?? 'bg-white/5'
}
export const POSITIONS = ['Gardien','Défenseur','Milieu','Attaquant']
export const TEAM_OPTIONS = [...new Set(PLAYERS_DATA.map(p => p.team))].map(fr => ({
  fr, code: FR_TO_CODE[fr] ?? '', flag: CODE_FLAGS[FR_TO_CODE[fr] ?? ''] ?? '🏳️',
}))
