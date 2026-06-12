<template>
  <div>

    <!-- Back -->
    <button @click="$router.back()"
            class="btn-ghost text-sm mb-6 inline-flex items-center gap-2">
      <i class="fas fa-arrow-left"></i> Retour aux actualités
    </button>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-24">
      <i class="fas fa-circle-notch animate-spin text-yellow-400 text-3xl"></i>
    </div>

    <div v-else-if="article" class="flex flex-col xl:flex-row gap-8">

      <!-- ── ARTICLE ─────────────────────────────────────────── -->
      <article class="flex-1 min-w-0">

        <!-- Hero image -->
        <div class="rounded-2xl overflow-hidden mb-6 relative" style="max-height:480px">
          <img :src="article.image" :alt="article.title"
               class="w-full h-full object-cover"
               style="max-height:480px"
               @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=1200&q=80'" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

          <!-- Category badge -->
          <span class="absolute top-4 left-4 bg-yellow-500 text-black text-xs font-black
                       px-3 py-1 rounded-full uppercase tracking-wider">
            Actualités
          </span>
        </div>

        <!-- Meta -->
        <div class="flex flex-wrap items-center gap-3 mb-4 text-sm">
          <span class="text-gray-400 flex items-center gap-1.5">
            <i class="fas fa-newspaper text-yellow-400 text-xs"></i>
            {{ article.source }}
          </span>
          <span class="text-gray-600">·</span>
          <span class="text-gray-400 flex items-center gap-1.5">
            <i class="fas fa-calendar text-xs"></i>
            {{ formatDate(article.published_at) }}
          </span>
          <a :href="article.url" target="_blank" rel="noopener"
             class="ml-auto text-yellow-400 hover:text-yellow-300 text-xs flex items-center gap-1 transition-colors">
            <i class="fas fa-external-link text-[10px]"></i> Source originale
          </a>
        </div>

        <!-- Title -->
        <h1 class="text-white text-2xl md:text-4xl font-black leading-tight mb-5">
          {{ article.title }}
        </h1>

        <!-- Ad after title -->
        <GoogleAd size="banner" slot="5678901234" class="mb-6" />

        <!-- Content -->
        <div class="prose-wc text-gray-300 text-base leading-relaxed space-y-4 mb-8">
          <p class="text-lg text-gray-200 font-medium leading-relaxed">{{ article.description }}</p>
          <p v-if="article.content && article.content !== article.description">
            {{ article.content }}
          </p>
          <p class="text-gray-400 text-sm italic">
            Pour lire l'article complet, consultez la
            <a :href="article.url" target="_blank" rel="noopener"
               class="text-yellow-400 hover:underline">source originale</a>.
          </p>
        </div>

        <!-- Ad mid-content -->
        <GoogleAd size="rectangle" slot="6789012345" class="mb-8" />

        <!-- Share -->
        <div class="card rounded-2xl p-5 flex flex-wrap items-center gap-3">
          <span class="text-white font-bold text-sm flex items-center gap-2">
            <i class="fas fa-share-nodes text-yellow-400"></i> Partager
          </span>
          <a :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(article.title)}&url=${encodeURIComponent(article.url)}`"
             target="_blank" rel="noopener"
             class="flex items-center gap-2 bg-sky-500/10 hover:bg-sky-500/20 text-sky-400
                    border border-sky-500/20 rounded-xl px-3 py-2 text-xs font-bold transition-colors">
            <i class="fab fa-x-twitter"></i> Twitter/X
          </a>
          <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(article.url)}`"
             target="_blank" rel="noopener"
             class="flex items-center gap-2 bg-blue-600/10 hover:bg-blue-600/20 text-blue-400
                    border border-blue-600/20 rounded-xl px-3 py-2 text-xs font-bold transition-colors">
            <i class="fab fa-facebook"></i> Facebook
          </a>
          <button @click="copyLink"
                  class="flex items-center gap-2 glass hover:bg-white/10 text-gray-300
                         rounded-xl px-3 py-2 text-xs font-bold transition-colors">
            <i class="fas fa-link"></i> {{ copied ? 'Copié !' : 'Copier le lien' }}
          </button>
        </div>

        <!-- Related articles -->
        <div v-if="related.length" class="mt-10">
          <h2 class="text-white font-black text-xl mb-5 flex items-center gap-2">
            <i class="fas fa-newspaper text-yellow-400"></i> Articles similaires
          </h2>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <RouterLink v-for="a in related" :key="a.slug"
                        :to="`/actualites/${a.slug}`"
                        class="card rounded-xl overflow-hidden group hover:border-yellow-500/30
                               transition-all hover:-translate-y-1">
              <div class="h-36 overflow-hidden">
                <img :src="a.image" :alt="a.title"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                     @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=400&q=80'" />
              </div>
              <div class="p-3">
                <p class="text-white text-xs font-semibold leading-snug line-clamp-2 group-hover:text-yellow-400 transition-colors">
                  {{ a.title }}
                </p>
                <p class="text-gray-600 text-[11px] mt-1.5">{{ timeAgo(a.published_at) }}</p>
              </div>
            </RouterLink>
          </div>
        </div>

      </article>

      <!-- ── SIDEBAR ─────────────────────────────────────────── -->
      <aside class="xl:w-72 shrink-0 space-y-6">

        <!-- Sticky ads + widgets -->
        <div class="xl:sticky xl:top-24 space-y-6">

          <!-- Ad -->
          <GoogleAd size="rectangle" slot="7890123456" />

          <!-- Latest news list -->
          <div class="card rounded-2xl p-5">
            <h3 class="text-white font-black text-sm mb-4 flex items-center gap-2">
              <i class="fas fa-clock text-yellow-400"></i> Dernières actualités
            </h3>
            <div class="space-y-4">
              <RouterLink v-for="a in latestNews" :key="a.slug"
                          :to="`/actualites/${a.slug}`"
                          class="flex gap-3 group hover:bg-white/5 rounded-xl p-2 -mx-2 transition-colors">
                <div class="w-16 h-14 rounded-lg overflow-hidden shrink-0">
                  <img :src="a.image" :alt="a.title"
                       class="w-full h-full object-cover"
                       @error="$event.target.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=100&q=60'" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-gray-300 text-xs leading-snug group-hover:text-white transition-colors line-clamp-2">
                    {{ a.title }}
                  </p>
                  <p class="text-gray-600 text-[11px] mt-1">{{ timeAgo(a.published_at) }}</p>
                </div>
              </RouterLink>
            </div>
          </div>

          <!-- Ad skyscraper -->
          <GoogleAd size="skyscraper" slot="8901234567" />

        </div>
      </aside>
    </div>

    <!-- 404 -->
    <div v-else class="card p-12 text-center text-gray-600">
      <i class="fas fa-circle-xmark text-4xl mb-4 block"></i>
      Article introuvable.
      <RouterLink to="/actualites" class="btn-primary inline-block mt-4">
        ← Retour aux actualités
      </RouterLink>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import GoogleAd from '@/components/GoogleAd.vue'

const route   = useRoute()
const article = ref(null)
const allNews = ref([])
const loading = ref(true)
const copied  = ref(false)

const related    = computed(() => allNews.value.filter(a => a.slug !== article.value?.slug).slice(0, 3))
const latestNews = computed(() => allNews.value.filter(a => a.slug !== article.value?.slug).slice(0, 5))

async function load() {
  loading.value = true
  article.value = null
  try {
    // Load all news for sidebar + related
    const [artRes, allRes] = await Promise.all([
      fetch(`/api/news/${route.params.slug}`),
      fetch('/api/news?cat=all&lang=fr'),
    ])
    if (artRes.ok) {
      article.value = await artRes.json()
    }
    if (allRes.ok) {
      const data = await allRes.json()
      allNews.value = data.articles ?? []
    }
    // If article not found via slug API, try finding it in all news
    if (!article.value && allNews.value.length) {
      article.value = allNews.value.find(a => a.slug === route.params.slug) ?? null
    }
  } catch {}
  finally { loading.value = false }
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' })
}

function timeAgo(dateStr) {
  if (!dateStr) return ''
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60)  return `il y a ${mins}min`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24)   return `il y a ${hrs}h`
  return `il y a ${Math.floor(hrs / 24)}j`
}

async function copyLink() {
  await navigator.clipboard.writeText(window.location.href).catch(() => {})
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}

watch(() => route.params.slug, load)
onMounted(load)
</script>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
