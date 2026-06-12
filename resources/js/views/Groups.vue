<template>
  <div>
    <h1 class="section-title">
      <i class="fas fa-layer-group text-yellow-400"></i>
      Phase de Groupes — 12 Groupes
    </h1>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="(teams, letter) in store.groupsFromFixtures" :key="letter"
           class="card overflow-hidden">

        <!-- Header -->
        <div class="hero-bg px-5 py-4 flex items-center justify-between">
          <span class="text-yellow-400 font-black text-xl">GROUPE {{ letter }}</span>
          <div class="flex gap-2 text-xs text-gray-400 font-mono">
            <span class="w-6 text-center">J</span>
            <span class="w-6 text-center">G</span>
            <span class="w-6 text-center">N</span>
            <span class="w-6 text-center">P</span>
            <span class="w-8 text-center">Pts</span>
          </div>
        </div>

        <!-- Teams -->
        <div class="divide-y divide-white/5">
          <div v-for="(code, idx) in teams" :key="code"
               class="flex items-center px-5 py-3 gap-3 hover:bg-white/5 transition-colors cursor-pointer"
               @click="$router.push(`/equipe/${code}`)">
            <span class="text-gray-600 text-sm w-4">{{ idx+1 }}</span>
            <img :src="flagImg(code)" :alt="code" class="w-7 h-5 object-cover rounded-sm shrink-0" loading="lazy" />
            <span class="flex-1 text-sm font-semibold text-white">{{ getTeam(code)?.name ?? code }}</span>
            <!-- Standing depuis API ou tirets -->
            <div v-if="getStanding(code)" class="flex gap-2 text-xs text-gray-400 font-mono">
              <span class="w-6 text-center">{{ getStanding(code)?.all?.played ?? 0 }}</span>
              <span class="w-6 text-center text-green-400">{{ getStanding(code)?.all?.win ?? 0 }}</span>
              <span class="w-6 text-center text-yellow-400">{{ getStanding(code)?.all?.draw ?? 0 }}</span>
              <span class="w-6 text-center text-red-400">{{ getStanding(code)?.all?.lose ?? 0 }}</span>
              <span class="w-8 text-center font-black text-white">{{ getStanding(code)?.points ?? 0 }}</span>
            </div>
            <div v-else class="flex gap-2 text-xs text-gray-700 font-mono">
              <span class="w-6 text-center">—</span>
              <span class="w-6 text-center">—</span>
              <span class="w-6 text-center">—</span>
              <span class="w-6 text-center">—</span>
              <span class="w-8 text-center">—</span>
            </div>
          </div>
        </div>

        <!-- Matchs du groupe -->
        <div class="px-5 pb-4 pt-2 border-t border-white/5 bg-black/20">
          <p class="text-xs text-gray-600 uppercase tracking-widest mb-2">Matchs</p>
          <div v-for="m in groupMatches(letter).slice(0,3)" :key="m.id"
               class="flex items-center justify-between py-1.5 text-xs cursor-pointer
                      hover:text-yellow-400 transition-colors"
               @click="$router.push(`/match/${m.id}`)">
            <span class="flex items-center gap-1">
              <img :src="flagImg(m.homeCode)" :alt="m.homeCode" class="w-5 h-3.5 object-cover rounded-sm" />
              {{ getTeam(m.homeCode)?.name ?? m.homeCode }}
            </span>
            <span class="font-bold px-2"
                  :class="m.scoreHome !== null ? 'text-yellow-400' : 'text-gray-600'">
              {{ m.scoreHome !== null ? `${m.scoreHome}—${m.scoreAway}` : m.time }}
            </span>
            <span class="flex items-center gap-1">
              {{ getTeam(m.awayCode)?.name ?? m.awayCode }}
              <img :src="flagImg(m.awayCode)" :alt="m.awayCode" class="w-5 h-3.5 object-cover rounded-sm" />
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAppStore } from '@/stores/app'
import { getTeam, TEAMS } from '@/services/api'
import { flagImg } from '@/utils/flag'
import { useSeoMeta } from '@/composables/useSeoMeta'
useSeoMeta({ title: 'Groupes – Classements Coupe du Monde 2026', description: 'Classements des 12 groupes de la Coupe du Monde FIFA 2026.', path: '/groupes' })

const store = useAppStore()

function getStanding(code) {
  return store.getStanding(code)
}

function groupMatches(letter) {
  return store.fixtures.filter(m => m.group === letter)
}
</script>

