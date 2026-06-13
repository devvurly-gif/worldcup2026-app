<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <div>
        <h1 class="text-2xl font-black text-base-content flex items-center gap-2">
          <i class="fas fa-calendar text-primary"></i>
          Calendrier & Résultats
        </h1>
        <p class="text-sm text-base-content/40 mt-0.5">{{ filtered.length }} matchs</p>
      </div>

      <!-- Filtres -->
      <div class="flex flex-wrap gap-2 items-center">
        <div class="join">
          <button v-for="p in phases" :key="p"
                  @click="filterPhase = p"
                  class="join-item btn btn-xs font-bold"
                  :class="filterPhase === p ? 'btn-primary' : 'btn-ghost text-base-content/40'">
            {{ p }}
          </button>
        </div>
        <select v-model="filterGroup"
                class="select select-bordered select-xs text-base-content">
          <option value="">Tous les groupes</option>
          <option v-for="g in 'ABCDEFGHIJKL'.split('')" :key="g" :value="g">Groupe {{ g }}</option>
        </select>
      </div>
    </div>

    <!-- Groupé par date -->
    <div v-for="(dayMatches, date) in groupedByDate" :key="date" class="mb-8">
      <div class="flex items-center gap-3 mb-3">
        <div class="badge badge-primary badge-outline font-bold capitalize">{{ formatDay(date) }}</div>
        <div class="flex-1 h-px bg-base-300"></div>
        <span class="text-xs text-base-content/30">{{ dayMatches.length }} match{{ dayMatches.length > 1 ? 's' : '' }}</span>
      </div>

      <div class="flex flex-col gap-2">
        <RouterLink v-for="m in dayMatches" :key="m.id"
                    :to="`/match/${m.id}`"
                    class="card card-compact bg-base-200 border border-base-300
                           hover:border-primary/40 hover:shadow-md transition-all"
                    :class="m.isLive ? 'border-error/40' : ''">
          <div class="card-body flex-row items-center gap-4 p-4">

            <!-- Heure + ville -->
            <div class="w-24 shrink-0">
              <div class="text-primary font-black text-sm">{{ m.time }}</div>
              <div class="text-[11px] text-base-content/30 mt-0.5">{{ m.city }}</div>
              <span v-if="m.isLive" class="badge badge-error badge-xs gap-1 animate-pulse mt-1">
                <span class="w-1 h-1 rounded-full bg-current"></span>
                {{ m.elapsed }}'
              </span>
              <span v-else-if="m.status === 'FT'" class="badge badge-ghost badge-xs mt-1">Terminé</span>
            </div>

            <!-- Équipes + score -->
            <div class="flex items-center gap-3 flex-1 min-w-0">
              <div class="flex-1 flex items-center justify-end gap-2 min-w-0">
                <span class="font-bold text-sm text-base-content truncate text-right">{{ m.homeName ?? m.homeCode }}</span>
                <TeamLink :code="m.homeCode" :show-name="false" flag-size="text-xl" @click.stop />
              </div>

              <div class="shrink-0 w-20 text-center">
                <span v-if="m.scoreHome !== null"
                      class="text-xl font-black tabular-nums"
                      :class="m.isLive ? 'text-error' : 'text-base-content'">
                  {{ m.scoreHome }}<span class="text-base-content/20">-</span>{{ m.scoreAway }}
                </span>
                <span v-else class="text-base-content/20 font-black text-lg">VS</span>
              </div>

              <div class="flex-1 flex items-center gap-2 min-w-0">
                <TeamLink :code="m.awayCode" :show-name="false" flag-size="text-xl" @click.stop />
                <span class="font-bold text-sm text-base-content truncate">{{ m.awayName ?? m.awayCode }}</span>
              </div>
            </div>

            <!-- Phase + stade -->
            <div class="hidden md:block w-36 shrink-0 text-right">
              <span class="badge badge-ghost badge-sm">{{ m.phase }}{{ m.group ? ` · Gr.${m.group}` : '' }}</span>
              <div class="text-[11px] text-base-content/25 mt-1 truncate">{{ m.stadium }}</div>
            </div>

          </div>
        </RouterLink>
      </div>
    </div>

    <div v-if="!filtered.length" class="card bg-base-200 p-12 text-center">
      <i class="fas fa-calendar-xmark text-4xl text-base-content/20 mb-4 block"></i>
      <p class="text-base-content/40">Aucun match pour cette sélection</p>
    </div>
  </div>
</template>

<script setup>
import { useSeoMeta } from '@/composables/useSeoMeta'
useSeoMeta({ title: 'Calendrier – Tous les Matchs Mondial 2026', description: 'Calendrier complet des 104 matchs de la Coupe du Monde FIFA 2026.', path: '/calendrier' })
import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import TeamLink from '@/components/TeamLink.vue'

const store = useAppStore()
const filterPhase = ref('Tous')
const filterGroup = ref('')
const phases = ['Tous', 'Groupes', 'Huitièmes', 'Quarts', 'Demi-finale', 'Finale']

const filtered = computed(() => store.fixtures.filter(m => {
  if (filterPhase.value !== 'Tous' && m.phase !== filterPhase.value) return false
  if (filterGroup.value && m.group !== filterGroup.value) return false
  return true
}))

const groupedByDate = computed(() => {
  const map = {}
  filtered.value.forEach(m => {
    if (!map[m.date]) map[m.date] = []
    map[m.date].push(m)
  })
  return map
})

function formatDay(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long', year:'numeric' })
}
</script>
