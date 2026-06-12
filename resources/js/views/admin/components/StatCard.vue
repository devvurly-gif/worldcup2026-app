<template>
  <div class="bg-white/5 border border-white/10 rounded-2xl p-5 flex items-center gap-4">
    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
         :class="bgClass">
      <i :class="`fas ${icon} text-lg`" :style="`color:${iconColor}`"></i>
    </div>
    <div>
      <p class="text-2xl font-black text-white leading-none">{{ value ?? '—' }}</p>
      <p class="text-gray-500 text-xs mt-0.5">{{ label }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ icon: String, color: String, label: String, value: [Number, String] })

const palettes = {
  blue:   { bg: 'bg-blue-500/10',   color: '#60a5fa' },
  green:  { bg: 'bg-green-500/10',  color: '#4ade80' },
  yellow: { bg: 'bg-yellow-500/10', color: '#fbbf24' },
  purple: { bg: 'bg-purple-500/10', color: '#c084fc' },
}
const bgClass   = computed(() => palettes[props.color]?.bg   ?? 'bg-white/10')
const iconColor = computed(() => palettes[props.color]?.color ?? '#fff')
</script>
