<template>
  <div class="space-y-5">

    <!-- Header + Add -->
    <div class="flex items-center justify-between">
      <p class="text-gray-400 text-sm">{{ teams.length }} équipes</p>
      <button @click="openModal()" class="btn-admin-primary text-sm">
        <i class="fas fa-plus mr-2"></i>Ajouter équipe
      </button>
    </div>

    <!-- Search -->
    <input v-model="search" placeholder="Rechercher une équipe…"
           class="input-admin w-full max-w-xs" />

    <!-- Table -->
    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-white/10 text-xs text-gray-500 uppercase tracking-wider">
            <th class="px-4 py-3 text-left">Drapeau</th>
            <th class="px-4 py-3 text-left">Code</th>
            <th class="px-4 py-3 text-left">Nom</th>
            <th class="px-4 py-3 text-left">Groupe</th>
            <th class="px-4 py-3 text-left">Conf.</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in filtered" :key="t.code"
              class="border-b border-white/5 hover:bg-white/3 transition-colors">
            <td class="px-4 py-3"><img :src="flagImg(t.code)" :alt="t.code" class="w-10 h-7 object-cover rounded" /></td>
            <td class="px-4 py-3 font-mono font-bold text-yellow-400">{{ t.code }}</td>
            <td class="px-4 py-3 text-white font-medium">{{ t.name }}</td>
            <td class="px-4 py-3 text-gray-400">{{ t.group ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-400">{{ t.confederation ?? '—' }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="openModal(t)" class="btn-admin-ghost mr-2">
                <i class="fas fa-pen text-xs"></i>
              </button>
              <button @click="deleteTeam(t.code)" class="btn-admin-danger">
                <i class="fas fa-trash text-xs"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!filtered.length">
            <td colspan="6" class="px-4 py-10 text-center text-gray-600">
              Aucune équipe trouvée
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
           style="background:rgba(0,0,0,.7)" @click.self="modal=false">
        <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl p-6 w-full max-w-md">
          <h2 class="text-white font-bold mb-5">
            {{ form.code ? 'Modifier' : 'Ajouter' }} une équipe
          </h2>
          <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="label-admin">Code FIFA</label>
                <input v-model="form.code" class="input-admin w-full" placeholder="MAR" maxlength="3" />
              </div>
              <div>
                <label class="label-admin">Drapeau (emoji)</label>
                <input v-model="form.flag" class="input-admin w-full" placeholder="🇲🇦" />
              </div>
            </div>
            <div>
              <label class="label-admin">Nom complet</label>
              <input v-model="form.name" class="input-admin w-full" placeholder="Maroc" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="label-admin">Groupe</label>
                <select v-model="form.group" class="input-admin w-full">
                  <option value="">—</option>
                  <option v-for="g in groups" :key="g" :value="g">{{ g }}</option>
                </select>
              </div>
              <div>
                <label class="label-admin">Confédération</label>
                <select v-model="form.confederation" class="input-admin w-full">
                  <option value="">—</option>
                  <option v-for="c in confs" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="flex gap-3 mt-6">
            <button @click="saveTeam" class="btn-admin-primary flex-1">
              <i class="fas fa-check mr-2"></i>Enregistrer
            </button>
            <button @click="modal=false" class="btn-admin-ghost flex-1">Annuler</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { TEAMS } from '@/services/api'
import { flagImg } from '@/utils/flag'

const teams  = ref([...TEAMS])
const search = ref('')
const modal  = ref(false)
const form   = ref({})

const groups = 'ABCDEFGHIJKL'.split('')
const confs  = ['UEFA','CONMEBOL','CONCACAF','CAF','AFC','OFC']

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return teams.value.filter(t =>
    t.name.toLowerCase().includes(q) || t.code.toLowerCase().includes(q)
  )
})

function openModal(team = null) {
  form.value = team
    ? { ...team }
    : { code: '', flag: '', name: '', group: '', confederation: '' }
  modal.value = true
}

function saveTeam() {
  const idx = teams.value.findIndex(t => t.code === form.value.code)
  if (idx >= 0) {
    teams.value[idx] = { ...form.value }
  } else {
    teams.value.push({ ...form.value })
  }
  modal.value = false
}

function deleteTeam(code) {
  if (!confirm(`Supprimer ${code} ?`)) return
  teams.value = teams.value.filter(t => t.code !== code)
}
</script>
