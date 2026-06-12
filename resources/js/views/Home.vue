<template>
  <div class="space-y-8">

    <!-- Hero match du jour -->
    <section class="hero-bg rounded-3xl p-6 md:p-8 relative overflow-hidden border border-white/10">
      <div class="absolute inset-0 opacity-5"
           style="background:url('https://upload.wikimedia.org/wikipedia/commons/thumb/4/43/FIFA_World_Cup_2026_logo.svg/800px-FIFA_World_Cup_2026_logo.svg.png') center/contain no-repeat"></div>
      <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
          <p class="text-yellow-400 text-xs font-black uppercase tracking-widest mb-2">⚽ Match d'ouverture</p>
          <h2 class="text-3xl md:text-5xl font-black text-white flex flex-wrap items-center gap-3">
            <img :src="flagImg('MEX')" alt="MEX" class="w-12 h-8 object-cover rounded-lg shadow" />
            Mexique
            <span class="text-yellow-400 mx-1">VS</span>
            <img :src="flagImg('RSA')" alt="RSA" class="w-12 h-8 object-cover rounded-lg shadow" />
            Afrique du Sud
          </h2>
          <p class="text-gray-300 mt-2 text-sm">
            <i class="fas fa-calendar mr-1"></i> 11 Juin 2026 · 20h00 ET
            &nbsp;|&nbsp;
            <i class="fas fa-map-marker-alt mr-1"></i> Estadio Azteca, Mexico City
          </p>
        </div>
        <div class="glass rounded-2xl p-5 text-center min-w-[130px]">
          <p class="text-xs text-gray-400 mb-1">Score</p>
          <p class="text-5xl font-black text-white">– : –</p>
          <span class="text-xs text-yellow-400 font-semibold mt-1 block">À venir</span>
        </div>
      </div>
    </section>

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="s in quickStats" :key="s.label"
           class="card p-5 text-center glass-hover">
        <div class="text-3xl mb-2">{{ s.icon }}</div>
        <div class="text-2xl font-black text-yellow-400">{{ s.value }}</div>
        <div class="text-xs text-gray-500 mt-0.5">{{ s.label }}</div>
      </div>
    </div>

    <!-- Matchs LIVE -->
    <section v-if="store.liveCount > 0">
      <h2 class="section-title">
        <span class="w-3 h-3 bg-red-500 rounded-full animate-blink"></span>
        Matchs EN DIRECT ({{ store.liveCount }})
      </h2>
      <div class="grid md:grid-cols-2 gap-4">
        <MatchCard v-for="m in store.liveMatches" :key="m.id" :match="m"/>
      </div>
    </section>

    <!-- Matchs aujourd'hui / à venir -->
    <div class="grid md:grid-cols-2 gap-8">
      <!-- Aujourd'hui -->
      <section>
        <h2 class="section-title">
          <i class="fas fa-calendar-day text-yellow-400"></i>
          Matchs du Jour
          <span class="text-sm font-normal text-gray-500 ml-auto">{{ todayLabel }}</span>
        </h2>
        <div v-if="store.todayMatches.length" class="space-y-3">
          <MatchCard v-for="m in store.todayMatches" :key="m.id" :match="m"/>
        </div>
        <div v-else class="card p-8 text-center text-gray-600">
          <i class="fas fa-moon text-3xl mb-3 block"></i>
          Aucun match aujourd'hui
        </div>
      </section>

      <!-- Prochains matchs -->
      <section>
        <h2 class="section-title">
          <i class="fas fa-clock text-yellow-400"></i>
          Prochains Matchs
        </h2>
        <div class="space-y-3">
          <MatchCard v-for="m in store.upcomingMatches.slice(0,5)" :key="m.id" :match="m"/>
        </div>
      </section>
    </div>

    <!-- ── NEWS SECTION ──────────────────────────────────── -->
    <section>
      <h2 class="section-title">
        <i class="fas fa-newspaper text-yellow-400"></i>
        Dernières Actualités
        <RouterLink to="/actualites" class="ml-auto text-sm font-normal text-yellow-400 hover:underline">
          Voir toutes →
        </RouterLink>
      </h2>

      <!-- Mini carousel (top 3) -->
      <NewsCarousel v-if="news.length" :articles="news" class="mb-6" />

      <!-- Loading skeleton -->
      <div v-else-if="newsLoading" class="grid sm:grid-cols-3 gap-4">
        <div v-for="n in 3" :key="n" class="card rounded-2xl overflow-hidden animate-pulse">
          <div class="h-40 bg-white/5"></div>
          <div class="p-4 space-y-2">
            <div class="h-4 bg-white/5 rounded w-3/4"></div>
            <div class="h-3 bg-white/5 rounded w-full"></div>
          </div>
        </div>
      </div>

      <!-- Recent articles row -->
      <div v-if="news.length" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
        <RouterLink v-for="article in news.slice(5, 9)" :key="article.slug"
                    :to="`/actualites/${article.slug}`"
                    class="card rounded-xl overflow-hidden group hover:border-yellow-500/30 transition-all hover:-translate-y-1">
          <div class="h-32 overflow-hidden">
            <img :src="article.image" :alt="article.title"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=400&q=80'" />
          </div>
          <div class="p-3">
            <p class="text-white text-xs font-semibold leading-snug line-clamp-2 group-hover:text-yellow-400 transition-colors">
              {{ article.title }}
            </p>
            <p class="text-gray-600 text-[11px] mt-1.5">{{ article.source }}</p>
          </div>
        </RouterLink>
      </div>
    </section>

    <!-- Aperçu groupes -->
    <section>
      <h2 class="section-title">
        <i class="fas fa-layer-group text-yellow-400"></i>
        Aperçu des Groupes
        <RouterLink to="/groupes" class="ml-auto text-sm font-normal text-yellow-400 hover:underline">
          Voir tout →
        </RouterLink>
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
        <RouterLink v-for="(teams, letter) in store.groupsFromFixtures" :key="letter"
                    to="/groupes"
                    class="card p-4 glass-hover">
          <div class="text-yellow-400 font-black text-lg mb-3">GROUPE {{ letter }}</div>
          <div class="space-y-1.5">
            <TeamLink v-for="code in teams" :key="code"
                      :code="code" cls="text-sm text-gray-300"
                      @click.stop />
          </div>
        </RouterLink>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { getTeam } from '@/services/api'
import MatchCard from '@/components/MatchCard.vue'
import TeamLink from '@/components/TeamLink.vue'
import NewsCarousel from '@/components/NewsCarousel.vue'
import { flagImg } from '@/utils/flag'

const store = useAppStore()

const todayLabel = new Date().toLocaleDateString('fr-FR', {
  weekday:'long', day:'numeric', month:'long'
})

const quickStats = [
  { icon:'🌍', value:16,  label:'Villes-hôtes' },
  { icon:'⚽', value:48,  label:'Équipes' },
  { icon:'🗓️', value:104, label:'Matchs' },
  { icon:'🏟️', value:3,   label:'Pays hôtes' },
]

// News preview
const news        = ref([])
const newsLoading = ref(false)

async function loadNews() {
  newsLoading.value = true
  try {
    const res  = await fetch('/api/news?cat=all&lang=fr')
    const data = await res.json()
    news.value = data.articles ?? []
  } catch {}
  finally { newsLoading.value = false }
}

onMounted(() => loadNews())
</script>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
