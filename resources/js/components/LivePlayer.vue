<template>
  <div class="card bg-base-200 border border-error/30 overflow-hidden">

    <!-- Header -->
    <div class="bg-base-300 px-4 py-2.5 flex items-center justify-between border-b border-base-300">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-error animate-pulse"></span>
        <span class="text-sm font-black text-base-content uppercase tracking-widest">Diffusion Live</span>
      </div>
      <button @click="showPlayer = !showPlayer" class="btn btn-ghost btn-xs gap-1">
        <i class="fas text-xs" :class="showPlayer ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        {{ showPlayer ? 'Masquer' : 'Afficher' }}
      </button>
    </div>

    <!-- Pas d'URL : message -->
    <div v-if="!currentUrl && !isAdmin" class="px-4 py-6 text-center text-base-content/30 text-sm">
      <i class="fas fa-video-slash text-2xl block mb-2"></i>
      Stream non configuré
    </div>

    <!-- Admin : saisir URL même sans stream actif -->
    <div v-if="!currentUrl && isAdmin" class="px-4 py-4 flex gap-2 items-center">
      <input v-model="customUrl" type="text"
             placeholder="Coller l'URL du flux (.m3u8, mp4...)"
             class="input input-bordered input-sm flex-1 font-mono text-xs" />
      <button @click="applyUrl" class="btn btn-sm btn-primary">Lancer</button>
    </div>

    <!-- Player -->
    <div v-if="showPlayer && currentUrl">
      <div class="relative bg-black" style="aspect-ratio:16/9">

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
               controls autoplay playsinline
               :muted="muted"
               @loadeddata="loadingStream = false"
               @error="onError">
        </video>

        <!-- Mute toggle -->
        <button @click="muted = !muted"
                class="absolute top-2 right-2 btn btn-circle btn-xs bg-black/60 border-0 text-white z-20">
          <i class="fas text-xs" :class="muted ? 'fa-volume-xmark' : 'fa-volume-high'"></i>
        </button>
      </div>

      <!-- Admin : changer URL -->
      <div v-if="isAdmin" class="px-4 py-2 border-t border-base-300 flex gap-2 items-center bg-base-300/50">
        <i class="fas fa-link text-xs text-base-content/30"></i>
        <input v-model="customUrl" type="text"
               placeholder="Changer l'URL du flux"
               class="input input-bordered input-xs flex-1 font-mono text-xs" />
        <button @click="applyUrl" class="btn btn-xs btn-primary">OK</button>
        <button @click="clearUrl" class="btn btn-xs btn-ghost text-error">
          <i class="fas fa-trash text-xs"></i>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import Hls from 'hls.js'

const props = defineProps({
  streamUrl: { type: String, default: '' },
  isAdmin:   { type: Boolean, default: false },
  fixtureId: { type: [Number, String], default: null },
})

const emit = defineEmits(['stream-saved'])

const videoEl       = ref(null)
const showPlayer    = ref(true)
const loadingStream = ref(false)
const streamError   = ref(false)
const muted         = ref(true)
const customUrl     = ref('')

const currentUrl = computed(() => customUrl.value || props.streamUrl)

let hls = null

function initPlayer() {
  const url = currentUrl.value
  if (!url || !videoEl.value) return

  streamError.value   = false
  loadingStream.value = true

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

async function applyUrl() {
  if (!customUrl.value.trim()) return
  streamError.value = false

  // Sauvegarder en DB si admin + fixtureId
  if (props.isAdmin && props.fixtureId) {
    const token = localStorage.getItem('auth_token')
    if (token) {
      await fetch(`/api/wc26/streams/${props.fixtureId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify({ stream_url: customUrl.value, is_active: true }),
      }).catch(() => {})
      emit('stream-saved', customUrl.value)
    }
  }
  // Attendre que le DOM mette à jour le <video>
  await new Promise(r => setTimeout(r, 100))
  initPlayer()
}

async function clearUrl() {
  if (props.isAdmin && props.fixtureId) {
    const token = localStorage.getItem('auth_token')
    if (token) {
      await fetch(`/api/wc26/streams/${props.fixtureId}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
      }).catch(() => {})
      emit('stream-saved', '')
    }
  }
  customUrl.value = ''
  if (hls) { hls.destroy(); hls = null }
  if (videoEl.value) videoEl.value.src = ''
}

function onError() {
  streamError.value   = true
  loadingStream.value = false
}

onMounted(() => {
  if (props.streamUrl) {
    customUrl.value = props.streamUrl
    initPlayer()
  }
})

onUnmounted(() => { if (hls) hls.destroy() })

watch(() => props.streamUrl, url => {
  if (url && url !== customUrl.value) {
    customUrl.value = url
    initPlayer()
  }
})
</script>
