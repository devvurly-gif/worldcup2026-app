<template>
  <div class="card bg-base-200 border border-error/30 overflow-hidden">

    <!-- Header -->
    <div class="bg-base-300 px-4 py-2.5 flex items-center justify-between border-b border-base-300">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-error animate-pulse"></span>
        <span class="text-sm font-black text-base-content uppercase tracking-widest">Diffusion Live</span>
      </div>
      <button @click="showPlayer = !showPlayer"
              class="btn btn-ghost btn-xs gap-1">
        <i class="fas text-xs" :class="showPlayer ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        {{ showPlayer ? 'Masquer' : 'Afficher' }}
      </button>
    </div>

    <!-- Player -->
    <div v-if="showPlayer" class="relative bg-black" style="aspect-ratio:16/9">

      <!-- Loader -->
      <div v-if="loadingStream"
           class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black z-10">
        <span class="loading loading-spinner loading-lg text-error"></span>
        <span class="text-xs text-base-content/40">Chargement du flux…</span>
      </div>

      <!-- Erreur -->
      <div v-if="streamError"
           class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black z-10">
        <i class="fas fa-triangle-exclamation text-3xl text-warning"></i>
        <p class="text-sm text-base-content/60">Flux indisponible</p>
        <button @click="initPlayer" class="btn btn-sm btn-ghost gap-2">
          <i class="fas fa-rotate-right"></i> Réessayer
        </button>
      </div>

      <video ref="videoEl"
             class="w-full h-full object-contain"
             controls
             autoplay
             playsinline
             :muted="muted"
             @loadeddata="loadingStream = false"
             @error="onError">
      </video>

      <!-- Mute toggle -->
      <button @click="muted = !muted"
              class="absolute top-2 right-2 btn btn-circle btn-xs btn-ghost bg-black/60 text-white z-20">
        <i class="fas text-xs" :class="muted ? 'fa-volume-xmark' : 'fa-volume-high'"></i>
      </button>
    </div>

    <!-- URL admin (si admin connecté) -->
    <div v-if="showPlayer && isAdmin" class="px-4 py-2 border-t border-base-300 flex gap-2 items-center">
      <input v-model="customUrl" type="text" placeholder="URL stream (.m3u8, mp4...)"
             class="input input-xs input-bordered flex-1 font-mono" />
      <button @click="setUrl(customUrl)" class="btn btn-xs btn-primary">OK</button>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import Hls from 'hls.js'

const props = defineProps({
  streamUrl: { type: String, default: '' },
  isAdmin:   { type: Boolean, default: false },
})

const videoEl      = ref(null)
const showPlayer   = ref(true)
const loadingStream = ref(true)
const streamError  = ref(false)
const muted        = ref(true)
const customUrl    = ref(props.streamUrl)

let hls = null

function initPlayer() {
  const url = customUrl.value || props.streamUrl
  if (!url || !videoEl.value) return

  streamError.value  = false
  loadingStream.value = true

  // Destroy previous instance
  if (hls) { hls.destroy(); hls = null }

  const video = videoEl.value

  if (Hls.isSupported() && (url.includes('.m3u8') || url.includes('hls'))) {
    hls = new Hls({ enableWorker: true, lowLatencyMode: true })
    hls.loadSource(url)
    hls.attachMedia(video)
    hls.on(Hls.Events.ERROR, (_, d) => {
      if (d.fatal) { streamError.value = true; loadingStream.value = false }
    })
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = url
  } else {
    video.src = url
  }
}

function setUrl(url) {
  customUrl.value = url
  initPlayer()
}

function onError() {
  streamError.value  = true
  loadingStream.value = false
}

onMounted(() => {
  if (props.streamUrl) initPlayer()
  else { loadingStream.value = false }
})

onUnmounted(() => { if (hls) hls.destroy() })

watch(() => props.streamUrl, url => {
  customUrl.value = url
  if (url) initPlayer()
})

watch(showPlayer, v => { if (v && customUrl.value) setTimeout(initPlayer, 100) })

defineExpose({ setUrl })
</script>
