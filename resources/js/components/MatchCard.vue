<template>
  <RouterLink :to="`/match/${match.id}`"
              class="card card-compact bg-base-200 border border-base-300 hover:border-primary/40
                     hover:shadow-lg hover:shadow-primary/5 transition-all duration-200 cursor-pointer block"
              :class="match.isLive ? 'border-error/40 shadow-error/10 shadow-md' : ''">
    <div class="card-body p-4">

      <!-- Phase + live badge -->
      <div class="flex items-center justify-between mb-2">
        <span class="badge badge-sm font-bold" :class="phaseClass">
          {{ match.phase }}{{ match.group ? ` · Gr.${match.group}` : '' }}
        </span>
        <span v-if="match.isLive" class="badge badge-error badge-sm gap-1 animate-pulse font-black">
          <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
          {{ match.elapsed }}'
        </span>
        <span v-else class="text-xs text-base-content/30">{{ formatDate(match.date) }}</span>
      </div>

      <!-- Teams + score -->
      <div class="flex items-center justify-between gap-2">

        <!-- Home -->
        <div class="flex-1 flex flex-col items-end gap-1">
          <div>
            <img v-if="match.homeLogo" :src="match.homeLogo" class="w-8 h-8 object-contain ml-auto"/>
            <TeamLink v-else :code="match.homeCode" :show-name="false" flag-size="text-2xl" @click.stop />
          </div>
          <TeamLink :code="match.homeCode" :name="match.homeName ?? match.homeCode"
                    :show-flag="false" cls="text-sm font-bold text-right" @click.stop />
        </div>

        <!-- Score -->
        <div class="text-center min-w-[72px]">
          <div v-if="match.scoreHome !== null && match.scoreHome !== undefined"
               class="text-2xl font-black tabular-nums"
               :class="match.isLive ? 'text-error' : 'text-primary'">
            {{ match.scoreHome }}<span class="text-base-content/20 mx-0.5">-</span>{{ match.scoreAway }}
          </div>
          <div v-else class="flex flex-col items-center gap-0.5">
            <span class="text-lg font-black text-base-content/20">VS</span>
            <span class="text-xs text-primary font-bold">{{ match.time }}</span>
          </div>
          <div v-if="match.status === 'HT'" class="badge badge-warning badge-xs mt-1">Mi-temps</div>
          <div v-if="match.status === 'FT'" class="text-[10px] text-base-content/30 mt-1">Terminé</div>
        </div>

        <!-- Away -->
        <div class="flex-1 flex flex-col items-start gap-1">
          <div>
            <img v-if="match.awayLogo" :src="match.awayLogo" class="w-8 h-8 object-contain"/>
            <TeamLink v-else :code="match.awayCode" :show-name="false" flag-size="text-2xl" @click.stop />
          </div>
          <TeamLink :code="match.awayCode" :name="match.awayName ?? match.awayCode"
                    :show-flag="false" cls="text-sm font-bold" @click.stop />
        </div>
      </div>

      <!-- Stade -->
      <div class="text-center text-[11px] text-base-content/30 mt-2 flex items-center justify-center gap-1">
        <i class="fas fa-location-dot text-[10px]"></i>
        {{ match.stadium }}, {{ match.city }}
      </div>

    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import TeamLink from '@/components/TeamLink.vue'

const props = defineProps({ match: Object })

const phaseClass = computed(() => {
  const p = props.match.phase
  if (p === 'Finale')      return 'badge-warning'
  if (p === 'Demi-finale') return 'badge-secondary badge-outline'
  if (p === 'Quarts')      return 'badge-info badge-outline'
  if (p === 'Huitièmes')   return 'badge-ghost'
  return 'badge-ghost'
})

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day:'numeric', month:'short' })
}
</script>
