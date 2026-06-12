<template>
  <div v-if="loading" class="flex items-center justify-center py-24">
    <i class="fas fa-circle-notch animate-spin text-yellow-400 text-3xl"></i>
  </div>

  <div v-else-if="match" class="max-w-3xl mx-auto space-y-6">

    <!-- Back -->
    <button @click="$router.back()" class="btn-ghost text-sm">
      <i class="fas fa-arrow-left mr-2"></i>Retour
    </button>

    <!-- Header match -->
    <div class="card overflow-hidden">
      <div class="hero-bg p-6">
        <!-- Phase -->
        <div class="text-center mb-4">
          <span class="text-xs font-black text-yellow-400 uppercase tracking-widest">
            {{ match.phase }}{{ match.group ? ` · Groupe ${match.group}` : '' }}
          </span>
          <div class="text-xs text-gray-400 mt-1">
            {{ formatDate(match.date) }} · {{ match.time }} ·
            <i class="fas fa-map-marker-alt mx-1"></i>{{ match.stadium }}, {{ match.city }}
          </div>
        </div>

        <!-- Score -->
        <div class="flex items-center justify-around">
          <!-- Home -->
          <div class="text-center flex-1">
            <img v-if="match.homeLogo" :src="match.homeLogo" class="w-16 h-16 object-contain mx-auto mb-2"/>
            <img v-else :src="flagImg(match.homeCode)" :alt="match.homeCode"
                 class="w-16 h-11 object-cover rounded-lg mx-auto mb-2" />
            <TeamLink :code="match.homeCode" :name="match.homeName"
                      :show-flag="false" cls="text-lg font-black justify-center" />
            <div class="text-xs text-gray-500">{{ getTeam(match.homeCode)?.confed }}</div>
          </div>

          <!-- Score central -->
          <div class="text-center px-6">
            <div v-if="match.isLive" class="badge-live mb-2 mx-auto inline-block">
              <span class="animate-blink">●</span> {{ match.elapsed }}'
            </div>
            <div class="text-5xl font-black"
                 :class="match.isLive ? 'text-red-400' : match.scoreHome !== null ? 'text-yellow-400' : 'text-gray-600'">
              {{ match.scoreHome !== null ? `${match.scoreHome} — ${match.scoreAway}` : 'VS' }}
            </div>
            <div v-if="match.status === 'HT'" class="text-orange-400 font-bold text-sm mt-1">Mi-temps</div>
            <div v-if="match.status === 'FT'" class="text-gray-500 text-sm mt-1">Match terminé</div>
            <div v-if="match.status === 'NS'" class="text-gray-500 text-sm mt-1">{{ match.time }}</div>
          </div>

          <!-- Away -->
          <div class="text-center flex-1">
            <img v-if="match.awayLogo" :src="match.awayLogo" class="w-16 h-16 object-contain mx-auto mb-2"/>
            <img v-else :src="flagImg(match.awayCode)" :alt="match.awayCode"
                 class="w-16 h-11 object-cover rounded-lg mx-auto mb-2" />
            <TeamLink :code="match.awayCode" :name="match.awayName"
                      :show-flag="false" cls="text-lg font-black justify-center" />
            <div class="text-xs text-gray-500">{{ getTeam(match.awayCode)?.confed }}</div>
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

    <!-- Onglets : Événements / Stats / Compos -->
    <div class="flex gap-2 border-b border-white/10">
      <button v-for="tab in tabs" :key="tab"
              @click="activeTab = tab"
              class="px-4 py-2.5 text-sm font-semibold transition-colors border-b-2 -mb-px"
              :class="activeTab === tab
                ? 'text-yellow-400 border-yellow-400'
                : 'text-gray-500 border-transparent hover:text-gray-300'">
        {{ tab }}
      </button>
    </div>

    <!-- Événements -->
    <div v-if="activeTab === 'Événements'">
      <div v-if="events.length" class="space-y-2">
        <div v-for="(e, i) in events" :key="i"
             class="card px-4 py-3 flex items-center gap-3"
             :class="e.team === match.homeName ? 'flex-row' : 'flex-row-reverse'">
          <span class="text-xs font-black text-yellow-400 w-8 text-center">{{ e.minute }}'</span>
          <span class="text-lg">{{ eventIcon(e) }}</span>
          <div :class="e.team === match.homeName ? 'text-left' : 'text-right'" class="flex-1">
            <div class="text-sm font-bold text-white">{{ e.player }}</div>
            <div class="text-xs text-gray-500">{{ e.detail }} · {{ e.team }}</div>
          </div>
        </div>
      </div>
      <div v-else class="card p-8 text-center text-gray-600">
        <i class="fas fa-whistle text-3xl mb-3 block"></i>
        {{ match.status === 'NS' ? 'Match pas encore commencé' : 'Aucun événement enregistré' }}
      </div>
    </div>

    <!-- Stats -->
    <div v-if="activeTab === 'Statistiques'">
      <div v-if="stats.length" class="space-y-3">
        <div v-for="s in stats[0]?.statistics ?? []" :key="s.type"
             class="card p-4">
          <div class="flex items-center justify-between mb-2 text-xs font-bold text-gray-400 uppercase">
            <span>{{ stats[0]?.team?.name }}</span>
            <span>{{ s.type }}</span>
            <span>{{ stats[1]?.statistics?.find(x=>x.type===s.type)?.value ?? '—' }}</span>
          </div>
          <div class="flex gap-2 items-center">
            <span class="text-sm font-black text-yellow-400 w-8">{{ s.value ?? 0 }}</span>
            <div class="flex-1 h-2 bg-gray-800 rounded-full overflow-hidden">
              <div class="h-full bg-yellow-400 rounded-full" :style="`width:${pct(s.value)}%`"></div>
            </div>
            <div class="flex-1 h-2 bg-gray-800 rounded-full overflow-hidden rotate-180">
              <div class="h-full bg-blue-400 rounded-full"
                   :style="`width:${pct(stats[1]?.statistics?.find(x=>x.type===s.type)?.value)}%`">
              </div>
            </div>
            <span class="text-sm font-black text-blue-400 w-8 text-right">
              {{ stats[1]?.statistics?.find(x=>x.type===s.type)?.value ?? 0 }}
            </span>
          </div>
        </div>
      </div>
      <div v-else class="card p-8 text-center text-gray-600">
        <i class="fas fa-chart-bar text-3xl mb-3 block"></i>
        Statistiques disponibles après le coup d'envoi
      </div>
    </div>

    <!-- Compositions -->
    <div v-if="activeTab === 'Compositions'">
      <div v-if="lineups.length" class="grid md:grid-cols-2 gap-6">
        <div v-for="l in lineups" :key="l.team" class="card p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-black text-white">{{ l.team }}</h3>
            <span class="text-yellow-400 font-bold text-sm">{{ l.formation }}</span>
          </div>
          <div class="space-y-1">
            <div v-for="p in l.startXI" :key="p.number"
                 class="flex items-center gap-3 py-1.5 border-b border-white/5">
              <span class="text-xs font-black text-gray-600 w-6">{{ p.number }}</span>
              <span class="text-sm text-white font-medium">{{ p.name }}</span>
              <span class="ml-auto text-xs text-gray-600">{{ p.pos }}</span>
            </div>
          </div>
          <!-- Remplaçants -->
          <div class="mt-3 pt-3 border-t border-white/10">
            <p class="text-xs text-gray-600 uppercase mb-2">Remplaçants</p>
            <div class="flex flex-wrap gap-1">
              <span v-for="p in l.substitutes" :key="p.number"
                    class="text-xs glass px-2 py-0.5 rounded-full text-gray-400">
                {{ p.number }}. {{ p.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="card p-8 text-center text-gray-600">
        <i class="fas fa-shirt text-3xl mb-3 block"></i>
        Compositions disponibles avant le coup d'envoi
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
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api, getTeam } from '@/services/api'
import TeamLink from '@/components/TeamLink.vue'
import { useAppStore } from '@/stores/app'
import { flagImg } from '@/utils/flag'

const route = useRoute()
const store = useAppStore()

const match   = ref(null)
const events  = ref([])
const stats   = ref([])
const lineups = ref([])
const weather = ref(null)
const loading = ref(true)
const activeTab = ref('Événements')
const tabs = ['Événements', 'Statistiques', 'Compositions']

async function load(id) {
  loading.value = true
  try {
    // D'abord chercher dans le store
    const cached = store.fixtures.find(f => String(f.id) === String(id))
    if (cached) match.value = cached

    // Puis charger les détails complets
    const res = await api.fixture(id)
    if (res.data) {
      const d = res.data
      match.value = {
        ...match.value,
        id: d.id, date: d.date, time: d.time,
        stadium: d.stadium, city: d.city,
        phase: d.phase, group: d.group,
        status: d.status, elapsed: d.elapsed, isLive: d.is_live,
        homeCode: match.value?.homeCode,
        awayCode: match.value?.awayCode,
        homeName: d.home?.name,
        awayName: d.away?.name,
        homeLogo: d.home?.logo,
        awayLogo: d.away?.logo,
        scoreHome: d.score?.home,
        scoreAway: d.score?.away,
      }
      events.value  = d.events  ?? []
      stats.value   = d.stats   ?? []
      lineups.value = d.lineups ?? []
    }
  } catch(e) {
    console.warn('Détail match:', e.message)
  } finally {
    loading.value = false
  }
}

function eventIcon(e) {
  if (e.type === 'Goal') return e.detail === 'Own Goal' ? '🥅' : '⚽'
  if (e.type === 'Card') return e.detail === 'Yellow Card' ? '🟨' : '🟥'
  if (e.type === 'subst') return '🔄'
  return '📌'
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

onMounted(() => load(route.params.id))
watch(() => route.params.id, id => load(id))
</script>
