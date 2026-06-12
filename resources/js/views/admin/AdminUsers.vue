<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-black text-white">Utilisateurs</h1>
      <span class="text-sm text-gray-500">{{ users.length }} comptes</span>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-500">Chargement…</div>

    <div v-else class="card rounded-2xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-white/5 text-gray-400 text-xs uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-left">Nom</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-left">Rôle</th>
            <th class="px-4 py-3 text-left">Statut</th>
            <th class="px-4 py-3 text-left">Inscrit le</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="u in users" :key="u.id" class="hover:bg-white/3 transition-colors">
            <td class="px-4 py-3 text-white font-medium">{{ u.name }}</td>
            <td class="px-4 py-3 text-gray-400">{{ u.email }}</td>
            <td class="px-4 py-3">
              <select :value="u.role" @change="updateRole(u, $event.target.value)"
                      class="input-admin text-xs py-1 px-2">
                <option value="user">user</option>
                <option value="editor">editor</option>
                <option value="admin">admin</option>
              </select>
            </td>
            <td class="px-4 py-3">
              <button @click="toggleActive(u)"
                      :class="u.is_active ? 'text-green-400' : 'text-red-400'"
                      class="text-xs font-bold">
                {{ u.is_active ? 'Actif' : 'Inactif' }}
              </button>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs">{{ formatDate(u.created_at) }}</td>
            <td class="px-4 py-3"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth    = useAuthStore()
const users   = ref([])
const loading = ref(true)

async function load() {
  loading.value = true
  const res = await fetch('/api/admin/users', { headers: auth.authHeaders() })
  users.value = await res.json()
  loading.value = false
}

async function updateRole(u, role) {
  await fetch(`/api/admin/users/${u.id}`, {
    method: 'PUT',
    headers: { ...auth.authHeaders(), 'Content-Type': 'application/json' },
    body: JSON.stringify({ role }),
  })
  u.role = role
}

async function toggleActive(u) {
  const is_active = !u.is_active
  await fetch(`/api/admin/users/${u.id}`, {
    method: 'PUT',
    headers: { ...auth.authHeaders(), 'Content-Type': 'application/json' },
    body: JSON.stringify({ is_active }),
  })
  u.is_active = is_active
}

function formatDate(d) {
  return d ? new Date(d).toLocaleDateString('fr-FR') : ''
}

onMounted(load)
</script>
