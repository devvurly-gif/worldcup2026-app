<template>
  <div v-if="team" class="space-y-6">

    <!-- ── HERO ─────────────────────────────────────────────── -->
    <div class="relative rounded-3xl overflow-hidden shadow-2xl"
         :style="`background: linear-gradient(135deg, ${team.color}33 0%, #0d0d2b 60%)`">
      <!-- Décor -->
      <div class="absolute inset-0 opacity-5"
           :style="`background: radial-gradient(circle at 80% 50%, ${team.color} 0%, transparent 60%)`"></div>
      <img :src="flagImg(team.code)" :alt="team.name"
           class="absolute right-4 top-0 h-48 object-contain select-none pointer-events-none opacity-10" />

      <div class="relative px-8 py-8">
        <div class="flex items-center gap-6">
          <!-- Drapeau image -->
          <div class="w-28 h-20 rounded-2xl overflow-hidden shadow-xl shrink-0 flex items-center justify-center"
               :style="`background: ${team.color}25; border: 2px solid ${team.color}40`">
            <img :src="flagImg(team.code)" :alt="team.name"
                 class="w-full h-full object-cover" />
          </div>

          <!-- Infos -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-widest"
                    :style="`background:${team.color}30; color:${team.color}; border:1px solid ${team.color}40`">
                {{ team.confed }}
              </span>
              <span class="text-xs text-gray-500 font-mono">FIFA #{{ team.ranking }}</span>
            </div>
            <h1 class="text-4xl font-black text-white leading-tight truncate">{{ team.name }}</h1>
            <p class="text-gray-400 mt-1 text-sm">
              <i class="fas fa-layer-group mr-1 text-yellow-400"></i>
              Groupe {{ team.group }} · CdM 2026
            </p>

            <!-- Mini stats inline -->
            <div v-if="standing" class="flex gap-4 mt-3">
              <div v-for="(v, label) in miniStats" :key="label" class="text-center">
                <p class="text-xl font-black" :class="v.cls ?? 'text-white'">{{ v.val }}</p>
                <p class="text-[10px] text-gray-600 uppercase tracking-widest">{{ label }}</p>
              </div>
            </div>
            <div v-else class="mt-3 text-xs text-gray-600 flex items-center gap-1.5">
              <i class="fas fa-clock"></i> Phase de groupes pas encore commencée
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex border-t border-white/10 overflow-x-auto">
        <button v-for="tab in tabs" :key="tab.id"
                @click="activeTab = tab.id"
                class="flex-1 min-w-max flex items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold
                       transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === tab.id
                  ? 'border-yellow-400 text-yellow-400 bg-yellow-400/5'
                  : 'border-transparent text-gray-500 hover:text-gray-300 hover:bg-white/3'">
          <i :class="'fas ' + tab.icon + ' text-xs'"></i>
          {{ tab.label }}
          <span v-if="tab.count" class="text-[10px] px-1.5 py-0.5 rounded-full bg-white/10">{{ tab.count }}</span>
        </button>
      </div>
    </div>

    <!-- ── TAB: JOUEURS ──────────────────────────────────────── -->
    <div v-show="activeTab === 'joueurs'" class="space-y-4">

      <!-- Staff -->
      <div v-if="coach" class="bg-white/5 border border-white/10 rounded-2xl p-5">
        <p class="label-section mb-3"><i class="fas fa-whistle mr-2 text-yellow-400"></i>Staff</p>
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl shrink-0"
               :style="`background:${team.color}20`">
            🧑‍💼
          </div>
          <div>
            <p class="text-white font-bold">{{ coach }}</p>
            <p class="text-gray-500 text-sm">Sélectionneur · {{ team.name }}</p>
          </div>
        </div>
      </div>

      <!-- Joueurs par poste -->
      <div v-for="pos in POSITIONS" :key="pos"
           class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-3 border-b border-white/10"
             :class="posBgLight(pos)">
          <span class="text-xs font-black px-2.5 py-1 rounded-full uppercase text-white"
                :class="posBg(pos)">{{ posShort(pos) }}</span>
          <span class="text-white font-semibold text-sm">{{ pos }}s</span>
          <span class="ml-auto text-gray-600 text-xs">{{ playersByPos(pos).length }}</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-px bg-white/5">
          <div v-for="p in playersByPos(pos)" :key="p.name"
               class="bg-[#07071a] p-3 flex items-center gap-3 hover:bg-white/5 transition-colors cursor-pointer"
               @click="selectedPlayer = p">
            <!-- Avatar -->
            <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 flex items-center justify-center"
                 :class="p.photo ? '' : posBgLight(pos)">
              <img v-if="p.photo" :src="p.photo" class="w-full h-full object-cover object-top" />
              <img v-else :src="flagImg(team.code)" :alt="team.name"
                   class="w-8 h-8 object-cover rounded-lg opacity-60" />
            </div>
            <div class="min-w-0">
              <p class="text-white text-xs font-semibold truncate">{{ p.name }}</p>
              <p class="text-gray-600 text-[10px] truncate">
                <span v-if="p.number" class="text-yellow-500 mr-1">#{{ p.number }}</span>
                {{ p.club ?? '—' }}
              </p>
              <p class="text-gray-700 text-[10px]">{{ p.birth_date ? calcAge(p.birth_date) + ' ans' : '' }}</p>
            </div>
          </div>
        </div>

        <div v-if="!playersByPos(pos).length"
             class="py-6 text-center text-gray-700 text-xs">Aucun joueur enregistré</div>
      </div>
    </div>

    <!-- ── TAB: MATCHS ───────────────────────────────────────── -->
    <div v-show="activeTab === 'matchs'" class="space-y-3">
      <div v-if="!teamFixtures.length" class="card py-16 text-center text-gray-600">
        <i class="fas fa-calendar-xmark text-3xl block mb-3"></i>
        Aucun match programmé
      </div>

      <div v-for="m in teamFixtures" :key="m.id"
           class="card flex items-center gap-4 px-5 py-4 hover:bg-white/8 transition-colors cursor-pointer"
           @click="$router.push(`/match/${m.id}`)">
        <!-- Date -->
        <div class="text-center w-14 shrink-0">
          <p class="text-yellow-400 font-black text-lg leading-none">{{ fmtDay(m.date) }}</p>
          <p class="text-gray-600 text-[10px] uppercase">{{ fmtMonth(m.date) }}</p>
        </div>

        <!-- Teams -->
        <div class="flex-1 flex items-center gap-3 min-w-0">
          <img :src="flagImg(m.homeCode)" :alt="m.homeCode" class="w-10 h-7 object-cover rounded shrink-0" />
          <div class="flex-1 text-center">
            <p class="text-gray-400 text-xs mb-1">
              {{ getTeam(m.homeCode)?.name ?? m.homeCode }}
              <span class="text-gray-600 mx-1">vs</span>
              {{ getTeam(m.awayCode)?.name ?? m.awayCode }}
            </p>
            <div class="flex items-center justify-center gap-3">
              <span class="text-2xl font-black"
                    :class="m.scoreHome !== null ? scoreColor(m, 'home') : 'text-gray-700'">
                {{ m.scoreHome ?? '—' }}
              </span>
              <span class="text-gray-600">:</span>
              <span class="text-2xl font-black"
                    :class="m.scoreAway !== null ? scoreColor(m, 'away') : 'text-gray-700'">
                {{ m.scoreAway ?? '—' }}
              </span>
            </div>
          </div>
          <img :src="flagImg(m.awayCode)" :alt="m.awayCode" class="w-10 h-7 object-cover rounded shrink-0" />
        </div>

        <!-- Status -->
        <div class="shrink-0 text-right">
          <span v-if="m.status === 'live'"
                class="badge-live text-[10px] px-2 py-0.5 animate-pulse">LIVE</span>
          <span v-else-if="m.scoreHome !== null"
                class="text-[10px] text-gray-600">Terminé</span>
          <span v-else class="text-[10px] text-gray-500">{{ m.time }}</span>
          <p class="text-[10px] text-gray-700 mt-0.5">Grp {{ team.group }}</p>
        </div>
      </div>
    </div>

    <!-- ── TAB: STATS ────────────────────────────────────────── -->
    <div v-show="activeTab === 'stats'" class="space-y-4">

      <!-- Classement groupe -->
      <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-white/10 flex items-center gap-2">
          <i class="fas fa-table-list text-yellow-400 text-sm"></i>
          <span class="text-white font-bold text-sm">Classement — Groupe {{ team.group }}</span>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-[10px] text-gray-600 uppercase tracking-widest border-b border-white/5">
              <th class="text-left px-5 py-2">Équipe</th>
              <th class="text-center px-2 py-2">J</th>
              <th class="text-center px-2 py-2 text-green-500">G</th>
              <th class="text-center px-2 py-2 text-yellow-500">N</th>
              <th class="text-center px-2 py-2 text-red-500">P</th>
              <th class="text-center px-2 py-2">Bf</th>
              <th class="text-center px-2 py-2">Bc</th>
              <th class="text-center px-2 py-2 text-white font-bold">Pts</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(code, idx) in groupTeams" :key="code"
                class="border-b border-white/5 transition-colors cursor-pointer"
                :class="code === team.code ? 'bg-yellow-400/8 hover:bg-yellow-400/12' : 'hover:bg-white/3'"
                @click="$router.push(`/equipe/${code}`)">
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <span class="text-gray-600 text-xs w-4">{{ idx+1 }}</span>
                  <img :src="flagImg(code)" :alt="code"
                       class="w-6 h-4 object-cover rounded" />
                  <span class="text-sm font-semibold" :class="code === team.code ? 'text-yellow-400' : 'text-white'">
                    {{ getTeam(code)?.name ?? code }}
                  </span>
                </div>
              </td>
              <td class="text-center px-2 py-3 text-gray-400 text-xs">{{ getStandingVal(code,'played') }}</td>
              <td class="text-center px-2 py-3 text-green-400 text-xs font-bold">{{ getStandingVal(code,'win') }}</td>
              <td class="text-center px-2 py-3 text-yellow-400 text-xs">{{ getStandingVal(code,'draw') }}</td>
              <td class="text-center px-2 py-3 text-red-400 text-xs">{{ getStandingVal(code,'lose') }}</td>
              <td class="text-center px-2 py-3 text-gray-400 text-xs">{{ getStandingVal(code,'goalsFor') }}</td>
              <td class="text-center px-2 py-3 text-gray-400 text-xs">{{ getStandingVal(code,'goalsAgainst') }}</td>
              <td class="text-center px-2 py-3 font-black text-base"
                  :class="code === team.code ? 'text-yellow-400' : 'text-white'">
                {{ getStandingVal(code,'points') }}
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!groupTeams.length" class="py-8 text-center text-gray-600 text-sm">Aucune donnée disponible</div>
      </div>

      <!-- Stats détaillées de l'équipe -->
      <div v-if="standing" class="grid grid-cols-2 gap-3">
        <div v-for="(s, i) in detailStats" :key="i"
             class="card p-4 text-center">
          <i :class="'fas ' + s.icon + ' text-2xl mb-2'" :style="`color:${team.color}`"></i>
          <p class="text-3xl font-black text-white">{{ s.val }}</p>
          <p class="text-gray-600 text-xs uppercase tracking-wider mt-1">{{ s.label }}</p>
        </div>
      </div>
      <div v-else class="card py-10 text-center text-gray-600">
        <i class="fas fa-chart-bar text-3xl block mb-3 opacity-30"></i>
        Les statistiques seront disponibles après le début des matchs
      </div>

      <!-- Répartition joueurs par poste -->
      <div class="card p-5">
        <p class="label-section mb-4"><i class="fas fa-chart-pie mr-2 text-yellow-400"></i>Effectif par poste</p>
        <div class="grid grid-cols-4 gap-3">
          <div v-for="pos in POSITIONS" :key="pos" class="text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-2"
                 :class="posBgLight(pos)">
              <span class="text-xs font-black text-white" :class="posBg(pos).replace('bg-','text-').replace('/10','')">
                {{ posShort(pos) }}
              </span>
            </div>
            <p class="text-2xl font-black text-white">{{ playersByPos(pos).length }}</p>
            <p class="text-[10px] text-gray-600">{{ pos }}s</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TAB: NEWS ─────────────────────────────────────────── -->
    <div v-show="activeTab === 'news'" class="space-y-3">
      <div v-if="loadingNews" class="card py-10 text-center">
        <i class="fas fa-spinner fa-spin text-yellow-400 text-2xl block mb-3"></i>
        <span class="text-gray-500 text-sm">Chargement des actualités…</span>
      </div>

      <template v-else-if="news.length">
        <a v-for="(article, i) in news" :key="i"
           :href="article.link" target="_blank" rel="noopener"
           class="card flex gap-4 p-4 hover:bg-white/8 transition-colors cursor-pointer group">
          <div v-if="article.image"
               class="w-24 h-16 rounded-xl overflow-hidden shrink-0 bg-white/5">
            <img :src="article.image" class="w-full h-full object-cover" loading="lazy" />
          </div>
          <div v-else class="w-24 h-16 rounded-xl shrink-0 overflow-hidden flex items-center justify-center"
               :style="`background:${team.color}15`">
            <img :src="flagImg(team.code)" :alt="team.name" class="w-full h-full object-cover opacity-70" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-semibold line-clamp-2 group-hover:text-yellow-400 transition-colors">
              {{ article.title }}
            </p>
            <p class="text-gray-600 text-xs mt-1 line-clamp-2">{{ article.description }}</p>
            <div class="flex items-center gap-2 mt-2 text-[10px] text-gray-700">
              <i class="fas fa-newspaper"></i>
              <span>{{ article.source }}</span>
              <span>·</span>
              <span>{{ article.date }}</span>
            </div>
          </div>
        </a>
      </template>

      <div v-else class="card py-12 text-center">
        <img :src="flagImg(team.code)" :alt="team.name" class="w-20 h-14 object-cover rounded-xl mx-auto mb-4 opacity-60" />
        <p class="text-white font-bold mb-1">Actualités {{ team.name }}</p>
        <p class="text-gray-600 text-sm mb-4">Les actualités seront disponibles prochainement</p>
        <a :href="`https://www.google.com/search?q=${encodeURIComponent(team.name + ' FIFA Coupe du Monde 2026')}&tbm=nws`"
           target="_blank"
           class="inline-flex items-center gap-2 btn-ghost text-sm">
          <i class="fab fa-google text-yellow-400"></i>
          Voir sur Google News
        </a>
      </div>
    </div>

  </div>

  <!-- Team not found -->
  <div v-else class="card py-20 text-center">
    <span class="text-5xl block mb-4">🏳️</span>
    <p class="text-white font-bold text-lg mb-2">Équipe introuvable</p>
    <p class="text-gray-600 text-sm mb-4">Code "{{ $route.params.code }}" non reconnu</p>
    <RouterLink to="/groupes" class="btn-ghost">← Retour aux groupes</RouterLink>
  </div>

  <!-- ── PLAYER MODAL ──────────────────────────────────────── -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="selectedPlayer"
           class="fixed inset-0 z-50 flex items-center justify-center p-4"
           style="background:rgba(0,0,0,.85)" @click.self="selectedPlayer=null">
        <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
          <!-- Photo header -->
          <div class="h-56 relative overflow-hidden bg-black"
               :class="!selectedPlayer.photo ? posBgLight(selectedPlayer.position) : ''">
            <!-- Photo joueur si disponible -->
            <img v-if="selectedPlayer.photo"
                 :src="selectedPlayer.photo"
                 class="absolute inset-0 w-full h-full object-cover object-top" />
            <!-- Fallback : drapeau image centré -->
            <div v-else class="w-full h-full flex items-center justify-center">
              <img :src="flagImg(team?.code)"
                   :alt="team?.name"
                   class="w-40 h-40 object-contain opacity-30 select-none" />
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0d0d2b] via-[#0d0d2b]/20 to-transparent"></div>
            <!-- Fermer -->
            <button @click="selectedPlayer=null"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/70 hover:bg-black
                           text-white flex items-center justify-center transition-colors z-10">
              <i class="fas fa-xmark text-sm"></i>
            </button>
            <!-- Badge poste -->
            <span class="absolute bottom-3 left-4 text-xs font-black px-2.5 py-1 rounded-full uppercase text-white z-10"
                  :class="posBg(selectedPlayer.position)">
              {{ posShort(selectedPlayer.position) }}
            </span>
            <!-- Numéro maillot si dispo -->
            <span v-if="selectedPlayer.number"
                  class="absolute bottom-3 right-4 text-xs font-black text-white/50 z-10">
              #{{ selectedPlayer.number }}
            </span>
          </div>
          <!-- Info -->
          <div class="p-5 space-y-3">
            <div class="flex items-center gap-3">
              <!-- Mini drapeau -->
              <img :src="flagImg(team?.code)" :alt="team?.name"
                   class="w-8 h-8 object-contain rounded shrink-0" />
              <div>
                <h3 class="text-white font-black text-xl leading-tight">{{ selectedPlayer.name }}</h3>
                <p class="text-gray-500 text-sm">{{ selectedPlayer.team }}</p>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
              <div class="bg-white/5 rounded-xl p-3 text-center">
                <p class="text-yellow-400 font-black text-lg">#{{ selectedPlayer.number ?? '—' }}</p>
                <p class="text-gray-600 text-[10px]">Maillot</p>
              </div>
              <div class="bg-white/5 rounded-xl p-3 text-center">
                <p class="text-white font-black text-lg">{{ selectedPlayer.birth_date ? calcAge(selectedPlayer.birth_date) : '—' }}</p>
                <p class="text-gray-600 text-[10px]">Âge</p>
              </div>
              <div class="bg-white/5 rounded-xl p-3 text-center">
                <p class="text-white font-bold text-sm truncate">{{ selectedPlayer.club ?? '—' }}</p>
                <p class="text-gray-600 text-[10px]">Club</p>
              </div>
            </div>
            <p v-if="selectedPlayer.birth_date" class="text-gray-600 text-xs text-center">
              Né le {{ selectedPlayer.birth_date }}
            </p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { getTeam, TEAMS } from '@/services/api'
import { usePlayers, calcAge, posShort, posBg, posBgLight, POSITIONS } from '@/composables/usePlayers'
import { flagImg } from '@/utils/flag'

const route  = useRoute()
const store  = useAppStore()
const { players, loadApiClubs } = usePlayers()

// ── Equipe ────────────────────────────────────────────────
const team = computed(() => getTeam(route.params.code?.toUpperCase()))

// ── Tabs ──────────────────────────────────────────────────
const activeTab = ref('joueurs')
const tabs = computed(() => [
  { id: 'joueurs', label: 'Joueurs',  icon: 'fa-users',      count: teamPlayers.value.length },
  { id: 'matchs',  label: 'Matchs',   icon: 'fa-futbol',     count: teamFixtures.value.length },
  { id: 'stats',   label: 'Stats',    icon: 'fa-chart-bar',  count: null },
  { id: 'news',    label: 'Actualités', icon: 'fa-newspaper', count: null },
])

// ── Joueurs de l'équipe ───────────────────────────────────
const teamPlayers = computed(() => {
  if (!team.value) return []
  return players.value.filter(p => p.team === team.value.name ||
                                   p.code === team.value.code)
})

function playersByPos(pos) {
  return teamPlayers.value.filter(p => p.position === pos)
}

// ── Coach (données statiques) ─────────────────────────────
const COACHES = {
  MAR:'Walid Regragui', FRA:'Didier Deschamps', BRA:'Dorival Júnior',
  ARG:'Lionel Scaloni', ESP:'Luis de la Fuente', GER:'Julian Nagelsmann',
  POR:'Roberto Martínez', NED:'Ronald Koeman', BEL:'Domenico Tedesco',
  ENG:'Gareth Southgate', ITA:'Luciano Spalletti', CRO:'Zlatko Dalić',
  USA:'Mauricio Pochettino', MEX:'Javier Aguirre', CAN:'Jesse Marsch',
  URU:'Marcelo Bielsa', COL:'Néstor Lorenzo', ECU:'Sebastián Beccacece',
  PAR:'Gustavo Alfaro', PAN:'Thomas Christiansen',
  SEN:'Aliou Cissé', NGA:'Finidi George', CMR:'Marc Brys',
  EGY:'Hossam Hassan', GHA:'Otto Addo', COD:'Sébastien Desabre',
  ALG:'Vladimir Petkovic', TUN:'Faouzi Benzarti', CPV:'Pedro Brito',
  RSA:'Hugo Broos', CIV:"Emerse Faé", MAR:'Walid Regragui',
  JPN:'Hajime Moriyasu', KOR:'Hong Myung-bo', AUS:'Tony Popovic',
  IRN:'Amir Ghalenoei', UZB:'Srecko Katanec', JOR:'Hossam Hassan',
  NZL:'Darren Bazeley', QAT:'Marquez Lopez',
  SUI:'Murat Yakin', TUR:'Vincenzo Montella', NOR:'Ståle Solbakken',
  AUT:'Ralf Rangnick', SCO:'Steve Clarke', CZE:'Ivan Hašek',
  SWE:'Jon Dahl Tomasson', BIH:'Sergej Barbarez', CUW:'Remko Bicentini',
  KSA:'Hervé Renard', IRQ:'Jesús Casas', HAI:'Marc Collat',
}
const coach = computed(() => team.value ? (COACHES[team.value.code] ?? null) : null)

// ── Standings ─────────────────────────────────────────────
const standing = computed(() => team.value ? store.getStanding(team.value.code) : null)

const miniStats = computed(() => {
  const s = standing.value
  if (!s) return {}
  return {
    'Pts': { val: s.points ?? 0, cls: 'text-yellow-400' },
    'J':   { val: s.all?.played ?? 0 },
    'G':   { val: s.all?.win ?? 0,  cls: 'text-green-400' },
    'N':   { val: s.all?.draw ?? 0, cls: 'text-yellow-300' },
    'P':   { val: s.all?.lose ?? 0, cls: 'text-red-400' },
  }
})

function getStandingVal(code, key) {
  const s = store.getStanding(code)
  if (!s) return '—'
  if (key === 'points') return s.points ?? 0
  if (key === 'played') return s.all?.played ?? 0
  if (key === 'win')    return s.all?.win ?? 0
  if (key === 'draw')   return s.all?.draw ?? 0
  if (key === 'lose')   return s.all?.lose ?? 0
  if (key === 'goalsFor')     return s.all?.goals?.for ?? 0
  if (key === 'goalsAgainst') return s.all?.goals?.against ?? 0
  return '—'
}

const detailStats = computed(() => {
  const s = standing.value
  if (!s) return []
  return [
    { label: 'Buts marqués',   val: s.all?.goals?.for ?? 0,         icon: 'fa-futbol',        },
    { label: 'Buts encaissés', val: s.all?.goals?.against ?? 0,     icon: 'fa-shield-halved', },
    { label: 'Différence',     val: (s.goalsDiff ?? 0) > 0 ? '+' + s.goalsDiff : s.goalsDiff ?? 0, icon: 'fa-arrow-trend-up' },
    { label: 'Points',         val: s.points ?? 0,                  icon: 'fa-star',          },
  ]
})

// Équipes du même groupe
const groupTeams = computed(() => {
  if (!team.value) return []
  return Object.values(store.groupsFromFixtures ?? {})
    .find(arr => arr.includes(team.value.code)) ?? []
})

// ── Matchs ────────────────────────────────────────────────
const teamFixtures = computed(() => {
  if (!team.value) return []
  return (store.fixtures ?? []).filter(m =>
    m.homeCode === team.value.code || m.awayCode === team.value.code
  ).sort((a, b) => new Date(a.date) - new Date(b.date))
})

function fmtDay(dateStr)   { return new Date(dateStr).getDate() }
function fmtMonth(dateStr) { return new Date(dateStr).toLocaleDateString('fr-FR', { month: 'short' }) }

function scoreColor(m, side) {
  const h = m.scoreHome, a = m.scoreAway
  if (h === null) return 'text-gray-700'
  if (side === 'home') {
    if (m.homeCode === team.value?.code) return h > a ? 'text-green-400' : h === a ? 'text-yellow-400' : 'text-red-400'
    return 'text-white'
  } else {
    if (m.awayCode === team.value?.code) return a > h ? 'text-green-400' : a === h ? 'text-yellow-400' : 'text-red-400'
    return 'text-white'
  }
}

// ── News (Google RSS proxy-free) ──────────────────────────
const news         = ref([])
const loadingNews  = ref(false)

async function loadNews() {
  if (!team.value) return
  loadingNews.value = true
  try {
    const q   = encodeURIComponent(`${team.value.name} football Coupe du Monde 2026`)
    const url = `https://api.rss2json.com/v1/api.json?rss_url=${encodeURIComponent('https://news.google.com/rss/search?q=' + q + '&hl=fr&gl=FR&ceid=FR:fr')}&api_key=public&count=8`
    const res = await fetch(url)
    const data = await res.json()
    news.value = (data.items ?? []).map(item => ({
      title:       item.title,
      description: item.description?.replace(/<[^>]+>/g, '').slice(0, 120) + '…',
      link:        item.link,
      image:       item.enclosure?.link || null,
      source:      item.author || item.source || 'Google News',
      date:        new Date(item.pubDate).toLocaleDateString('fr-FR'),
    }))
  } catch { news.value = [] }
  finally  { loadingNews.value = false }
}

// flagImg imported from @/utils/flag

// ── Player modal ──────────────────────────────────────────
const selectedPlayer = ref(null)

// ── Init ──────────────────────────────────────────────────
watch(() => route.params.code, () => {
  activeTab.value    = 'joueurs'
  selectedPlayer.value = null
  news.value         = []
  if (activeTab.value === 'news') loadNews()
}, { immediate: true })

watch(activeTab, (tab) => {
  if (tab === 'news' && !news.value.length) loadNews()
})

onMounted(loadApiClubs)
</script>

<style scoped>
.label-section { @apply text-xs text-gray-500 uppercase tracking-widest font-bold; }
.modal-enter-active, .modal-leave-active { transition: opacity .2s, transform .2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(.96); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
