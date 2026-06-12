<template>
  <RouterLink
    :to="to"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
    :class="isActive
      ? 'bg-yellow-500/15 text-yellow-400 font-semibold'
      : 'text-gray-500 hover:text-white hover:bg-white/5'"
  >
    <i :class="`fas ${icon} w-4 text-center text-xs`"></i>
    <span>{{ label }}</span>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({ to: String, icon: String, label: String })
const route = useRoute()
const isActive = computed(() => route.path === props.to || route.path.startsWith(props.to + '/'))
</script>
