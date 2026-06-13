<template>
  <div>
    <h1 class="text-2xl font-black text-base-content flex items-center gap-2 mb-6">
      <i class="fas fa-layer-group text-primary"></i>
      Phase de Groupes — 12 Groupes
    </h1>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="(teams, letter) in store.groupsFromFixtures" :key="letter"
           class="card bg-base-200 border border-base-300 overflow-hidden">

        <!-- Header groupe -->
        <div class="bg-base-300 px-5 py-3 flex items-center justify-between border-b border-base-300">
          <span class="font-black text-primary tracking-widest text-sm">GROUPE {{ letter }}</span>
          <div class="flex gap-3 text-[11px] text-base-content/30 font-mono font-bold">
            <span class="w-5 text-center">J</span>
            <span class="w-5 text-center text-success">G</span>
            <span class="w-5 text-center text-warning">N</span>
            <span class="w-5 text-center text-error">P</span>
            <span class="w-7 text-center text-base-content">Pts</span>
          </div>
        </div>

        <!-- Équipes -->
        <div class="divide-y divide-base-300">
          <div v-for="(code, idx) in teams" :key="code"
               class="flex items-center px-4 py-2.5 gap-3 hover:bg-base-300 transition-colors cursor-pointer"
               @click="$router.push(`/equipe/${code}`)">
            <span class="text-base-content/20 text-xs font-bold w-4">{{ idx+1 }}</span>
            <img :src="flagImg(code)" :alt="code" class="w-7 h-5 object-cover rounded-sm shrink-0" loading="lazy" />
            <span class="flex-1 text-sm font-semibold text-base-content">{{ getTeam(code)?.name ?? code }}</span>
            <div v-if="getStanding(code)" class="flex gap-3 text-xs font-mono">
              <span class="w-5 text-center text-base-content/60">{{ getStanding(code)?.all?.played ?? 0 }}</span>
              <span class="w-5 text-center text-success font-bold">{{ getStanding(code)?.all?.win ?? 0 }}</span>
              <span class="w-5 text-center text-warning">{{ getStanding(code)?.all?.draw ?? 0 }}</span>
              <span class="w-5 text-center text-error">{{ getStanding(code)?.all?.lose ?? 0 }}</span>
              <span class="w-7 text-center font-black text-base-content">{{ getStanding(code)?.points ?? 0 }}</span>
            </div>
            <div v-else class="flex gap-3 text-xs font-mono text-base-content/20">
              <span class="w-5 text-center">—</span>
              <span class="w-5 text-center">—</span>
              <span class="w-5 text-center">—</span>
              <span class="w-5 text-center">—</span>
              <span class="w-7 text-center">—</span>
            </div>
          </div>
        </div>

        <!-- Matchs du groupe -->
        <div class="px-4 pb-3 pt-2 bg-base-300/50 border-t border-base-300">
          <p class="text-[10px] text-base-content/30 uppercase tracking-widest mb-2 font-bold">Matchs</p>
          <div v-for="m in groupMatches(letter).slice(0,3)" :key="m.id"
               class="flex items-center justify-between py-1.5 text-xs cursor-pointer
                      hover:text-primary transition-colors rounded"
               @click.stop="$router.push(`/match/${m.id}`)">
            <span class="flex items-center gap-1.5 flex-1 min-w-0">
              <img :src="flagImg(m.homeCode)" :alt="m.homeCode" class="w-5 h-3.5 object-cover rounded-sm shrink-0" />
              <span class="truncate text-base-content/70">{{ getTeam(m.homeCode)?.name ?? m.homeCode }}</span>
            </span>
            <span class="badge badge-xs mx-2 shrink-0 font-bold"
                  :class="m.scoreHome !== null ? 'badge-primary badge-outline' : 'badge-ghost'">
              {{ m.scoreHome !== null ? `${m.scoreHome}–${m.scoreAway}` : m.time }}
            </span>
            <span class="flex items-center gap-1.5 flex-1 min-w-0 justify-end">
              <span class="truncate text-base-content/70">{{ getTeam(m.awayCode)?.name ?? m.awayCode }}</span>
              <img :src="flagImg(m.awayCode)" :alt="m.awayCode" class="w-5 h-3.5 object-cover rounded-sm shrink-0" />
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAppStore } from '@/stores/app'
import { getTeam } from '@/services/api'
import { flagImg } from '@/utils/flag'
import { useSeoMeta } from '@/composables/useSeoMeta'
useSeoMeta({ title: 'Groupes – Classements Coupe du Monde 2026', description: 'Classements des 12 groupes de la Coupe du Monde FIFA 2026.', path: '/groupes' })

const store = useAppStore()
function getStanding(code) { return store.getStanding(code) }
function groupMatches(letter) { return store.fixtures.filter(m => m.group === letter) }
</script>
