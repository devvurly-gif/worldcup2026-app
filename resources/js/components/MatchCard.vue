<template>
  <RouterLink :to="`/match/${match.id}`"
              class="card p-4 glass-hover cursor-pointer block"
              :class="match.isLive ? 'border-red-500/40 live-pulse' : ''">
    <!-- Phase / Groupe -->
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold px-2.5 py-1 rounded-full"
            :class="phaseClass">
        {{ match.phase }}{{ match.group ? ` · Gr.${match.group}` : '' }}
      </span>
      <span v-if="match.isLive" class="badge-live">
        <span class="animate-blink">●</span> {{ match.elapsed }}'
      </span>
      <span v-else class="text-xs text-gray-500">{{ formatDate(match.date) }}</span>
    </div>

    <!-- Score central -->
    <div class="flex items-center justify-between gap-2">
      <!-- Équipe domicile -->
      <div class="flex-1 text-right flex flex-col items-end">
        <div class="text-2xl mb-1">
          <img v-if="match.homeLogo" :src="match.homeLogo" class="w-8 h-8 object-contain ml-auto"/>
          <TeamLink v-else :code="match.homeCode" :show-name="false" flag-size="text-2xl" @click.stop />
        </div>
        <TeamLink :code="match.homeCode" :name="match.homeName ?? match.homeCode"
                  :show-flag="false" cls="text-sm font-bold" @click.stop />
      </div>

      <!-- Score -->
      <div class="text-center min-w-[80px]">
        <div v-if="match.scoreHome !== null && match.scoreHome !== undefined"
             class="text-3xl font-black"
             :class="match.isLive ? 'text-red-400' : 'text-yellow-400'">
          {{ match.scoreHome }} — {{ match.scoreAway }}
        </div>
        <div v-else class="space-y-0.5">
          <div class="text-xl font-black text-gray-600">VS</div>
          <div class="text-xs text-yellow-400 font-semibold">{{ match.time }}</div>
        </div>
        <div v-if="match.status === 'HT'" class="text-xs text-orange-400 font-bold mt-1">Mi-temps</div>
        <div v-if="match.status === 'FT'" class="text-xs text-gray-500 mt-1">Terminé</div>
      </div>

      <!-- Équipe extérieur -->
      <div class="flex-1 text-left flex flex-col items-start">
        <div class="text-2xl mb-1">
          <img v-if="match.awayLogo" :src="match.awayLogo" class="w-8 h-8 object-contain"/>
          <TeamLink v-else :code="match.awayCode" :show-name="false" flag-size="text-2xl" @click.stop />
        </div>
        <TeamLink :code="match.awayCode" :name="match.awayName ?? match.awayCode"
                  :show-flag="false" cls="text-sm font-bold" @click.stop />
      </div>
    </div>

    <!-- Stade -->
    <div class="mt-3 text-center text-xs text-gray-600">
      <i class="fas fa-map-marker-alt mr-1"></i>{{ match.stadium }}, {{ match.city }}
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { getTeam } from '@/services/api'
import TeamLink from '@/components/TeamLink.vue'

const props = defineProps({ match: Object })

const homeTeam = computed(() => getTeam(props.match.homeCode))
const awayTeam = computed(() => getTeam(props.match.awayCode))

const phaseClass = computed(() => {
  const p = props.match.phase
  if (p === 'Finale') return 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'
  if (p === 'Demi-finale') return 'bg-purple-500/20 text-purple-400 border border-purple-500/30'
  if (p === 'Quarts') return 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
  return 'glass text-gray-400'
})

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day:'numeric', month:'short' })
}
</script>
