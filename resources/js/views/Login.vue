<template>
  <div class="min-h-screen hero-bg flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="text-5xl mb-3">⚽</div>
        <h1 class="text-2xl font-black text-white">World Cup <span class="text-gradient">2026</span></h1>
      </div>

      <div class="card p-6 rounded-2xl">
        <h2 class="text-lg font-bold text-white mb-5">Connexion</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="label-admin">Email</label>
            <input v-model="form.email" type="email" required class="input-admin w-full" placeholder="vous@email.com" />
          </div>
          <div>
            <label class="label-admin">Mot de passe</label>
            <input v-model="form.password" type="password" required class="input-admin w-full" placeholder="••••••••" />
          </div>

          <p v-if="error" class="text-red-400 text-sm">{{ error }}</p>

          <button type="submit" :disabled="loading" class="btn-admin-primary w-full">
            <span v-if="loading">Connexion…</span>
            <span v-else>Se connecter</span>
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
          Pas encore de compte ?
          <RouterLink to="/register" class="text-yellow-400 hover:underline">S'inscrire</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    router.push(auth.isAdmin ? '/admin' : '/')
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
