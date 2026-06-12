<template>
  <div>
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <h1 class="section-title mb-0">
        <i class="fas fa-calendar text-yellow-400"></i>
        Calendrier & Résultats
        <span class="text-sm font-normal text-gray-500">({{ filtered.length }} matchs)</span>
      </h1>

      <!-- Filtres -->
      <div class="flex flex-wrap gap-2">
        <button v-for="p in phases" :key="p"
                @click="filterPhase = p"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                :class="filterPhase === p ? 'bg-yellow-500 text-black' : 'btn-ghost text-gray-400'">
          {{ p }}
        </button>
        <!-- Filtre groupe -->
        <select v-model="filterGroup"
                class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-yellow-400">
          <option value="">Tous les groupes</option>
          <option v-for="g in 'ABCDEFGHIJKL'.split('')" :key="g" :value="g">Groupe {{ g }}</option>
        </select>
      </div>
    </div>

    <!-- Liste par date -->
    <div v-for="(dayMatches, date) in groupedByDate" :key="date" class="mb-8">
      <div class="flex items-center gap-3 mb-3">
        <span class="text-sm font-black text-yellow-400">{{ formatDay(date) }}</span>
        <div class="flex-1 h-px bg-white/10"></div>
        <span class="text-xs text-gray-600">{{ dayMatches.length }} match{{ dayMatches.length > 1 ? 's' : '' }}</span>
      </div>

      <div class="space-y-2">
        <RouterLink v-for="m in dayMatches" :key="m.id"
                    :to="`/match/${m.id}`"
                    class="card p-4 flex flex-col md:flex-row md:items-center gap-4 glass-hover">

          <!-- Heure + stade -->
          <div class="md:w-32 flex-shrink-0">
            <div class="text-yellow-400 font-bold text-sm">{{ m.time }}</div>
            <div class="text-xs text-gray-600 mt-0.5">{{ m.city }}</div>
            <span v-if="m.isLive" class="badge-live mt-1 inline-block">
              <span class="animate-blink">●</span> {{ m.elapsed }}'
            </span>
            <span v-else-if="m.status === 'FT'" class="text-xs text-gray-500 mt-1 block">Terminé</span>
          </div>

          <!-- Équipes + Score -->
          <div class="flex items-center gap-4 flex-1">
            <div class="flex-1 text-right flex items-center justify-end gap-2">
              <TeamLink :code="m.homeCode" :name="m.homeName" :show-flag="false"
                        cls="font-bold text-sm" @click.stop />
              <img v-if="m.homeLogo" :src="m.homeLogo" class="w-7 h-7 object-contain"/>
              <TeamLink v-else :code="m.homeCode" :show-name="false" flag-size="text-xl" @click.stop />
            </div>
            <div class="text-center w-24">
              <div v-if="m.scoreHome !== null"
                   class="text-2xl font-black"
                   :class="m.isLive ? 'text-red-400' : 'text-white'">
                {{ m.scoreHome }} — {{ m.scoreAway }}
              </div>
              <div v-else class="text-gray-600 font-black text-lg">VS</div>
            </div>
            <div class="flex-1 flex items-center gap-2">
              <img v-if="m.awayLogo" :src="m.awayLogo" class="w-7 h-7 object-contain"/>
              <TeamLink v-else :code="m.awayCode" :show-name="false" flag-size="text-xl" @click.stop />
              <TeamLink :code="m.awayCode" :name="m.awayName" :show-flag="false"
                        cls="font-bold text-sm" @click.stop />
            </div>
          </div>

          <!-- Badge phase -->
          <div class="md:w-40 flex-shrink-0 text-right">
            <span class="text-xs glass px-2.5 py-1 rounded-lg text-gray-400">
              {{ m.phase }}{{ m.group ? ` · Gr.${m.group}` : '' }}
            </span>
            <div class="text-xs text-gray-700 mt-1 truncate">{{ m.stadium }}</div>
          </div>
        </RouterLink>
      </div>
    </div>

    <div v-if="!filtered.length" class="card p-12 text-center text-gray-600">
      <i class="fas fa-calendar-xmark text-4xl mb-4 block"></i>
      Aucun match pour cette sélection
    </div>
  </div>
</template>

<script setup>
import { useSeoMeta } from '@/composables/useSeoMeta'
useSeoMeta({ title: 'Calendrier – Tous les Matchs Mondial 2026', description: 'Calendrier complet des 104 matchs de la Coupe du Monde FIFA 2026.', path: '/calendrier' })
import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import { getTeam } from '@/services/api'
import TeamLink from '@/components/TeamLink.vue'

const store = useAppStore()
const filterPhase = ref('Tous')
const filterGroup = ref('')

const phases = ['Tous', 'Groupes', 'Huitièmes', 'Quarts', 'Demi-finale', 'Finale']

const filtered = computed(() => {
  return store.fixtures.filter(m => {
    if (filterPhase.value !== 'Tous' && m.phase !== filterPhase.value) return false
    if (filterGroup.value && m.group !== filterGroup.value) return false
    return true
  })
})

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
  return new Date(d).toLocaleDateString('fr-FR', {
    weekday:'long', day:'numeric', month:'long', year:'numeric'
  })
}
</script>

