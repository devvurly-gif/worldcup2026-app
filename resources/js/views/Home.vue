<template>
  <div class="space-y-10">

    <!-- ══════════════════════════════════════════════
         HERO — Cinematic Section
    ══════════════════════════════════════════════ -->
    <section class="relative rounded-3xl overflow-hidden min-h-[420px] flex flex-col justify-between"
             style="background:linear-gradient(135deg,#020617 0%,#0f172a 30%,#1e1b4b 60%,#0c1a3a 100%)">

      <!-- Grid mesh overlay -->
      <div class="absolute inset-0 opacity-[0.04]"
           style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);background-size:40px 40px"></div>

      <!-- Glow blobs -->
      <div class="absolute top-0 left-1/4 w-96 h-96 rounded-full opacity-20 blur-3xl"
           style="background:radial-gradient(circle,#f59e0b,transparent 70%)"></div>
      <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full opacity-15 blur-3xl"
           style="background:radial-gradient(circle,#6366f1,transparent 70%)"></div>

      <!-- Trophy watermark -->
      <div class="absolute right-6 top-6 bottom-6 w-64 md:w-80 opacity-[0.06] hidden md:block"
           style="background:url('https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/FIFA_World_Cup_2026_logo.svg/400px-FIFA_World_Cup_2026_logo.svg.png') right center/contain no-repeat"></div>

      <!-- Content -->
      <div class="relative z-10 p-6 md:p-10 flex flex-col gap-8">

        <!-- Top row: badge + host countries -->
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 animate-pulse shadow-lg shadow-yellow-400/50"></span>
            <span class="text-yellow-400 text-xs font-black uppercase tracking-[0.2em]">FIFA World Cup 2026™</span>
          </div>
          <div class="flex items-center gap-2">
            <img src="https://flagcdn.com/w40/us.png" alt="USA" class="w-8 h-5 object-cover rounded shadow" />
            <img src="https://flagcdn.com/w40/ca.png" alt="CAN" class="w-8 h-5 object-cover rounded shadow" />
            <img src="https://flagcdn.com/w40/mx.png" alt="MEX" class="w-8 h-5 object-cover rounded shadow" />
            <span class="text-gray-400 text-xs ml-1 hidden sm:block">USA · CANADA · MEXIQUE</span>
          </div>
        </div>

        <!-- Main title -->
        <div>
          <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white leading-none tracking-tight">
            COUPE DU<br>
            <span style="background:linear-gradient(135deg,#f59e0b,#fbbf24,#fde68a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
              MONDE 2026
            </span>
          </h1>
          <p class="text-gray-400 mt-3 text-sm md:text-base max-w-lg">
            48 équipes · 104 matchs · 16 stades · 3 pays hôtes
          </p>
        </div>

        <!-- Countdown + Opening Match -->
        <div class="flex flex-wrap gap-4">

          <!-- Countdown -->
          <div class="glass rounded-2xl px-5 py-4 flex items-center gap-4 border border-yellow-400/20">
            <div class="text-center">
              <div class="text-3xl font-black text-yellow-400 tabular-nums leading-none">{{ pad(countdown.days) }}</div>
              <div class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Jours</div>
            </div>
            <div class="text-yellow-400/40 text-2xl font-thin">:</div>
            <div class="text-center">
              <div class="text-3xl font-black text-white tabular-nums leading-none">{{ pad(countdown.hours) }}</div>
              <div class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Heures</div>
            </div>
            <div class="text-yellow-400/40 text-2xl font-thin">:</div>
            <div class="text-center">
              <div class="text-3xl font-black text-white tabular-nums leading-none">{{ pad(countdown.mins) }}</div>
              <div class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Min</div>
            </div>
          </div>

          <!-- Opening match chip -->
          <div class="glass rounded-2xl px-5 py-4 flex items-center gap-3 border border-white/10 flex-1 min-w-fit">
            <div class="flex items-center gap-2">
              <img :src="flagImg('MEX')" alt="MEX" class="w-10 h-7 object-cover rounded-lg shadow" />
              <span class="text-white font-bold text-sm hidden sm:block">Mexique</span>
            </div>
            <div class="text-center px-2">
              <div class="text-yellow-400 font-black text-xs uppercase tracking-wider">Match<br>d'ouverture</div>
              <div class="text-white font-black text-lg mt-0.5">VS</div>
              <div class="text-gray-500 text-[10px]">11 Juin 2026</div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-white font-bold text-sm hidden sm:block">Afr. du Sud</span>
              <img :src="flagImg('RSA')" alt="RSA" class="w-10 h-7 object-cover rounded-lg shadow" />
            </div>
          </div>

        </div>
      </div>

      <!-- Bottom stats bar -->
      <div class="relative z-10 border-t border-white/5 grid grid-cols-2 md:grid-cols-4">
        <div v-for="(s, i) in quickStats" :key="s.label"
             class="flex flex-col items-center justify-center py-4 gap-1"
             :class="i < 3 ? 'border-r border-white/5' : ''">
          <span class="text-2xl font-black text-yellow-400">{{ s.value }}</span>
          <span class="text-[11px] text-gray-500 uppercase tracking-wider">{{ s.label }}</span>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════════
         LIVE MATCHES
    ══════════════════════════════════════════════ -->
    <section v-if="store.liveCount > 0">
      <div class="flex items-center gap-3 mb-5">
        <div class="flex items-center gap-2 bg-red-500/10 border border-red-500/30 rounded-full px-3 py-1">
          <span class="w-2 h-2 bg-red-500 rounded-full animate-ping absolute"></span>
          <span class="w-2 h-2 bg-red-500 rounded-full relative"></span>
          <span class="text-red-400 text-xs font-black uppercase tracking-wider">En Direct</span>
          <span class="text-red-400 font-black text-sm">{{ store.liveCount }}</span>
        </div>
      </div>
      <div class="grid md:grid-cols-2 gap-4">
        <MatchCard v-for="m in store.liveMatches" :key="m.id" :match="m"/>
      </div>
    </section>

    <!-- ══════════════════════════════════════════════
         TODAY + UPCOMING MATCHES
    ══════════════════════════════════════════════ -->
    <div class="grid lg:grid-cols-2 gap-6">

      <!-- Today -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-black text-white flex items-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-yellow-400/10 flex items-center justify-center">
              <i class="fas fa-calendar-day text-yellow-400 text-sm"></i>
            </span>
            Matchs du Jour
          </h2>
          <span class="text-gray-600 text-xs capitalize">{{ todayLabel }}</span>
        </div>
        <div v-if="store.todayMatches.length" class="space-y-3">
          <MatchCard v-for="m in store.todayMatches" :key="m.id" :match="m"/>
        </div>
        <div v-else class="rounded-2xl border border-white/5 bg-white/[0.02] p-10 text-center">
          <i class="fas fa-moon text-3xl text-gray-700 mb-3 block"></i>
          <p class="text-gray-600 text-sm">Aucun match aujourd'hui</p>
        </div>
      </div>

      <!-- Upcoming -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-black text-white flex items-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-blue-400/10 flex items-center justify-center">
              <i class="fas fa-clock text-blue-400 text-sm"></i>
            </span>
            Prochains Matchs
          </h2>
          <RouterLink to="/calendrier" class="text-yellow-400 text-xs hover:underline">Voir tout →</RouterLink>
        </div>
        <div class="space-y-3">
          <MatchCard v-for="m in store.upcomingMatches.slice(0,5)" :key="m.id" :match="m"/>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════
         NEWS
    ══════════════════════════════════════════════ -->
    <section>
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-black text-white flex items-center gap-2">
          <span class="w-8 h-8 rounded-xl bg-purple-400/10 flex items-center justify-center">
            <i class="fas fa-newspaper text-purple-400 text-sm"></i>
          </span>
          Dernières Actualités
        </h2>
        <RouterLink to="/actualites"
                    class="flex items-center gap-1.5 text-xs font-semibold text-yellow-400 hover:text-yellow-300 transition-colors">
          Voir toutes <i class="fas fa-arrow-right text-[10px]"></i>
        </RouterLink>
      </div>

      <NewsCarousel v-if="news.length" :articles="news.slice(0,5)" class="mb-6" />

      <div v-else-if="newsLoading" class="grid sm:grid-cols-3 gap-4">
        <div v-for="n in 3" :key="n" class="rounded-2xl overflow-hidden border border-white/5 animate-pulse">
          <div class="h-40 bg-white/5"></div>
          <div class="p-4 space-y-2">
            <div class="h-3 bg-white/5 rounded w-3/4"></div>
            <div class="h-3 bg-white/5 rounded w-full"></div>
          </div>
        </div>
      </div>

      <!-- News grid -->
      <div v-if="news.length" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
        <RouterLink v-for="article in news.slice(5, 9)" :key="article.slug"
                    :to="`/actualites/${article.slug}`"
                    class="group rounded-2xl overflow-hidden border border-white/5 bg-white/[0.02]
                           hover:border-yellow-400/30 hover:-translate-y-1 transition-all duration-300">
          <div class="h-36 overflow-hidden relative">
            <img :src="article.image" :alt="article.title"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                 @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=400&q=80'" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          </div>
          <div class="p-3.5">
            <p class="text-white text-xs font-semibold leading-snug line-clamp-2 group-hover:text-yellow-400 transition-colors">
              {{ article.title }}
            </p>
            <p class="text-gray-600 text-[10px] mt-2 flex items-center gap-1">
              <i class="fas fa-circle-dot text-[6px]"></i>
              {{ article.source }}
            </p>
          </div>
        </RouterLink>
      </div>
    </section>

    <!-- ══════════════════════════════════════════════
         GROUPS PREVIEW
    ══════════════════════════════════════════════ -->
    <section>
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-black text-white flex items-center gap-2">
          <span class="w-8 h-8 rounded-xl bg-green-400/10 flex items-center justify-center">
            <i class="fas fa-layer-group text-green-400 text-sm"></i>
          </span>
          Aperçu des Groupes
        </h2>
        <RouterLink to="/groupes" class="flex items-center gap-1.5 text-xs font-semibold text-yellow-400 hover:text-yellow-300 transition-colors">
          Voir tout <i class="fas fa-arrow-right text-[10px]"></i>
        </RouterLink>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3">
        <RouterLink v-for="(teams, letter) in store.groupsFromFixtures" :key="letter"
                    to="/groupes"
                    class="group rounded-2xl border border-white/5 bg-white/[0.02] p-4
                           hover:border-yellow-400/30 hover:bg-white/5 transition-all duration-200">
          <div class="flex items-center justify-between mb-3">
            <span class="text-yellow-400 font-black text-base">{{ letter }}</span>
            <span class="text-[10px] text-gray-600 font-semibold uppercase tracking-wider">Groupe</span>
          </div>
          <div class="space-y-2">
            <TeamLink v-for="code in teams" :key="code"
                      :code="code" cls="text-xs text-gray-400 group-hover:text-gray-300"
                      @click.stop />
          </div>
        </RouterLink>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAppStore } from '@/stores/app'
import MatchCard from '@/components/MatchCard.vue'
import TeamLink from '@/components/TeamLink.vue'
import NewsCarousel from '@/components/NewsCarousel.vue'
import { flagImg } from '@/utils/flag'

const store = useAppStore()

const todayLabel = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long', day: 'numeric', month: 'long',
})

const quickStats = [
  { value: 48,  label: 'Équipes' },
  { value: 104, label: 'Matchs' },
  { value: 16,  label: 'Stades' },
  { value: 3,   label: 'Pays hôtes' },
]

// Countdown
const countdown = ref({ days: 0, hours: 0, mins: 0 })
function pad(n) { return String(n).padStart(2, '0') }
function updateCd() {
  const diff = new Date('2026-06-11T20:00:00-05:00') - Date.now()
  if (diff <= 0) { countdown.value = { days: 0, hours: 0, mins: 0 }; return }
  countdown.value = {
    days:  Math.floor(diff / 86400000),
    hours: Math.floor((diff % 86400000) / 3600000),
    mins:  Math.floor((diff % 3600000) / 60000),
  }
}
let cdTimer
onMounted(() => { updateCd(); cdTimer = setInterval(updateCd, 30000) })
onUnmounted(() => clearInterval(cdTimer))

// News
const news        = ref([])
const newsLoading = ref(false)
async function loadNews() {
  newsLoading.value = true
  try {
    const data = await fetch('/api/news?cat=all&lang=fr').then(r => r.json())
    news.value = data.articles ?? []
  } catch {}
  finally { newsLoading.value = false }
}
onMounted(() => loadNews())
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>
