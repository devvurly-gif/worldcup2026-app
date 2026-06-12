<template>
  <div class="min-h-screen flex items-center justify-center bg-navy-deep px-4"
       style="background: radial-gradient(ellipse at 50% 0%, #1a1a5e 0%, #050518 70%)">

    <div class="w-full max-w-sm">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="text-5xl mb-3">⚽</div>
        <h1 class="text-2xl font-black text-white tracking-tight">WC 2026 Admin</h1>
        <p class="text-gray-500 text-sm mt-1">Panneau d'administration</p>
      </div>

      <!-- Card -->
      <div class="bg-white/5 border border-white/10 rounded-2xl p-8 backdrop-blur">
        <form @submit.prevent="submit">
          <label class="block text-xs text-gray-400 uppercase tracking-widest mb-2">
            Mot de passe
          </label>
          <input
            v-model="pw"
            type="password"
            placeholder="••••••••••"
            autocomplete="current-password"
            autofocus
            class="w-full bg-black/30 border rounded-xl px-4 py-3 text-white text-sm
                   focus:outline-none transition-colors mb-4"
            :class="error ? 'border-red-500' : 'border-white/20 focus:border-brand'"
          />

          <p v-if="error" class="text-red-400 text-xs mb-4 flex items-center gap-2">
            <i class="fas fa-circle-xmark"></i> {{ error }}
          </p>

          <button
            type="submit"
            :disabled="!pw || loading"
            class="w-full py-3 rounded-xl font-bold text-sm transition-all
                   bg-brand hover:bg-brand-light text-black
                   disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <i v-if="loading" class="fas fa-circle-notch animate-spin mr-2"></i>
            <i v-else class="fas fa-right-to-bracket mr-2"></i>
            {{ loading ? 'Connexion…' : 'Se connecter' }}
          </button>
        </form>
      </div>

      <p class="text-center mt-6">
        <RouterLink to="/" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">
          <i class="fas fa-arrow-left mr-1"></i>Retour au site
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'

const router = useRouter()
const admin  = useAdminStore()

const pw      = ref('')
const loading = ref(false)
const error   = ref('')

async function submit() {
  if (!pw.value) return
  loading.value = true
  error.value   = ''
  const ok = await admin.login(pw.value)
  loading.value = false
  if (ok) {
    router.push({ name: 'admin-dashboard' })
  } else {
    error.value = 'Mot de passe incorrect'
    pw.value    = ''
  }
}
</script>
