<template>
  <div class="max-w-3xl mx-auto space-y-8">

    <div class="flex items-center gap-3">
      <RouterLink to="/" class="btn-ghost text-sm">
        <i class="fas fa-arrow-left mr-2"></i>Retour
      </RouterLink>
      <h1 class="section-title mb-0">
        <i class="fas fa-file-excel text-green-400"></i>
        Import Effectifs XLS
      </h1>
    </div>

    <!-- Format attendu -->
    <div class="card p-5 border border-blue-500/20 bg-blue-500/5">
      <p class="text-blue-400 font-bold text-sm mb-3">
        <i class="fas fa-info-circle mr-2"></i>Format du fichier Excel
      </p>
      <p class="text-xs text-gray-400 mb-3">
        Une seule feuille avec les colonnes suivantes (la ligne 1 = en-têtes) :
      </p>
      <div class="overflow-x-auto">
        <table class="text-xs w-full border-collapse">
          <thead>
            <tr class="border-b border-white/10">
              <th v-for="col in columns" :key="col.name"
                  class="px-3 py-2 text-left font-bold"
                  :class="col.required ? 'text-yellow-400' : 'text-gray-500'">
                {{ col.name }}
                <span v-if="col.required" class="text-red-400">*</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b border-white/5">
              <td v-for="col in columns" :key="col.name" class="px-3 py-2 text-gray-400">
                {{ col.example }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class="text-xs text-gray-600 mt-3">
        * Obligatoires — <span class="text-yellow-400">team</span> = code FIFA 3 lettres (MAR, BRA, FRA…)
      </p>

      <!-- Bouton télécharger template -->
      <button @click="downloadTemplate"
              class="mt-3 btn-ghost text-xs text-green-400 border-green-500/30">
        <i class="fas fa-download mr-1"></i>Télécharger template Excel
      </button>
    </div>

    <!-- Import depuis fichier serveur -->
    <div class="card p-5 border border-green-500/20 bg-green-500/5">
      <p class="text-green-400 font-bold text-sm mb-3">
        <i class="fas fa-server mr-2"></i>Import rapide depuis le fichier serveur
      </p>
      <p class="text-xs text-gray-400 mb-4">
        Importe directement <code class="text-yellow-400">squads_wc2026.xlsx</code> (48 équipes, ~1200 joueurs) déjà présent sur le serveur.
      </p>
      <div class="flex gap-3">
        <input v-model="adminPassword" type="password"
               placeholder="Mot de passe admin"
               class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white
                      focus:outline-none focus:border-green-400" />
        <button @click="importFromServer"
                :disabled="!adminPassword || uploading"
                class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all
                       disabled:opacity-40 disabled:cursor-not-allowed
                       bg-green-600 hover:bg-green-500 text-white">
          <span v-if="uploading"><i class="fas fa-circle-notch animate-spin mr-2"></i>En cours…</span>
          <span v-else><i class="fas fa-bolt mr-2"></i>Importer</span>
        </button>
      </div>
    </div>

    <!-- Upload zone -->
    <div class="card p-6">
      <p class="text-sm font-bold text-white mb-4">
        <i class="fas fa-upload text-yellow-400 mr-2"></i>Importer un fichier
      </p>

      <!-- Drop zone -->
      <div class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
           :class="dragging
             ? 'border-yellow-400 bg-yellow-400/5'
             : 'border-white/20 hover:border-white/40'"
           @dragover.prevent="dragging = true"
           @dragleave="dragging = false"
           @drop.prevent="onDrop"
           @click="$refs.fileInput.click()">
        <i class="fas fa-file-excel text-5xl mb-3 block"
           :class="selectedFile ? 'text-green-400' : 'text-gray-700'"></i>
        <p v-if="!selectedFile" class="text-gray-500 text-sm">
          Glisse ton fichier ici ou <span class="text-yellow-400">clique pour sélectionner</span>
        </p>
        <div v-else class="text-sm">
          <p class="text-green-400 font-bold">{{ selectedFile.name }}</p>
          <p class="text-gray-500 text-xs mt-1">{{ formatSize(selectedFile.size) }}</p>
        </div>
        <input ref="fileInput" type="file"
               accept=".xlsx,.xls,.csv"
               class="hidden"
               @change="onFileSelect" />
      </div>

      <!-- Mot de passe admin -->
      <div class="mt-4">
        <label class="text-xs text-gray-500 uppercase tracking-wider block mb-1">
          Mot de passe admin
        </label>
        <input v-model="adminPassword" type="password"
               placeholder="••••••••••"
               class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white
                      focus:outline-none focus:border-yellow-400" />
      </div>

      <!-- Bouton import -->
      <button @click="importFile"
              :disabled="!selectedFile || !adminPassword || uploading"
              class="w-full mt-4 py-3 rounded-xl font-bold text-sm transition-all
                     disabled:opacity-40 disabled:cursor-not-allowed"
              :class="uploading ? 'bg-gray-700 text-gray-400' : 'bg-yellow-500 hover:bg-yellow-400 text-black'">
        <span v-if="uploading">
          <i class="fas fa-circle-notch animate-spin mr-2"></i>Importation en cours…
        </span>
        <span v-else>
          <i class="fas fa-file-import mr-2"></i>Importer l'effectif
        </span>
      </button>
    </div>

    <!-- Résultat import -->
    <div v-if="result" class="card p-5"
         :class="result.success ? 'border border-green-500/30 bg-green-500/5' : 'border border-red-500/30 bg-red-500/5'">
      <div class="flex items-start gap-3">
        <i class="text-2xl mt-0.5"
           :class="result.success ? 'fas fa-check-circle text-green-400' : 'fas fa-circle-xmark text-red-400'"></i>
        <div class="flex-1">
          <p class="font-bold text-sm" :class="result.success ? 'text-green-400' : 'text-red-400'">
            {{ result.message ?? result.error }}
          </p>
          <div v-if="result.teams?.length" class="mt-3 flex flex-wrap gap-2">
            <span v-for="t in result.teams" :key="t.team"
                  class="glass text-xs px-3 py-1 rounded-full text-white">
              {{ t.team }} — {{ t.players }} joueurs
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Équipes déjà importées -->
    <div class="card p-5">
      <div class="flex items-center justify-between mb-4">
        <p class="font-bold text-white text-sm">
          <i class="fas fa-users text-yellow-400 mr-2"></i>
          Effectifs importés
          <span v-if="squads" class="text-gray-500 font-normal ml-2">
            ({{ squads.teams?.length ?? 0 }} équipes · {{ squads.total_players ?? 0 }} joueurs)
          </span>
        </p>
        <button @click="loadSquads" class="btn-ghost text-xs">
          <i class="fas fa-rotate-right mr-1"></i>Actualiser
        </button>
      </div>

      <div v-if="squads?.teams?.length" class="space-y-2">
        <div v-for="code in squads.teams" :key="code"
             class="flex items-center gap-3 p-3 glass rounded-xl">
          <img :src="flagImg(code)" :alt="code" class="w-7 h-5 object-cover rounded-sm shrink-0" />
          <span class="flex-1 font-bold text-white text-sm">{{ getTeam(code)?.name ?? code }}</span>
          <span class="text-xs text-gray-500">
            {{ squads.data?.[code]?.length ?? 0 }} joueurs
          </span>
          <button @click="deleteTeam(code)"
                  class="text-xs text-red-400 hover:text-red-300 transition-colors ml-2">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
      <div v-else class="text-center py-6 text-gray-600 text-sm">
        <i class="fas fa-inbox text-3xl mb-3 block"></i>
        Aucun effectif importé pour le moment
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getTeam } from '@/services/api'
import { flagImg } from '@/utils/flag'

const API = '/api/wc26'

const selectedFile  = ref(null)
const adminPassword = ref('')
const uploading     = ref(false)
const dragging      = ref(false)
const result        = ref(null)
const squads        = ref(null)
const fileInput     = ref(null)

const columns = [
  { name: 'team',     required: true,  example: 'MAR' },
  { name: 'name',     required: true,  example: 'Achraf Hakimi' },
  { name: 'position', required: true,  example: 'Défenseur' },
  { name: 'number',   required: false, example: '2' },
  { name: 'age',      required: false, example: '26' },
  { name: 'club',     required: false, example: 'PSG' },
  { name: 'caps',     required: false, example: '70' },
  { name: 'goals',    required: false, example: '3' },
]

function onFileSelect(e) {
  selectedFile.value = e.target.files[0] ?? null
  result.value = null
}
function onDrop(e) {
  dragging.value = false
  selectedFile.value = e.dataTransfer.files[0] ?? null
  result.value = null
}
function formatSize(bytes) {
  return bytes > 1024*1024
    ? (bytes / 1024 / 1024).toFixed(1) + ' MB'
    : (bytes / 1024).toFixed(0) + ' KB'
}

async function importFile() {
  if (!selectedFile.value || !adminPassword.value) return
  uploading.value = true
  result.value = null
  try {
    const fd = new FormData()
    fd.append('file', selectedFile.value)

    const res = await fetch(`${API}/squads/import`, {
      method: 'POST',
      headers: { 'X-Admin-Password': adminPassword.value },
      body: fd,
    })
    result.value = await res.json()
    if (result.value.success) {
      selectedFile.value = null
      await loadSquads()
    }
  } catch(e) {
    result.value = { error: `Erreur réseau : ${e.message}` }
  } finally {
    uploading.value = false
  }
}

async function loadSquads() {
  try {
    const res = await fetch(`${API}/squads`)
    squads.value = await res.json()
  } catch {}
}

async function deleteTeam(code) {
  if (!adminPassword.value) {
    adminPassword.value = prompt('Mot de passe admin :') ?? ''
  }
  if (!adminPassword.value) return
  if (!confirm(`Supprimer l'effectif de ${code} ?`)) return
  try {
    await fetch(`${API}/squads/${code}`, {
      method: 'DELETE',
      headers: { 'X-Admin-Password': adminPassword.value },
    })
    await loadSquads()
  } catch {}
}

function downloadTemplate() {
  // Génère un CSV template téléchargeable
  const header = 'team,name,position,number,age,club,caps,goals'
  const rows = [
    'MAR,Achraf Hakimi,Défenseur,2,26,PSG,70,3',
    'MAR,Yassine Bounou,Gardien,1,33,Al-Hilal,60,0',
    'FRA,Kylian Mbappé,Attaquant,10,27,Real Madrid,80,45',
  ]
  const csv = [header, ...rows].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url  = URL.createObjectURL(blob)
  const a    = document.createElement('a')
  a.href = url
  a.download = 'squads_template.csv'
  a.click()
  URL.revokeObjectURL(url)
}

onMounted(() => loadSquads())
</script>
