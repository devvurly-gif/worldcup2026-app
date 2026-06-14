<template>
  <div v-if="loading && !match" class="flex items-center justify-center py-24">
    <i class="fas fa-circle-notch animate-spin text-yellow-400 text-3xl"></i>
  </div>

  <div v-else-if="match" class="max-w-3xl mx-auto space-y-6">

    <!-- Back -->
    <button @click="$router.back()" class="btn-ghost text-sm">
      <i class="fas fa-arrow-left mr-2"></i>Retour
    </button>

    <!-- Live Stream Player -->
    <LivePlayer v-if="match.isLive || streamUrl"
                :stream-url="streamUrl"
                :is-admin="isAdmin"
                :fixture-id="match.id"
                @stream-saved="streamUrl = $event"
                ref="playerRef" />

    <!-- Header match -->
    <div class="card overflow-hidden">
      <div class="hero-bg p-6">
        <!-- Phase + Live badge -->
        <div class="text-center mb-4">
          <span class="text-xs font-black text-yellow-400 uppercase tracking-widest">
            {{ match.phase }}{{ match.group ? ` · Groupe ${match.group}` : '' }}
          </span>
          <div class="text-xs text-gray-400 mt-1">
            {{ formatDate(match.date) }} · {{ match.time }} ·
            <i class="fas fa-map-marker-alt mx-1"></i>{{ match.stadium }}, {{ match.city }}
          </div>
        </div>

        <!-- Score principal -->
        <div class="flex items-center justify-around">
          <!-- Domicile -->
          <div class="text-center flex-1">
            <img v-if="match.homeLogo" :src="match.homeLogo" class="w-16 h-16 object-contain mx-auto mb-2"/>
            <img v-else :src="flagImg(match.homeCode)" :alt="match.homeCode"
                 class="w-16 h-11 object-cover rounded-lg mx-auto mb-2" />
            <TeamLink :code="match.homeCode" :name="match.homeName"
                      :show-flag="false" cls="text-lg font-black justify-center" />
            <div class="text-xs text-gray-500">{{ getTeam(match.homeCode)?.confed }}</div>
            <!-- Buteurs domicile -->
            <div v-if="homeGoals.length" class="mt-2 space-y-0.5">
              <div v-for="e in homeGoals" :key="e.minute+e.player"
                   class="text-xs text-gray-300 flex items-center justify-center gap-1">
                <span>⚽</span>
                <span>{{ e.player }}</span>
                <span class="text-yellow-400 font-bold">{{ e.minute }}'</span>
                <span v-if="e.detail==='Own Goal'" class="text-red-400">(csc)</span>
                <span v-if="e.detail==='Penalty'" class="text-blue-400">(pen)</span>
              </div>
            </div>
          </div>

          <!-- Score central -->
          <div class="text-center px-4">
            <!-- Indicateur LIVE -->
            <div v-if="match.isLive" class="badge-live mb-2 mx-auto inline-flex items-center gap-1.5 px-3 py-1">
              <span class="w-2 h-2 rounded-full bg-red-400 animate-pulse inline-block"></span>
              <span class="font-black text-sm">{{ displayElapsed }}'</span>
            </div>
            <div v-else-if="match.status === 'HT'" class="mb-2 text-center">
              <span class="text-xs font-bold text-orange-400 px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/30">
                MI-TEMPS
              </span>
            </div>
            <div v-else-if="match.status === 'FT'" class="mb-2 text-center">
              <span class="text-xs font-bold text-gray-400 px-3 py-1 rounded-full bg-white/5 border border-white/10">
                TERMINÉ
              </span>
            </div>

            <!-- Score -->
            <div class="text-6xl font-black leading-none"
                 :class="match.isLive ? 'text-red-400' : match.scoreHome !== null ? 'text-yellow-400' : 'text-gray-600'">
              {{ match.scoreHome !== null ? match.scoreHome : '—' }}
            </div>
            <div class="text-2xl font-black text-gray-500 my-1">:</div>
            <div class="text-6xl font-black leading-none"
                 :class="match.isLive ? 'text-red-400' : match.scoreAway !== null ? 'text-yellow-400' : 'text-gray-600'">
              {{ match.scoreAway !== null ? match.scoreAway : '—' }}
            </div>

            <div v-if="match.status === 'NS'" class="text-yellow-400 font-bold text-lg mt-2">{{ match.time }}</div>

            <!-- Refresh indicator -->
            <div v-if="match.isLive" class="mt-2 text-xs text-gray-600 flex items-center justify-center gap-1">
              <i class="fas fa-sync-alt" :class="loading ? 'animate-spin' : ''"></i>
              <span>{{ loading ? 'Mise à jour...' : `Actualisé il y a ${secondsSinceUpdate}s` }}</span>
            </div>
          </div>

          <!-- Extérieur -->
          <div class="text-center flex-1">
            <img v-if="match.awayLogo" :src="match.awayLogo" class="w-16 h-16 object-contain mx-auto mb-2"/>
            <img v-else :src="flagImg(match.awayCode)" :alt="match.awayCode"
                 class="w-16 h-11 object-cover rounded-lg mx-auto mb-2" />
            <TeamLink :code="match.awayCode" :name="match.awayName"
                      :show-flag="false" cls="text-lg font-black justify-center" />
            <div class="text-xs text-gray-500">{{ getTeam(match.awayCode)?.confed }}</div>
            <!-- Buteurs extérieur -->
            <div v-if="awayGoals.length" class="mt-2 space-y-0.5">
              <div v-for="e in awayGoals" :key="e.minute+e.player"
                   class="text-xs text-gray-300 flex items-center justify-center gap-1">
                <span>⚽</span>
                <span>{{ e.player }}</span>
                <span class="text-yellow-400 font-bold">{{ e.minute }}'</span>
                <span v-if="e.detail==='Own Goal'" class="text-red-400">(csc)</span>
                <span v-if="e.detail==='Penalty'" class="text-blue-400">(pen)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Météo -->
      <div v-if="weather" class="px-6 py-3 border-t border-white/10 flex items-center justify-center gap-4 text-sm">
        <span class="text-2xl">{{ weather.icon }}</span>
        <span class="font-bold text-white">{{ weather.temp }}°C</span>
        <span class="text-gray-400">{{ weather.description }}</span>
        <span class="text-gray-600">💧 {{ weather.humidity }}%</span>
        <span class="text-gray-600">🌬️ {{ weather.wind }} km/h</span>
      </div>
    </div>

    <!-- Timeline des buts (si matchs joués) -->
    <div v-if="events.length" class="card p-4">
      <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Buts</h3>
      <div class="relative">
        <!-- Ligne centrale -->
        <div class="absolute left-1/2 top-0 bottom-0 w-px bg-white/10"></div>
        <div class="space-y-2">
          <div v-for="(e, i) in events" :key="i"
               class="flex items-center gap-2"
               :class="e.side === 'home' ? 'flex-row' : 'flex-row-reverse'">
            <!-- Info joueur -->
            <div class="flex-1" :class="e.side === 'home' ? 'text-right pr-4' : 'text-left pl-4'">
              <span class="text-sm font-bold text-white">{{ e.player }}</span>
              <span v-if="e.detail==='Penalty'" class="ml-1 text-xs text-blue-400">(pen)</span>
              <span v-if="e.detail==='Own Goal'" class="ml-1 text-xs text-red-400">(csc)</span>
            </div>
            <!-- Badge minute central -->
            <div class="relative z-10 flex flex-col items-center">
              <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-black"
                   :class="e.detail==='Own Goal' ? 'bg-red-500/20 text-red-400 border border-red-500/40' : 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/40'">
                {{ e.minute }}'
              </div>
              <span class="text-lg -mt-1">{{ e.detail==='Own Goal' ? '🥅' : e.detail==='Penalty' ? '🎯' : '⚽' }}</span>
            </div>
            <div class="flex-1" :class="e.side === 'home' ? 'pl-4' : 'pr-4'"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Onglets -->
    <div class="flex gap-2 border-b border-white/10">
      <button @click="activeTab = 'Événements'"
              class="px-4 py-2.5 text-sm font-semibold transition-colors border-b-2 -mb-px"
              :class="activeTab === 'Événements'
                ? 'text-yellow-400 border-yellow-400'
                : 'text-gray-500 border-transparent hover:text-gray-300'">
        Événements
        <span v-if="events.length"
              class="ml-1.5 text-xs bg-yellow-500/20 text-yellow-400 px-1.5 py-0.5 rounded-full">
          {{ events.length }}
        </span>
      </button>
    </div>

    <!-- Événements -->
    <div v-if="activeTab === 'Événements'">
      <div v-if="events.length" class="space-y-2">
        <div v-for="(e, i) in events" :key="i"
             class="card px-4 py-3 flex items-center gap-3"
             :class="e.side === 'home' ? 'flex-row' : 'flex-row-reverse'">
          <span class="text-xs font-black text-yellow-400 w-10 text-center shrink-0">{{ e.minute }}'</span>
          <span class="text-xl shrink-0">{{ eventIcon(e) }}</span>
          <div :class="e.side === 'home' ? 'text-left' : 'text-right'" class="flex-1 min-w-0">
            <div class="text-sm font-bold text-white truncate">{{ e.player }}</div>
            <div class="text-xs text-gray-500">
              {{ eventLabel(e) }} · <span class="text-gray-400">{{ e.team }}</span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="card p-8 text-center text-gray-600">
        <i class="fas fa-futbol text-3xl mb-3 block opacity-30"></i>
        <p>{{ match.status === 'NS' ? 'Match pas encore commencé' : 'Aucun événement enregistré' }}</p>
      </div>
    </div>


  </div>

  <div v-else class="card p-12 text-center text-gray-600">
    <i class="fas fa-circle-xmark text-4xl mb-4 block"></i>
    Match introuvable
    <div class="mt-4">
      <RouterLink to="/calendrier" class="btn-primary inline-block">← Calendrier</RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api, getTeam } from '@/services/api'
import TeamLink from '@/components/TeamLink.vue'
import LivePlayer from '@/components/LivePlayer.vue'
import { useAppStore } from '@/stores/app'
import { flagImg } from '@/utils/flag'

const route = useRoute()
const store = useAppStore()

const match      = ref(null)
const events     = ref([])
const weather    = ref(null)
const loading    = ref(true)
const activeTab  = ref('Événements')
const playerRef  = ref(null)
const streamUrl  = ref('')

const isAdmin = computed(() => !!localStorage.getItem('auth_token'))

async function loadStream(fixtureId) {
  try {
    const res = await fetch(`/api/wc26/streams/${fixtureId}`)
    const d   = await res.json()
    streamUrl.value = d.stream_url ?? ''
  } catch {
    streamUrl.value = ''
  }
}

// Live chrono
const displayElapsed    = ref(0)
const secondsSinceUpdate = ref(0)
let   elapsedTimer      = null
let   pollTimer         = null
let   uiTimer           = null
let   lastUpdated       = Date.now()

const homeGoals = computed(() => events.value.filter(e => e.side === 'home'))
const awayGoals = computed(() => events.value.filter(e => e.side === 'away'))

async function load(id, silent = false) {
  if (!silent) loading.value = true
  try {
    const cached = store.fixtures.find(f => String(f.id) === String(id))
    if (cached && !match.value) match.value = cached

    const res = await api.fixture(id)
    if (res.data) {
      const d = res.data
      match.value = {
        ...match.value,
        id: d.id, date: d.date, time: d.time,
        stadium: d.stadium, city: d.city,
        phase: d.phase, group: d.group,
        status: d.status, elapsed: d.elapsed, isLive: d.is_live,
        homeCode: match.value?.homeCode ?? d.home?.fifa_code,
        awayCode: match.value?.awayCode ?? d.away?.fifa_code,
        homeName: d.home?.name,
        awayName: d.away?.name,
        homeLogo: d.home?.logo,
        awayLogo: d.away?.logo,
        scoreHome: d.score?.home,
        scoreAway: d.score?.away,
      }
      events.value = d.events ?? []

      // Charger stream URL depuis l'API
      loadStream(d.id)

      // Init live chrono
      if (d.is_live && d.elapsed) {
        displayElapsed.value = d.elapsed
        startElapsedTicker(d.elapsed)
      } else {
        stopElapsedTicker()
      }

      lastUpdated = Date.now()
    }
  } catch(e) {
    console.warn('Détail match:', e.message)
  } finally {
    loading.value = false
  }
}

function startElapsedTicker(base) {
  stopElapsedTicker()
  let sec = 0
  elapsedTimer = setInterval(() => {
    sec++
    displayElapsed.value = base + Math.floor(sec / 60)
  }, 1000)
}

function stopElapsedTicker() {
  if (elapsedTimer) { clearInterval(elapsedTimer); elapsedTimer = null }
}

function startPolling(id) {
  stopPolling()
  pollTimer = setInterval(() => load(id, true), 30_000)
  uiTimer   = setInterval(() => {
    secondsSinceUpdate.value = Math.floor((Date.now() - lastUpdated) / 1000)
  }, 1000)
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
  if (uiTimer)   { clearInterval(uiTimer);   uiTimer   = null }
}

function eventIcon(e) {
  if (e.type === 'Goal')  return e.detail === 'Own Goal' ? '🥅' : e.detail === 'Penalty' ? '🎯' : '⚽'
  if (e.type === 'Card')  return e.detail === 'Yellow Card' ? '🟨' : '🟥'
  if (e.type === 'subst') return '🔄'
  return '📌'
}

function eventLabel(e) {
  if (e.type === 'Goal')  return e.detail === 'Own Goal' ? 'But contre son camp' : e.detail === 'Penalty' ? 'But sur penalty' : 'But'
  if (e.type === 'Card')  return e.detail === 'Yellow Card' ? 'Carton jaune' : 'Carton rouge'
  if (e.type === 'subst') return 'Remplacement'
  return e.detail
}

function pct(val) {
  const v = parseInt(val) || 0
  return Math.min(100, v > 10 ? 100 : v * 10)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', {
    weekday:'long', day:'numeric', month:'long', year:'numeric'
  })
}

onMounted(async () => {
  const id = route.params.id
  await load(id)
  if (match.value?.isLive) {
    startPolling(id)
  }
})

onUnmounted(() => {
  stopPolling()
  stopElapsedTicker()
})

watch(() => route.params.id, async id => {
  stopPolling()
  stopElapsedTicker()
  events.value = []
  stats.value  = []
  await load(id)
  if (match.value?.isLive) startPolling(id)
})

watch(() => match.value?.isLive, isLive => {
  const id = route.params.id
  if (isLive) startPolling(id)
  else stopPolling()
})
</script>
