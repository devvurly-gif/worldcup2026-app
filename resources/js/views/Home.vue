<template>
  <div class="space-y-0">

    <!-- ═══════════════════════════════════════════════
         HERO — Full immersive stadium
    ═══════════════════════════════════════════════ -->
    <section class="relative rounded-3xl overflow-hidden" style="min-height:520px">

      <!-- Stadium background -->
      <img src="https://images.unsplash.com/photo-1540747913346-19212a4cf528?w=1400&q=85"
           alt="stadium" class="absolute inset-0 w-full h-full object-cover object-center" />

      <!-- Deep overlay gradient -->
      <div class="absolute inset-0"
           style="background:linear-gradient(160deg,rgba(2,6,23,.92) 0%,rgba(15,23,42,.80) 40%,rgba(30,27,75,.70) 70%,rgba(12,26,58,.85) 100%)"></div>

      <!-- Grid texture -->
      <div class="absolute inset-0 opacity-[0.03]"
           style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:60px 60px"></div>

      <!-- Gold top accent line -->
      <div class="absolute top-0 left-0 right-0 h-0.5"
           style="background:linear-gradient(90deg,transparent,#f59e0b 30%,#fbbf24 50%,#f59e0b 70%,transparent)"></div>

      <!-- Content -->
      <div class="relative z-10 p-6 md:p-10 lg:p-12 h-full flex flex-col justify-between" style="min-height:520px">

        <!-- Top bar -->
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-2.5">
            <div class="relative">
              <span class="w-2 h-2 rounded-full bg-yellow-400 block animate-ping absolute"></span>
              <span class="w-2 h-2 rounded-full bg-yellow-400 block relative"></span>
            </div>
            <span class="text-yellow-400 text-[11px] font-black uppercase tracking-[0.25em]">FIFA World Cup 2026™</span>
          </div>
          <div class="flex items-center gap-1.5 glass rounded-full px-3 py-1.5">
            <img src="https://flagcdn.com/w40/us.png" alt="USA" class="w-6 h-4 object-cover rounded" />
            <img src="https://flagcdn.com/w40/ca.png" alt="CAN" class="w-6 h-4 object-cover rounded" />
            <img src="https://flagcdn.com/w40/mx.png" alt="MEX" class="w-6 h-4 object-cover rounded" />
            <span class="text-gray-300 text-[11px] font-medium ml-1">USA · CAN · MEX</span>
          </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-col lg:flex-row items-end justify-between gap-8 mt-auto">

          <!-- Left: Title + info -->
          <div class="flex-1">
            <p class="text-gray-400 text-sm font-medium mb-2 tracking-wide">11 Juin — 17 Juillet 2026</p>
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black leading-none tracking-tighter text-white">
              COUPE<br>
              <span class="text-outline">DU MONDE</span><br>
              <span style="background:linear-gradient(90deg,#f59e0b,#fde68a,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">2026</span>
            </h1>
            <div class="flex flex-wrap items-center gap-3 mt-5">
              <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/15 rounded-full px-3 py-1 text-xs text-gray-300">
                <i class="fas fa-shield-halved text-yellow-400 text-[10px]"></i> 48 Équipes
              </span>
              <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/15 rounded-full px-3 py-1 text-xs text-gray-300">
                <i class="fas fa-futbol text-yellow-400 text-[10px]"></i> 104 Matchs
              </span>
              <span class="inline-flex items-center gap-1.5 bg-white/10 border border-white/15 rounded-full px-3 py-1 text-xs text-gray-300">
                <i class="fas fa-location-dot text-yellow-400 text-[10px]"></i> 16 Stades
              </span>
            </div>
          </div>

          <!-- Right: Countdown + Opening Match -->
          <div class="flex flex-col gap-3 w-full lg:w-auto lg:min-w-[300px]">

            <!-- Countdown box -->
            <div class="rounded-2xl p-4 border border-yellow-400/20"
                 style="background:rgba(245,158,11,.08);backdrop-filter:blur(12px)">
              <p class="text-yellow-400/70 text-[10px] font-black uppercase tracking-[0.2em] mb-3">
                {{ countdown.days > 0 ? 'Compte à rebours' : '🔴 Tournoi en cours' }}
              </p>
              <div class="flex items-center gap-2">
                <div class="flex-1 text-center">
                  <div class="text-4xl font-black text-yellow-400 tabular-nums leading-none">{{ pad(countdown.days) }}</div>
                  <div class="text-[9px] text-yellow-400/50 uppercase tracking-widest mt-1">Jours</div>
                </div>
                <div class="text-yellow-400/30 text-2xl">:</div>
                <div class="flex-1 text-center">
                  <div class="text-4xl font-black text-white tabular-nums leading-none">{{ pad(countdown.hours) }}</div>
                  <div class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Heures</div>
                </div>
                <div class="text-yellow-400/30 text-2xl">:</div>
                <div class="flex-1 text-center">
                  <div class="text-4xl font-black text-white tabular-nums leading-none">{{ pad(countdown.mins) }}</div>
                  <div class="text-[9px] text-gray-500 uppercase tracking-widest mt-1">Min</div>
                </div>
              </div>
            </div>

            <!-- Opening match -->
            <div class="rounded-2xl p-4 border border-white/10"
                 style="background:rgba(255,255,255,.05);backdrop-filter:blur(12px)">
              <p class="text-gray-500 text-[10px] font-black uppercase tracking-[0.2em] mb-3">Match d'ouverture</p>
              <div class="flex items-center justify-between gap-3">
                <div class="flex flex-col items-center gap-1.5 flex-1">
                  <img :src="flagImg('MEX')" alt="MEX" class="w-12 h-8 object-cover rounded-lg shadow-lg" />
                  <span class="text-white font-bold text-xs">Mexique</span>
                </div>
                <div class="text-center">
                  <div class="text-yellow-400 font-black text-xl">VS</div>
                  <div class="text-gray-600 text-[10px] mt-0.5">11·06·2026</div>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                  <img :src="flagImg('RSA')" alt="RSA" class="w-12 h-8 object-cover rounded-lg shadow-lg" />
                  <span class="text-white font-bold text-xs">Afr. du Sud</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════
         LIVE BANNER
    ═══════════════════════════════════════════════ -->
    <div v-if="store.liveCount > 0" class="mt-4">
      <div class="rounded-2xl border border-red-500/30 p-4"
           style="background:linear-gradient(135deg,rgba(239,68,68,.08),rgba(239,68,68,.03))">
        <div class="flex items-center gap-3 mb-4">
          <div class="relative flex items-center justify-center w-8 h-8">
            <span class="absolute w-full h-full rounded-full bg-red-500/30 animate-ping"></span>
            <span class="w-3 h-3 rounded-full bg-red-500 relative z-10"></span>
          </div>
          <span class="text-red-400 font-black uppercase tracking-wider text-sm">En Direct</span>
          <span class="bg-red-500 text-white text-xs font-black px-2 py-0.5 rounded-full">{{ store.liveCount }}</span>
        </div>
        <div class="grid md:grid-cols-2 gap-3">
          <MatchCard v-for="m in store.liveMatches" :key="m.id" :match="m"/>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         MATCHES — TODAY + UPCOMING
    ═══════════════════════════════════════════════ -->
    <div class="mt-8 grid lg:grid-cols-2 gap-5">

      <!-- Today -->
      <div class="rounded-2xl border border-white/5 overflow-hidden"
           style="background:rgba(255,255,255,.02)">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                 style="background:rgba(245,158,11,.15)">
              <i class="fas fa-calendar-day text-yellow-400 text-xs"></i>
            </div>
            <span class="font-black text-white text-sm">Matchs du Jour</span>
          </div>
          <span class="text-gray-600 text-xs capitalize">{{ todayLabel }}</span>
        </div>
        <div class="p-4">
          <div v-if="store.todayMatches.length" class="space-y-2.5">
            <MatchCard v-for="m in store.todayMatches" :key="m.id" :match="m"/>
          </div>
          <div v-else class="py-10 text-center">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-3"
                 style="background:rgba(255,255,255,.04)">
              <i class="fas fa-moon text-gray-600 text-xl"></i>
            </div>
            <p class="text-gray-600 text-sm">Aucun match aujourd'hui</p>
          </div>
        </div>
      </div>

      <!-- Upcoming -->
      <div class="rounded-2xl border border-white/5 overflow-hidden"
           style="background:rgba(255,255,255,.02)">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                 style="background:rgba(99,102,241,.15)">
              <i class="fas fa-clock text-indigo-400 text-xs"></i>
            </div>
            <span class="font-black text-white text-sm">Prochains Matchs</span>
          </div>
          <RouterLink to="/calendrier"
                      class="text-yellow-400 text-xs font-semibold hover:text-yellow-300 transition-colors flex items-center gap-1">
            Tout voir <i class="fas fa-chevron-right text-[9px]"></i>
          </RouterLink>
        </div>
        <div class="p-4 space-y-2.5">
          <MatchCard v-for="m in store.upcomingMatches.slice(0,5)" :key="m.id" :match="m"/>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         NEWS — Carousel + Grid
    ═══════════════════════════════════════════════ -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
          <div class="w-1 h-6 rounded-full" style="background:linear-gradient(180deg,#f59e0b,#fde68a)"></div>
          <h2 class="font-black text-white text-lg tracking-tight">Dernières Actualités</h2>
        </div>
        <RouterLink to="/actualites"
                    class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-yellow-400 transition-colors font-medium border border-white/10 rounded-full px-3 py-1.5 hover:border-yellow-400/30">
          Voir toutes <i class="fas fa-arrow-right text-[9px]"></i>
        </RouterLink>
      </div>

      <NewsCarousel v-if="news.length" :articles="news.slice(0,5)" class="mb-5" />

      <div v-else-if="newsLoading" class="grid sm:grid-cols-3 gap-4 mb-5">
        <div v-for="n in 3" :key="n" class="rounded-2xl overflow-hidden border border-white/5 animate-pulse">
          <div class="h-40 bg-white/5"></div>
          <div class="p-4 space-y-2">
            <div class="h-3 bg-white/5 rounded w-3/4"></div>
            <div class="h-3 bg-white/5 rounded"></div>
          </div>
        </div>
      </div>

      <!-- 4 article cards -->
      <div v-if="news.length" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <RouterLink v-for="article in news.slice(5, 9)" :key="article.slug"
                    :to="`/actualites/${article.slug}`"
                    class="group rounded-2xl overflow-hidden border border-white/5 hover:border-yellow-400/25
                           transition-all duration-300 hover:-translate-y-1"
                    style="background:rgba(255,255,255,.02)">
          <div class="relative h-36 overflow-hidden">
            <img :src="article.image" :alt="article.title"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                 @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=400&q=80'" />
            <div class="absolute inset-0" style="background:linear-gradient(to top,rgba(0,0,0,.7) 0%,transparent 60%)"></div>
            <div class="absolute bottom-2 left-2">
              <span class="text-[9px] text-gray-400 font-medium bg-black/50 rounded px-1.5 py-0.5">{{ article.source }}</span>
            </div>
          </div>
          <div class="p-3">
            <p class="text-white text-xs font-semibold leading-snug line-clamp-2 group-hover:text-yellow-400 transition-colors">
              {{ article.title }}
            </p>
          </div>
        </RouterLink>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         GROUPS — Compact premium grid
    ═══════════════════════════════════════════════ -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
          <div class="w-1 h-6 rounded-full" style="background:linear-gradient(180deg,#22c55e,#86efac)"></div>
          <h2 class="font-black text-white text-lg tracking-tight">Aperçu des Groupes</h2>
        </div>
        <RouterLink to="/groupes"
                    class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-yellow-400 transition-colors font-medium border border-white/10 rounded-full px-3 py-1.5 hover:border-yellow-400/30">
          Voir tout <i class="fas fa-arrow-right text-[9px]"></i>
        </RouterLink>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3">
        <RouterLink v-for="(teams, letter) in store.groupsFromFixtures" :key="letter"
                    to="/groupes"
                    class="group rounded-2xl border border-white/5 p-4 hover:border-yellow-400/30
                           transition-all duration-200 hover:-translate-y-0.5"
                    style="background:rgba(255,255,255,.02)">
          <div class="flex items-center justify-between mb-3">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm text-yellow-400"
                 style="background:rgba(245,158,11,.12)">
              {{ letter }}
            </div>
            <i class="fas fa-chevron-right text-[9px] text-gray-700 group-hover:text-yellow-400 transition-colors"></i>
          </div>
          <div class="space-y-1.5">
            <TeamLink v-for="code in teams" :key="code"
                      :code="code" cls="text-xs text-gray-500 group-hover:text-gray-300 transition-colors"
                      @click.stop />
          </div>
        </RouterLink>
      </div>
    </div>

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
.text-outline {
  -webkit-text-stroke: 2px rgba(255,255,255,.15);
  color: transparent;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>
