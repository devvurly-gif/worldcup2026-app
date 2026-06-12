<template>
  <div class="space-y-8">

    <!-- ── PAGE TITLE ──────────────────────────────────────── -->
    <div class="flex items-center justify-between">
      <h1 class="section-title mb-0">
        <i class="fas fa-newspaper text-yellow-400"></i>
        Actualités World Cup 2026
      </h1>
    </div>

    <!-- ── AD: BANNER TOP ──────────────────────────────────── -->
    <GoogleAd size="banner" slot="1234567890" />

    <!-- ── CAROUSEL ────────────────────────────────────────── -->
    <NewsCarousel :articles="featured" />

    <!-- ── CATEGORY TABS ───────────────────────────────────── -->
    <div class="flex gap-2 border-b border-white/10 overflow-x-auto pb-0">
      <button v-for="tab in tabs" :key="tab.cat"
              @click="selectCat(tab.cat)"
              class="px-4 py-2.5 text-sm font-semibold transition-colors border-b-2 -mb-px whitespace-nowrap"
              :class="activeCat === tab.cat
                ? 'text-yellow-400 border-yellow-400'
                : 'text-gray-500 border-transparent hover:text-gray-300'">
        <i :class="tab.icon" class="mr-1.5 text-xs"></i>{{ tab.label }}
      </button>
    </div>

    <!-- ── MAIN CONTENT + SIDEBAR ──────────────────────────── -->
    <div class="flex flex-col xl:flex-row gap-8">

      <!-- Articles grid -->
      <div class="flex-1 min-w-0">

        <!-- Loading -->
        <div v-if="loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <div v-for="n in 6" :key="n" class="card rounded-2xl overflow-hidden animate-pulse">
            <div class="h-48 bg-white/5"></div>
            <div class="p-4 space-y-3">
              <div class="h-4 bg-white/5 rounded w-3/4"></div>
              <div class="h-3 bg-white/5 rounded w-full"></div>
              <div class="h-3 bg-white/5 rounded w-2/3"></div>
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="card p-10 text-center text-red-400">
          <i class="fas fa-circle-exclamation text-3xl block mb-3"></i>
          {{ error }}
          <button @click="load" class="btn-admin-primary mt-4 mx-auto text-sm px-6">Réessayer</button>
        </div>

        <!-- Empty -->
        <div v-else-if="!articles.length" class="card p-10 text-center text-gray-600">
          <i class="fas fa-newspaper text-3xl block mb-3"></i>
          Aucune actualité disponible pour le moment.
        </div>

        <!-- Grid -->
        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <template v-for="(article, i) in articles" :key="article.slug">

            <!-- AD after 6th article -->
            <div v-if="i === 5" class="sm:col-span-2 lg:col-span-3">
              <GoogleAd size="banner" slot="2345678901" />
            </div>

            <!-- Article card -->
            <RouterLink :to="`/actualites/${article.slug}`"
                        class="card rounded-2xl overflow-hidden cursor-pointer group
                               hover:border-yellow-500/30 transition-all hover:-translate-y-1">
              <!-- Thumbnail -->
              <div class="h-48 overflow-hidden relative bg-navy-mid">
                <img :src="article.image" :alt="article.title"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=600&q=80'" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <span class="absolute bottom-3 left-3 bg-yellow-500/90 text-black text-[10px] font-black
                             px-2 py-0.5 rounded-full uppercase tracking-wider">
                  Actualités
                </span>
              </div>
              <!-- Info -->
              <div class="p-4">
                <h3 class="text-white font-bold text-sm leading-snug line-clamp-2 group-hover:text-yellow-400 transition-colors">
                  {{ article.title }}
                </h3>
                <p class="text-gray-500 text-xs mt-2 line-clamp-2">{{ article.description }}</p>
                <div class="flex items-center justify-between mt-3">
                  <span class="text-gray-600 text-[11px] flex items-center gap-1">
                    <i class="fas fa-newspaper text-[9px]"></i>{{ article.source }}
                  </span>
                  <span class="text-gray-600 text-[11px] flex items-center gap-1">
                    <i class="fas fa-clock text-[9px]"></i>{{ timeAgo(article.published_at) }}
                  </span>
                </div>
              </div>
            </RouterLink>

          </template>
        </div>

        <!-- Load more -->
        <div v-if="articles.length >= 10 && !loading" class="text-center mt-8">
          <button @click="loadMore"
                  :disabled="loadingMore"
                  class="btn-admin-ghost px-8 py-3 text-sm">
            <i class="fas fa-circle-notch mr-2" :class="loadingMore ? 'animate-spin' : 'hidden'"></i>
            Charger plus d'articles
          </button>
        </div>

      </div>

      <!-- ── SIDEBAR ─────────────────────────────────────── -->
      <aside class="xl:w-72 shrink-0 space-y-6">

        <!-- Ad skyscraper -->
        <GoogleAd size="skyscraper" slot="3456789012" />

        <!-- Trending -->
        <div class="card rounded-2xl p-5">
          <h3 class="text-white font-black text-sm mb-4 flex items-center gap-2">
            <i class="fas fa-fire text-orange-400"></i> Tendances
          </h3>
          <div class="space-y-3">
            <RouterLink v-for="(a, i) in trending" :key="a.slug"
                        :to="`/actualites/${a.slug}`"
                        class="flex items-start gap-3 group hover:bg-white/5 rounded-xl p-2 -mx-2 transition-colors">
              <span class="text-2xl font-black text-yellow-400/30 shrink-0 leading-none mt-0.5">{{ i + 1 }}</span>
              <p class="text-gray-300 text-xs leading-snug group-hover:text-white transition-colors line-clamp-3">
                {{ a.title }}
              </p>
            </RouterLink>
          </div>
        </div>

        <!-- Ad rectangle -->
        <GoogleAd size="rectangle" slot="4567890123" />

        <!-- Quick links -->
        <div class="card rounded-2xl p-5">
          <h3 class="text-white font-black text-sm mb-4 flex items-center gap-2">
            <i class="fas fa-link text-yellow-400"></i> Navigation
          </h3>
          <div class="space-y-2">
            <RouterLink v-for="link in quickLinks" :key="link.to" :to="link.to"
                        class="flex items-center gap-2.5 text-gray-400 hover:text-yellow-400
                               transition-colors text-sm py-1.5">
              <i :class="link.icon" class="text-xs w-4 text-center"></i>
              {{ link.label }}
            </RouterLink>
          </div>
        </div>

      </aside>
    </div>

  </div>
</template>

<script setup>
import { useSeoMeta } from '@/composables/useSeoMeta'
useSeoMeta({ title: 'Actualités Mondial 2026', description: 'Toutes les actualités de la Coupe du Monde FIFA 2026.', path: '/actualites' })
import { ref, computed, onMounted } from 'vue'
import NewsCarousel from '@/components/NewsCarousel.vue'
import GoogleAd from '@/components/GoogleAd.vue'

const tabs = [
  { cat: 'all',     label: 'Toutes',   icon: 'fas fa-globe' },
  { cat: 'maroc',   label: 'Maroc',    icon: 'fas fa-star' },
  { cat: 'equipes', label: 'Équipes',  icon: 'fas fa-users' },
  { cat: 'scores',  label: 'Résultats', icon: 'fas fa-futbol' },
]

const quickLinks = [
  { to: '/',           label: 'Accueil',    icon: 'fas fa-home' },
  { to: '/groupes',    label: 'Groupes',    icon: 'fas fa-layer-group' },
  { to: '/calendrier', label: 'Calendrier', icon: 'fas fa-calendar' },
  { to: '/joueurs',    label: 'Joueurs',    icon: 'fas fa-users' },
  { to: '/bracket',    label: 'Bracket',    icon: 'fas fa-trophy' },
]

const activeCat   = ref('all')
const articles    = ref([])
const loading     = ref(false)
const loadingMore = ref(false)
const error       = ref('')

const featured  = computed(() => articles.value.slice(0, 5))
const trending  = computed(() => articles.value.slice(0, 7))

async function load(cat = activeCat.value) {
  loading.value = true
  error.value   = ''
  try {
    const res  = await fetch(`/api/news?cat=${cat}&lang=fr`)
    const data = await res.json()
    articles.value = data.articles ?? []
  } catch (e) {
    error.value = 'Impossible de charger les actualités. Vérifiez votre connexion.'
  } finally {
    loading.value = false
  }
}

function selectCat(cat) {
  activeCat.value = cat
  articles.value  = []
  load(cat)
}

async function loadMore() {
  loadingMore.value = true
  // Re-fetch with offset (future enhancement)
  await load()
  loadingMore.value = false
}

function timeAgo(dateStr) {
  if (!dateStr) return ''
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60)  return `il y a ${mins}min`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24)   return `il y a ${hrs}h`
  const days = Math.floor(hrs / 24)
  return `il y a ${days}j`
}

onMounted(() => load())
</script>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>

