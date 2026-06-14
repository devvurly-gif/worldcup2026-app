<template>
  <div class="space-y-5">

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 items-center">
      <select v-model="statusFilter" class="input-admin">
        <option value="">Tous les matchs</option>
        <option value="finished">Terminés</option>
        <option value="live">En direct</option>
        <option value="upcoming">À venir</option>
      </select>
      <input v-model="search" placeholder="Équipe, groupe…" class="input-admin w-48" />
      <span class="ml-auto text-gray-500 text-sm">{{ filtered.length }} matchs</span>
      <button @click="loadFixtures" class="btn-admin-ghost text-sm">
        <i class="fas fa-rotate-right mr-1"></i>Actualiser
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-white/10 text-xs text-gray-500 uppercase tracking-wider">
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-left">Heure</th>
            <th class="px-4 py-3 text-left">Domicile</th>
            <th class="px-4 py-3 text-center w-32">Score</th>
            <th class="px-4 py-3 text-left">Extérieur</th>
            <th class="px-4 py-3 text-left">Groupe</th>
            <th class="px-4 py-3 text-left">Stade</th>
            <th class="px-4 py-3 text-center">Stream</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in paginated" :key="m.id"
              class="border-b border-white/5 hover:bg-white/3 transition-colors">
            <td class="px-4 py-3 text-gray-400 text-xs">{{ m.date }}</td>
            <td class="px-4 py-3 text-gray-400 text-xs">{{ m.time }}</td>
            <td class="px-4 py-3 text-white font-medium">
              <span class="inline-flex items-center gap-1.5"><img :src="flagImg(m.home)" :alt="m.home" class="w-5 h-3.5 object-cover rounded-sm" />{{ m.home }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span v-if="m.homeScore != null"
                    class="font-black text-white text-base tracking-widest">
                {{ m.homeScore }} – {{ m.awayScore }}
              </span>
              <span v-else class="text-gray-600 text-xs">vs</span>
            </td>
            <td class="px-4 py-3 text-white font-medium">
              <span class="inline-flex items-center gap-1.5"><img :src="flagImg(m.away)" :alt="m.away" class="w-5 h-3.5 object-cover rounded-sm" />{{ m.away }}</span>
            </td>
            <td class="px-4 py-3 text-xs">
              <span v-if="m.group"
                    class="bg-yellow-500/10 text-yellow-400 px-2 py-0.5 rounded font-bold">
                Gr. {{ m.group }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs truncate max-w-28">{{ m.venue }}</td>
            <td class="px-4 py-3 text-center">
              <span v-if="getStream(m.id)"
                    class="inline-flex items-center gap-1 text-xs text-green-400 font-bold">
                <i class="fas fa-circle text-[8px] animate-pulse"></i> Actif
              </span>
              <span v-else class="text-xs text-gray-700">—</span>
            </td>
            <td class="px-4 py-3 text-right">
              <button @click="openModal(m)" class="btn-admin-ghost text-xs">
                <i class="fas fa-pen mr-1"></i>Éditer
              </button>
            </td>
          </tr>
          <tr v-if="!filtered.length && !loading">
            <td colspan="8" class="px-4 py-10 text-center text-gray-600">
              <i class="fas fa-futbol block text-3xl mb-3 animate-pulse"></i>
              Aucun match
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center gap-2 justify-center" v-if="totalPages > 1">
      <button @click="page--" :disabled="page===1" class="btn-admin-ghost px-3">‹</button>
      <span class="text-gray-400 text-sm">{{ page }} / {{ totalPages }}</span>
      <button @click="page++" :disabled="page===totalPages" class="btn-admin-ghost px-3">›</button>
    </div>

    <!-- Score Modal -->
    <Teleport to="body">
      <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
           style="background:rgba(0,0,0,.7)" @click.self="modal=false">
        <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl p-6 w-full max-w-sm">
          <h2 class="text-white font-bold mb-5 text-center">
            {{ form.home }} vs {{ form.away }}
          </h2>

          <!-- Score -->
          <div class="flex items-center gap-3 mb-4">
            <div class="flex-1 text-center">
              <p class="text-gray-400 text-xs mb-1">{{ form.home }}</p>
              <input v-model.number="form.homeScore" type="number" min="0"
                     class="input-admin w-full text-center text-2xl font-black" />
            </div>
            <span class="text-gray-500 font-black text-xl">–</span>
            <div class="flex-1 text-center">
              <p class="text-gray-400 text-xs mb-1">{{ form.away }}</p>
              <input v-model.number="form.awayScore" type="number" min="0"
                     class="input-admin w-full text-center text-2xl font-black" />
            </div>
          </div>

          <!-- Statut -->
          <div class="mb-4">
            <label class="label-admin">Statut</label>
            <select v-model="form.status" class="input-admin w-full">
              <option value="upcoming">À venir</option>
              <option value="live">En direct</option>
              <option value="finished">Terminé</option>
            </select>
          </div>

          <!-- Stream URL -->
          <div class="mb-5">
            <label class="label-admin flex items-center gap-2">
              <i class="fas fa-video text-red-400"></i>
              URL Stream Live
              <span class="text-gray-600 font-normal">(optionnel)</span>
            </label>
            <input v-model="form.streamUrl" type="url"
                   placeholder="https://... (.m3u8, .mp4, iframe src...)"
                   class="input-admin w-full font-mono text-xs" />
            <p class="text-gray-600 text-xs mt-1">
              Supporte HLS (.m3u8), MP4, et tout flux vidéo direct.
            </p>
            <button v-if="form.streamUrl"
                    @click="form.streamUrl = ''"
                    class="text-xs text-red-400 hover:text-red-300 mt-1">
              <i class="fas fa-trash mr-1"></i>Supprimer le stream
            </button>
          </div>

          <div class="flex gap-3">
            <button @click="saveScore" class="btn-admin-primary flex-1">
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
import { ref, computed, watch, onMounted } from 'vue'
import { TEAMS } from '@/services/api'
import { flagImg } from '@/utils/flag'

const PAGE_SIZE = 20
const fixtures     = ref([])
const streams      = ref({}) // { fixtureId: streamUrl }
const loading      = ref(false)
const search       = ref('')
const statusFilter = ref('')
const page         = ref(1)
const modal        = ref(false)
const form         = ref({})

const teamMap = Object.fromEntries(TEAMS.map(t => [t.code, t]))

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return fixtures.value.filter(m => {
    if (statusFilter.value) {
      const s = m.homeScore != null ? 'finished' : 'upcoming'
      if (statusFilter.value !== s) return false
    }
    if (q && !m.home.toLowerCase().includes(q) && !m.away.toLowerCase().includes(q)) return false
    return true
  })
})

const totalPages = computed(() => Math.ceil(filtered.value.length / PAGE_SIZE) || 1)
const paginated  = computed(() => filtered.value.slice((page.value-1)*PAGE_SIZE, page.value*PAGE_SIZE))
watch([search, statusFilter], () => page.value = 1)

function getStream(id) {
  return streams.value[id] || ''
}

async function openModal(m) {
  form.value = {
    ...m,
    homeScore: m.homeScore ?? 0,
    awayScore: m.awayScore ?? 0,
    status: m.homeScore != null ? 'finished' : 'upcoming',
    streamUrl: '',
  }
  // Charger stream URL depuis l'API
  try {
    const res = await fetch(`/api/wc26/streams/${m.id}`)
    const d   = await res.json()
    form.value.streamUrl = d.stream_url ?? ''
  } catch {}
  modal.value = true
}

async function saveScore() {
  const idx = fixtures.value.findIndex(m => m.id === form.value.id)
  if (idx >= 0) fixtures.value[idx] = { ...form.value }

  // Sauvegarder stream URL via API
  const token = localStorage.getItem('auth_token')
  if (token) {
    if (form.value.streamUrl) {
      await fetch(`/api/wc26/streams/${form.value.id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify({ stream_url: form.value.streamUrl, is_active: true }),
      }).catch(() => {})
      streams.value[form.value.id] = form.value.streamUrl
    } else {
      await fetch(`/api/wc26/streams/${form.value.id}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
      }).catch(() => {})
      delete streams.value[form.value.id]
    }
  }
  modal.value = false
}

async function loadFixtures() {
  loading.value = true
  try {
    const d = await fetch('/api/wc26/fixtures').then(r => r.json())
    fixtures.value = d.fixtures ?? d ?? []
  } catch {}
  loading.value = false
}

async function loadStreams() {
  try {
    const res = await fetch('/api/wc26/streams')
    const d   = await res.json()
    streams.value = Object.fromEntries((d.data ?? []).map(s => [s.fixture_id, s.stream_url]))
  } catch {}
}

onMounted(() => { loadFixtures(); loadStreams() })
</script>
