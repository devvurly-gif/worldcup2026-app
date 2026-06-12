<template>
  <div class="relative rounded-2xl overflow-hidden bg-[#0d1117]" style="height:380px">

    <!-- Loading -->
    <div v-if="!articles.length"
         class="absolute inset-0 flex flex-col items-center justify-center gap-3">
      <i class="fas fa-circle-notch animate-spin text-yellow-400 text-3xl"></i>
      <p class="text-gray-600 text-sm">Chargement des actualités…</p>
    </div>

    <!-- Slides (one visible at a time) -->
    <template v-else>
      <div v-for="(article, i) in articles" :key="article.slug"
           class="absolute inset-0 transition-all duration-700 ease-in-out cursor-pointer"
           :class="i === current
             ? 'opacity-100 translate-x-0 z-10'
             : i < current ? 'opacity-0 -translate-x-full z-0' : 'opacity-0 translate-x-full z-0'"
           @click="goDetail(article)">

        <!-- Background image -->
        <img :src="article.image" :alt="article.title"
             class="absolute inset-0 w-full h-full object-cover"
             @error="onImgError($event, i)" />

        <!-- Dark gradient overlay -->
        <div class="absolute inset-0 pointer-events-none"
             style="background:linear-gradient(to top, rgba(0,0,0,.95) 0%, rgba(0,0,0,.55) 45%, rgba(0,0,0,.15) 100%)">
        </div>

        <!-- Content -->
        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 z-10">
          <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="bg-yellow-500 text-black text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
              Actualités
            </span>
            <span class="text-gray-400 text-xs">{{ formatDate(article.published_at) }}</span>
            <span v-if="article.source" class="text-gray-500 text-xs">· {{ article.source }}</span>
          </div>
          <h2 class="text-white text-xl md:text-3xl font-black leading-tight mb-2 line-clamp-2">
            {{ article.title }}
          </h2>
          <p class="text-gray-300 text-sm line-clamp-2 max-w-2xl hidden md:block">
            {{ article.description }}
          </p>
          <button class="mt-4 bg-yellow-500 hover:bg-yellow-400 text-black font-bold text-sm
                         px-5 py-2.5 rounded-xl transition-colors inline-flex items-center gap-2">
            Lire l'article <i class="fas fa-arrow-right text-xs"></i>
          </button>
        </div>
      </div>

      <!-- Left arrow -->
      <button @click.stop="prev"
              class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full
                     bg-black/60 hover:bg-black/90 text-white flex items-center justify-center
                     transition-colors border border-white/10 backdrop-blur-sm">
        <i class="fas fa-chevron-left text-sm"></i>
      </button>

      <!-- Right arrow -->
      <button @click.stop="next"
              class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full
                     bg-black/60 hover:bg-black/90 text-white flex items-center justify-center
                     transition-colors border border-white/10 backdrop-blur-sm">
        <i class="fas fa-chevron-right text-sm"></i>
      </button>

      <!-- Dots -->
      <div class="absolute bottom-5 right-6 flex gap-1.5 z-20">
        <button v-for="(_, i) in articles" :key="i"
                @click.stop="jumpTo(i)"
                class="rounded-full transition-all duration-300"
                :class="i === current
                  ? 'w-6 h-2 bg-yellow-400'
                  : 'w-2 h-2 bg-white/30 hover:bg-white/60'">
        </button>
      </div>

      <!-- Progress bar -->
      <div class="absolute bottom-0 left-0 h-0.5 bg-yellow-400 z-20"
           :style="{ width: barWidth + '%', transition: animating ? `width ${DURATION}ms linear` : 'none' }">
      </div>

      <!-- Slide counter -->
      <div class="absolute top-4 right-4 z-20 glass text-xs text-gray-400 px-2.5 py-1 rounded-full">
        {{ current + 1 }} / {{ articles.length }}
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  articles: { type: Array, default: () => [] },
})

const DURATION = 6000
const router   = useRouter()
const current  = ref(0)
const barWidth = ref(0)
const animating = ref(false)
let timer = null

const FALLBACK_IMAGES = [
  'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=1200&q=80',
  'https://images.unsplash.com/photo-1489944440615-453fc2b6a9a9?w=1200&q=80',
  'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1200&q=80',
]

function onImgError(e, i) {
  e.target.src = FALLBACK_IMAGES[i % FALLBACK_IMAGES.length]
}

function goDetail(article) {
  router.push(`/actualites/${article.slug}`)
}

function next() {
  current.value = (current.value + 1) % props.articles.length
  resetBar()
}

function prev() {
  current.value = (current.value - 1 + props.articles.length) % props.articles.length
  resetBar()
}

function jumpTo(i) {
  current.value = i
  resetBar()
}

function resetBar() {
  clearInterval(timer)
  barWidth.value  = 0
  animating.value = false
  // Small delay so CSS transition resets properly
  setTimeout(() => {
    animating.value = true
    barWidth.value  = 100
    timer = setInterval(() => {
      current.value  = (current.value + 1) % Math.max(props.articles.length, 1)
      barWidth.value = 0
      animating.value = false
      setTimeout(() => {
        animating.value = true
        barWidth.value  = 100
      }, 50)
    }, DURATION)
  }, 50)
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

watch(() => props.articles.length, (n) => {
  if (n > 0) { current.value = 0; resetBar() }
})

onMounted(() => {
  if (props.articles.length) resetBar()
})

onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
