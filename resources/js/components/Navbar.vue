<template>
  <header class="sticky top-0 z-50 bg-base-200 border-b border-base-300 shadow-lg">
    <div class="max-w-7xl mx-auto px-4">

      <!-- Ligne 1 : Logo / Countdown / Status -->
      <div class="flex items-center justify-between py-2.5 border-b border-base-300">

        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-3 group shrink-0">
          <span class="text-3xl group-hover:scale-110 transition-transform">🏆</span>
          <div class="leading-tight">
            <div class="text-base font-black text-base-content">FIFA WORLD CUP</div>
            <div class="text-xs font-bold text-primary tracking-widest">2026 USA · CAN · MEX</div>
          </div>
        </RouterLink>

        <!-- Countdown -->
        <div v-if="countdown.days >= 0" class="hidden md:flex items-center gap-1">
          <template v-if="countdown.days > 0">
            <div class="bg-base-300 rounded-lg px-3 py-1 text-center">
              <div class="text-xl font-black text-primary leading-none">{{ countdown.days }}</div>
              <div class="text-[10px] text-base-content/30 uppercase">j</div>
            </div>
            <span class="text-primary font-black">:</span>
            <div class="bg-base-300 rounded-lg px-3 py-1 text-center">
              <div class="text-xl font-black text-primary leading-none">{{ pad(countdown.hours) }}</div>
              <div class="text-[10px] text-base-content/30 uppercase">h</div>
            </div>
            <span class="text-primary font-black">:</span>
            <div class="bg-base-300 rounded-lg px-3 py-1 text-center">
              <div class="text-xl font-black text-primary leading-none">{{ pad(countdown.mins) }}</div>
              <div class="text-[10px] text-base-content/30 uppercase">m</div>
            </div>
          </template>
          <template v-else>
            <div class="badge badge-error gap-1.5 font-bold animate-pulse">
              <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
              Tournoi en cours !
            </div>
          </template>
        </div>

        <!-- Status + actions -->
        <div class="flex items-center gap-2 shrink-0">
          <div class="hidden sm:flex badge gap-1.5 font-medium" :class="statusBadgeClass">
            <span class="w-1.5 h-1.5 rounded-full shrink-0"
                  :class="store.status.type === 'live'    ? 'bg-error animate-ping' :
                          store.status.type === 'ok'      ? 'bg-success' : 'bg-warning animate-pulse'">
            </span>
            <span class="max-w-[140px] truncate text-xs">{{ store.status.msg || 'En attente…' }}</span>
          </div>
          <button @click="store.forceRefresh()" class="btn btn-ghost btn-xs btn-square">
            <i class="fas fa-rotate-right text-xs" :class="store.status.type==='loading' ? 'animate-spin' : ''"></i>
          </button>
          <div v-if="store.liveCount > 0" class="badge badge-error gap-1 animate-pulse font-black text-xs">
            🔴 {{ store.liveCount }} LIVE
          </div>
        </div>
      </div>

      <!-- Ligne 2 : Navigation -->
      <nav class="flex overflow-x-auto scrollbar-hide">
        <RouterLink v-for="item in navItems" :key="item.to"
                    :to="item.to"
                    class="flex items-center gap-1.5 px-4 py-3 text-sm font-semibold whitespace-nowrap
                           border-b-2 border-transparent text-base-content/40 transition-colors
                           hover:text-base-content hover:border-primary/50 shrink-0"
                    active-class="!text-primary !border-primary">
          <i :class="item.icon" class="text-xs"></i>
          {{ item.label }}
          <img v-if="item.flag" :src="item.flag" :alt="item.label"
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
  { to:'/',           label:'Accueil',     icon:'fas fa-home' },
  { to:'/groupes',    label:'Groupes',     icon:'fas fa-layer-group' },
  { to:'/calendrier', label:'Calendrier',  icon:'fas fa-calendar' },
  { to:'/joueurs',    label:'Joueurs',     icon:'fas fa-users' },
  { to:'/actualites', label:'Actualités',  icon:'fas fa-newspaper' },
  { to:'/maroc',      label:'Maroc',       icon:'fas fa-star', flag:'https://flagcdn.com/w40/ma.png' },
  { to:'/bracket',    label:'Bracket',     icon:'fas fa-trophy' },
]

const statusBadgeClass = computed(() => ({
  'badge-success badge-outline': store.status.type === 'ok',
  'badge-error badge-outline':   store.status.type === 'live',
  'badge-warning badge-outline': store.status.type === 'loading' || store.status.type === 'error',
  'badge-ghost':                 store.status.type === 'idle',
}))

const countdown = ref({ days: 0, hours: 0, mins: 0 })
function pad(n) { return String(n).padStart(2,'0') }
function updateCd() {
  const diff = new Date('2026-06-11T20:00:00-05:00') - Date.now()
  if (diff <= 0) { countdown.value = { days: -1, hours: 0, mins: 0 }; return }
  countdown.value = {
    days:  Math.floor(diff / 86400000),
    hours: Math.floor((diff % 86400000) / 3600000),
    mins:  Math.floor((diff % 3600000) / 60000),
  }
}
let cdTimer
onMounted(() => { updateCd(); cdTimer = setInterval(updateCd, 30000) })
onUnmounted(() => clearInterval(cdTimer))
</script>
