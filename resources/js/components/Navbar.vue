<template>
  <header class="sticky top-0 z-50 hero-bg shadow-2xl">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Top bar -->
      <div class="flex items-center justify-between py-3 border-b border-white/10">
        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-3 group">
          <span class="text-3xl group-hover:scale-110 transition-transform">🏆</span>
          <div>
            <h1 class="text-lg font-black text-white leading-none">FIFA WORLD CUP</h1>
            <span class="text-yellow-400 font-bold text-xs tracking-widest">2026 USA · CAN · MEX</span>
          </div>
        </RouterLink>

        <!-- Compte à rebours -->
        <div v-if="countdown.days >= 0" class="hidden md:flex items-center gap-3 glass rounded-xl px-4 py-2">
          <template v-if="countdown.days > 0">
            <div class="text-center">
              <div class="text-2xl font-black text-yellow-400">{{ countdown.days }}</div>
              <div class="text-[10px] text-gray-500 uppercase">jours</div>
            </div>
            <span class="text-yellow-400 font-bold">:</span>
            <div class="text-center">
              <div class="text-2xl font-black text-yellow-400">{{ pad(countdown.hours) }}</div>
              <div class="text-[10px] text-gray-500 uppercase">heures</div>
            </div>
            <span class="text-yellow-400 font-bold">:</span>
            <div class="text-center">
              <div class="text-2xl font-black text-yellow-400">{{ pad(countdown.mins) }}</div>
              <div class="text-[10px] text-gray-500 uppercase">min</div>
            </div>
          </template>
          <template v-else>
            <span class="w-2 h-2 bg-red-500 rounded-full animate-blink"></span>
            <span class="text-yellow-400 font-bold text-sm">Tournoi en cours !</span>
          </template>
        </div>

        <!-- Status + Quota -->
        <div class="flex items-center gap-2">
          <div class="hidden sm:flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg"
               :class="statusClass">
            <span class="w-1.5 h-1.5 rounded-full"
                  :class="store.status.type === 'live' ? 'bg-red-400 animate-blink' :
                          store.status.type === 'ok'   ? 'bg-green-400' : 'bg-yellow-400 animate-pulse'">
            </span>
            <span class="font-medium max-w-[160px] truncate">{{ store.status.msg || 'En attente…' }}</span>
          </div>
          <button @click="store.forceRefresh()"
                  class="btn-ghost text-xs px-2.5 py-1.5">
            <i class="fas fa-rotate-right" :class="store.status.type==='loading' ? 'animate-spin' : ''"></i>
          </button>
          <!-- Live badge -->
          <span v-if="store.liveCount > 0"
                class="badge-live animate-pulse">
            🔴 {{ store.liveCount }} LIVE
          </span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex gap-0 overflow-x-auto">
        <RouterLink v-for="item in navItems" :key="item.to"
                    :to="item.to"
                    class="flex items-center gap-2 px-4 py-3 text-sm font-semibold whitespace-nowrap
                           transition-colors border-b-2 border-transparent text-gray-400
                           hover:text-white hover:border-yellow-400/50"
                    active-class="!text-yellow-400 !border-yellow-400">
          <i :class="item.icon" class="text-xs"></i>
          {{ item.label }}
          <img v-if="item.to==='/maroc'" src="https://flagcdn.com/w40/ma.png" alt="MA"
               class="w-5 h-3.5 object-cover rounded-sm" />
        </RouterLink>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAppStore } from '@/stores/app'

const store = useAppStore()

const navItems = [
  { to:'/',            label:'Accueil',     icon:'fas fa-home' },
  { to:'/groupes',     label:'Groupes',     icon:'fas fa-layer-group' },
  { to:'/calendrier',  label:'Calendrier',  icon:'fas fa-calendar' },
  { to:'/joueurs',     label:'Joueurs',     icon:'fas fa-users' },
  { to:'/actualites',  label:'Actualités',  icon:'fas fa-newspaper' },
  { to:'/maroc',       label:'Focus Maroc', icon:'fas fa-star' },
  { to:'/bracket',     label:'Bracket',     icon:'fas fa-trophy' },
]

const statusClass = computed(() => ({
  'bg-green-500/10 border border-green-500/20 text-green-400': store.status.type === 'ok',
  'bg-red-500/10 border border-red-500/20 text-red-400': store.status.type === 'live',
  'bg-yellow-500/10 border border-yellow-500/20 text-yellow-400': store.status.type === 'loading' || store.status.type === 'error',
  'glass text-gray-400': store.status.type === 'idle',
}))

// Countdown
const countdown = ref({ days: 0, hours: 0, mins: 0 })
function pad(n) { return String(n).padStart(2,'0') }
function updateCd() {
  const diff = new Date('2026-06-11T20:00:00-05:00') - Date.now()
  if (diff <= 0) { countdown.value = { days: -1, hours: 0, mins: 0 }; return }
  const d = Math.floor(diff / 86400000)
  const h = Math.floor((diff % 86400000) / 3600000)
  const m = Math.floor((diff % 3600000) / 60000)
  countdown.value = { days: d, hours: h, mins: m }
}
let cdTimer
onMounted(() => { updateCd(); cdTimer = setInterval(updateCd, 30000) })
onUnmounted(() => clearInterval(cdTimer))
</script>
