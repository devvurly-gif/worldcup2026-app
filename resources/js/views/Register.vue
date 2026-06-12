<template>
  <div class="min-h-screen hero-bg flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <div class="text-5xl mb-3">⚽</div>
        <h1 class="text-2xl font-black text-white">World Cup <span class="text-gradient">2026</span></h1>
      </div>

      <div class="card p-6 rounded-2xl">
        <h2 class="text-lg font-bold text-white mb-5">Créer un compte</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="label-admin">Nom</label>
            <input v-model="form.name" type="text" required class="input-admin w-full" placeholder="Votre nom" />
          </div>
          <div>
            <label class="label-admin">Email</label>
            <input v-model="form.email" type="email" required class="input-admin w-full" placeholder="vous@email.com" />
          </div>
          <div>
            <label class="label-admin">Mot de passe</label>
            <input v-model="form.password" type="password" required minlength="8" class="input-admin w-full" placeholder="Min. 8 caractères" />
          </div>
          <div>
            <label class="label-admin">Confirmer le mot de passe</label>
            <input v-model="form.password_confirmation" type="password" required class="input-admin w-full" placeholder="••••••••" />
          </div>

          <p v-if="error" class="text-red-400 text-sm">{{ error }}</p>

          <button type="submit" :disabled="loading" class="btn-admin-primary w-full">
            <span v-if="loading">Inscription…</span>
            <span v-else>S'inscrire</span>
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
          Déjà un compte ?
          <RouterLink to="/login" class="text-yellow-400 hover:underline">Se connecter</RouterLink>
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
const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

async function submit() {
  error.value = ''
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Les mots de passe ne correspondent pas.'
    return
  }
  loading.value = true
  try {
    await auth.register(form.value.name, form.value.email, form.value.password, form.value.password_confirmation)
    router.push('/')
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
