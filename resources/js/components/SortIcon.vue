<template>
  <i class="fas text-[10px] ml-1 transition-opacity"
     :class="[
       current === field ? (dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort',
       current === field ? 'opacity-100 text-yellow-400' : 'opacity-30'
     ]"></i>
</template>

<script setup>
defineProps({ field: String, current: String, dir: String })
</script>
