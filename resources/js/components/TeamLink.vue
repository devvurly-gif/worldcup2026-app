<template>
  <RouterLink :to="`/equipe/${code}`"
              class="team-link inline-flex items-center gap-1.5 hover:text-yellow-400 transition-colors group/tl"
              :class="cls">
    <img v-if="showFlag && flagSrc" :src="flagSrc" :alt="code"
         class="object-cover rounded-sm shrink-0 inline-block"
         :class="flagImgCls" loading="lazy" />
    <span v-else-if="showFlag" class="shrink-0">🏳️</span>
    <span v-if="showName" class="truncate group-hover/tl:underline underline-offset-2">{{ displayName }}</span>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { getTeam } from '@/services/api'
import { flagImg } from '@/utils/flag'

const props = defineProps({
  code:     { type: String, required: true },
  name:     { type: String, default: null },
  showFlag: { type: Boolean, default: true },
  showName: { type: Boolean, default: true },
  flagSize: { type: String, default: 'text-xl' },
  cls:      { type: String, default: '' },
})

const team        = computed(() => getTeam(props.code))
const displayName = computed(() => props.name ?? team.value?.name ?? props.code)
const flagSrc     = computed(() => flagImg(props.code))

// Map text-size classes to image dimensions
const flagImgCls = computed(() => {
  const s = props.flagSize
  if (s === 'text-2xl') return 'w-8 h-5'
  if (s === 'text-3xl') return 'w-10 h-7'
  if (s === 'text-base') return 'w-6 h-4'
  return 'w-7 h-5'  // text-xl default
})
</script>
