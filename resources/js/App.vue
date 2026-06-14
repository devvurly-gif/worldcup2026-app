<template>
  <div class="min-h-screen bg-base-100">
    <Navbar />
    <main class="max-w-7xl mx-auto px-4 py-6">
      <RouterView v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>
    <Footer />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useAppStore } from '@/stores/app'
import Navbar from '@/components/Navbar.vue'
import Footer from '@/components/Footer.vue'

const store = useAppStore()
onMounted(() => store.startPolling())
onUnmounted(() => store.stopPolling())
</script>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity .2s, transform .2s; }
.fade-enter-from { opacity: 0; transform: translateY(6px); }
.fade-leave-to   { opacity: 0; transform: translateY(-6px); }
</style>
