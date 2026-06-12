<template>
  <!-- Google AdSense — configure ADSENSE_CLIENT and slot in .env / this component -->
  <div v-if="enabled" class="google-ad" :class="wrapClass">
    <ins class="adsbygoogle"
         style="display:block"
         :data-ad-client="client"
         :data-ad-slot="slot"
         :data-ad-format="format"
         data-full-width-responsive="true">
    </ins>
  </div>

  <!-- Placeholder shown when AdSense not configured -->
  <div v-else class="ad-placeholder" :class="placeholderClass">
    <div class="flex flex-col items-center justify-center h-full gap-2 opacity-40">
      <i class="fas fa-rectangle-ad text-2xl text-gray-500"></i>
      <span class="text-xs text-gray-500 uppercase tracking-wider">Publicité</span>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'

const props = defineProps({
  slot:   { type: String, default: '0000000000' }, // Your AdSense slot ID
  format: { type: String, default: 'auto' },       // auto | rectangle | banner
  size:   { type: String, default: 'banner' },     // banner | rectangle | skyscraper
})

// ⚙️  SET YOUR ADSENSE PUBLISHER ID HERE (or via Vite env VITE_ADSENSE_CLIENT)
const client  = import.meta.env.VITE_ADSENSE_CLIENT || 'ca-pub-XXXXXXXXXXXXXXXXX'
const enabled = computed(() => !client.includes('XXXXXXXXXX'))

const wrapClass = computed(() => ({
  'min-h-[90px]':  props.size === 'banner',
  'min-h-[250px]': props.size === 'rectangle',
  'min-h-[600px]': props.size === 'skyscraper',
}))

const placeholderClass = computed(() => ({
  'h-[90px] w-full rounded-xl bg-white/3 border border-white/10':              props.size === 'banner',
  'h-[250px] w-full rounded-xl bg-white/3 border border-white/10':             props.size === 'rectangle',
  'h-[600px] w-[160px] rounded-xl bg-white/3 border border-white/10':          props.size === 'skyscraper',
  'h-[280px] w-full md:w-[336px] rounded-xl bg-white/3 border border-white/10':props.size === 'large-rect',
}))

onMounted(() => {
  if (enabled.value) {
    try { (window.adsbygoogle = window.adsbygoogle || []).push({}) } catch {}
  }
})
</script>
