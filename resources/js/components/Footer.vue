<template>
  <footer class="border-t border-white/5 mt-12 py-6">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-gray-600">
      <div class="flex items-center gap-2">
        <span class="text-yellow-400 text-base">🏆</span>
        <span>FIFA World Cup 2026 Tracker</span>
        <span class="text-gray-700">·</span>
        <span>Données : API-Football · OpenWeatherMap</span>
      </div>
      <div class="flex items-center gap-4">
        <span>Site non officiel — à titre informatif</span>
        <span class="text-gray-700">·</span>
        <span>{{ quota }}/95 req. aujourd'hui</span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useAppStore } from '@/stores/app'
const store = useAppStore()
const quota = computed(() => store.quota.count)
</script>
