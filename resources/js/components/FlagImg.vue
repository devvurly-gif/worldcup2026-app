<template>
  <img v-if="src" :src="src" :alt="code" :class="cls" loading="eager" decoding="async" />
  <span v-else :class="cls">🏳️</span>
</template>

<script setup>
import { computed } from 'vue'
import { flagImg } from '@/utils/flag'

const props = defineProps({
  code: { type: String, default: '' },
  cls:  { type: String, default: 'w-8 h-5 object-cover rounded-sm' },
})

const src = computed(() => flagImg(props.code))
</script>
