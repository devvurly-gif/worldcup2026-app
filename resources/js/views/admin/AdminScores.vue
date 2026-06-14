<template>
  <div class="space-y-5">

    <!-- Toolbar -->
    <div class="flex flex-wrap gap-3 items-center">
      <select v-model="statusFilter" class="input-admin">
        <option value="">Tous</option>
        <option value="NS">À venir</option>
        <option value="live">En direct</option>
        <option value="FT">Terminés</option>
      </select>
      <input v-model="search" placeholder="Équipe, groupe…" class="input-admin w-48" />
      <span class="ml-auto text-gray-500 text-sm">{{ filtered.length }} matchs</span>
      <button @click="loadAll" :disabled="loading" class="btn-admin-ghost text-sm">
        <i class="fas fa-rotate-right mr-1" :class="loading ? 'animate-spin' : ''"></i>Actualiser
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-white/10 text-xs text-gray-500 uppercase tracking-wider">
            <th class="px-3 py-3 text-left">Date</th>
            <th class="px-3 py-3 text-left">Match</th>
            <th class="px-3 py-3 text-center w-28">Score</th>
            <th class="px-3 py-3 text-center">Statut</th>
            <th class="px-3 py-3 text-center">Stream</th>
            <th class="px-3 py-3 text-center">Override</th>
            <th class="px-3 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in paginated" :key="m.id"
              class="border-b border-white/5 hover:bg-white/3 transition-colors"
              :class="m._overridden ? 'bg-yellow-500/5' : ''">

            <td class="px-3 py-3 text-gray-400 text-xs whitespace-nowrap">
              {{ m.date }}<br><span class="text-primary">{{ m.time }}</span>
            </td>

            <td class="px-3 py-3">
              <div class="flex items-center gap-2 whitespace-nowrap">
                <img :src="flagImg(m.homeCode)" class="w-5 h-3.5 object-cover rounded-sm" />
                <span class="font-medium text-white text-xs">{{ m.homeName ?? m.homeCode }}</span>
                <span class="text-gray-600 text-xs mx-1">vs</span>
                <span class="font-medium text-white text-xs">{{ m.awayName ?? m.awayCode }}</span>
                <img :src="flagImg(m.awayCode)" class="w-5 h-3.5 object-cover rounded-sm" />
                <span v-if="m.group" class="ml-1 text-[10px] bg-yellow-500/10 text-yellow-400 px-1.5 py-0.5 rounded font-bold">Gr.{{ m.group }}</span>
              </div>
            </td>

            <td class="px-3 py-3 text-center">
              <span v-if="m.scoreHome !== null" class="font-black text-white tabular-nums">
                {{ m.scoreHome }} – {{ m.scoreAway }}
              </span>
              <span v-else class="text-gray-700 text-xs">–</span>
            </td>

            <td class="px-3 py-3 text-center">
              <span class="text-xs px-2 py-0.5 rounded font-bold"
                    :class="statusClass(m.status)">{{ m.status }}</span>
            </td>

            <td class="px-3 py-3 text-center">
              <span v-if="getStream(m.id)" class="text-xs text-green-400 font-bold">
                <i class="fas fa-circle text-[8px] animate-pulse mr-1"></i>Actif
              </span>
              <span v-else class="text-gray-700 text-xs">—</span>
            </td>

            <td class="px-3 py-3 text-center">
              <i v-if="m._overridden" class="fas fa-pen-to-square text-yellow-400 text-xs" title="Override actif"></i>
              <span v-else class="text-gray-700 text-xs">—</span>
            </td>

            <td class="px-3 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="openModal(m)" class="btn-admin-ghost text-xs px-2 py-1">
                  <i class="fas fa-pen"></i>
                </button>
                <button v-if="m._overridden" @click="deleteOverride(m)" class="btn-admin-danger text-xs px-2 py-1" title="Supprimer override">
                  <i class="fas fa-rotate-left"></i>
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!filtered.length && !loading">
            <td colspan="7" class="px-4 py-10 text-center text-gray-600">
              <i class="fas fa-futbol block text-3xl mb-3 opacity-30"></i>Aucun match
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

    <!-- Modal CRUD -->
    <Teleport to="body">
      <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
           style="background:rgba(0,0,0,.75)" @click.self="modal=false">
        <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl p-6 w-full max-w-md">

          <h2 class="text-white font-bold mb-1 text-center text-sm">
            {{ form.homeName ?? form.homeCode }} vs {{ form.awayName ?? form.awayCode }}
          </h2>
          <p class="text-center text-gray-500 text-xs mb-5">{{ form.date }} · {{ form.time }} · {{ form.stadium }}</p>

          <!-- Score -->
          <div class="flex items-center gap-3 mb-4">
            <div class="flex-1 text-center">
              <p class="label-admin mb-1">{{ form.homeName ?? form.homeCode }}</p>
              <input v-model.number="form.homeScore" type="number" min="0" max="99"
                     class="input-admin w-full text-center text-2xl font-black" />
            </div>
            <span class="text-gray-500 font-black text-xl">–</span>
            <div class="flex-1 text-center">
              <p class="label-admin mb-1">{{ form.awayName ?? form.awayCode }}</p>
              <input v-model.number="form.awayScore" type="number" min="0" max="99"
                     class="input-admin w-full text-center text-2xl font-black" />
            </div>
          </div>

          <!-- Statut -->
          <div class="mb-4">
            <label class="label-admin">Statut</label>
            <select v-model="form.status" class="input-admin w-full">
              <option value="NS">NS — À venir</option>
              <option value="1H">1H — 1ère mi-temps</option>
              <option value="HT">HT — Mi-temps</option>
              <option value="2H">2H — 2ème mi-temps</option>
              <option value="ET">ET — Prolongations</option>
              <option value="PEN">PEN — Tirs au but</option>
              <option value="FT">FT — Terminé</option>
              <option value="PST">PST — Reporté</option>
              <option value="CANC">CANC — Annulé</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="mb-4">
            <label class="label-admin">Notes (optionnel)</label>
            <input v-model="form.notes" type="text" maxlength="500"
                   placeholder="Ex : Match suspendu à 67'"
                   class="input-admin w-full text-sm" />
          </div>

          <!-- Stream URL -->
          <div class="mb-5">
            <label class="label-admin flex items-center gap-2">
              <i class="fas fa-video text-red-400"></i> URL Stream Live
            </label>
            <input v-model="form.streamUrl" type="text"
                   placeholder="https://... (.m3u8, .mp4...)"
                   class="input-admin w-full font-mono text-xs" />
          </div>

          <div class="flex gap-3">
            <button @click="save" :disabled="saving" class="btn-admin-primary flex-1">
              <i class="fas fa-check mr-2"></i>{{ saving ? 'Enregistrement…' : 'Enregistrer' }}
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
import { flagImg } from '@/utils/flag'

const PAGE_SIZE = 25
const fixtures  = ref([])
const streams   = ref({})
const overrides = ref({})
const loading   = ref(false)
const saving    = ref(false)
const search    = ref('')
const statusFilter = ref('')
const page      = ref(1)
const modal     = ref(false)
const form      = ref({})

const token = () => localStorage.getItem('auth_token')

// ── Computed ─────────────────────────────────────────────────────
const enriched = computed(() =>
  fixtures.value.map(m => ({
    ...m,
    _overridden: !!overrides.value[m.id],
  }))
)

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return enriched.value.filter(m => {
    if (statusFilter.value) {
      if (statusFilter.value === 'live' && !m.isLive) return false
      if (statusFilter.value === 'NS'   && (m.status !== 'NS' || m.isLive)) return false
      if (statusFilter.value === 'FT'   && m.status !== 'FT') return false
    }
    if (q) {
      const hay = `${m.homeName} ${m.awayName} ${m.homeCode} ${m.awayCode} ${m.group ?? ''}`.toLowerCase()
      if (!hay.includes(q)) return false
    }
    return true
  })
})

const totalPages = computed(() => Math.ceil(filtered.value.length / PAGE_SIZE) || 1)
const paginated  = computed(() => filtered.value.slice((page.value-1)*PAGE_SIZE, page.value*PAGE_SIZE))
watch([search, statusFilter], () => page.value = 1)

// ── Helpers ───────────────────────────────────────────────────────
function getStream(id) { return streams.value[id] || '' }

function statusClass(s) {
  if (!s || s === 'NS') return 'bg-gray-500/20 text-gray-400'
  if (['1H','2H','ET','PEN'].includes(s)) return 'bg-red-500/20 text-red-400'
  if (s === 'HT') return 'bg-orange-500/20 text-orange-400'
  if (s === 'FT') return 'bg-green-500/20 text-green-400'
  return 'bg-yellow-500/20 text-yellow-400'
}

// ── Load ─────────────────────────────────────────────────────────
async function loadAll() {
  loading.value = true
  await Promise.allSettled([loadFixtures(), loadStreams(), loadOverrides()])
  loading.value = false
}

async function loadFixtures() {
  try {
    const d = await fetch('/api/wc26/fixtures').then(r => r.json())
    fixtures.value = (d.data ?? []).sort((a, b) =>
      (a.date ?? '').localeCompare(b.date ?? '') || (a.time ?? '').localeCompare(b.time ?? '')
    )
  } catch {}
}

async function loadStreams() {
  try {
    const d = await fetch('/api/wc26/streams').then(r => r.json())
    streams.value = Object.fromEntries((d.data ?? []).map(s => [s.fixture_id, s.stream_url]))
  } catch {}
}

async function loadOverrides() {
  try {
    const d = await fetch('/api/wc26/overrides').then(r => r.json())
    overrides.value = Object.fromEntries((d.data ?? []).map(o => [o.fixture_id, o]))
  } catch {}
}

// ── Modal ────────────────────────────────────────────────────────
async function openModal(m) {
  const ov = overrides.value[m.id]
  form.value = {
    ...m,
    homeScore : ov?.home_score ?? m.scoreHome ?? null,
    awayScore : ov?.away_score ?? m.scoreAway ?? null,
    status    : ov?.status    ?? m.status    ?? 'NS',
    notes     : ov?.notes     ?? '',
    streamUrl : streams.value[m.id] ?? '',
  }
  modal.value = true
}

async function save() {
  saving.value = true
  const t = token()
  if (!t) { saving.value = false; return }

  // Sauvegarder override score/statut
  const hasChanges = form.value.homeScore !== null || form.value.notes
  if (hasChanges || overrides.value[form.value.id]) {
    await fetch(`/api/wc26/overrides/${form.value.id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${t}` },
      body: JSON.stringify({
        home_score: form.value.homeScore,
        away_score: form.value.awayScore,
        status    : form.value.status,
        notes     : form.value.notes,
      }),
    }).catch(() => {})
  }

  // Sauvegarder stream URL
  if (form.value.streamUrl) {
    await fetch(`/api/wc26/streams/${form.value.id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${t}` },
      body: JSON.stringify({ stream_url: form.value.streamUrl, is_active: true }),
    }).catch(() => {})
  } else if (streams.value[form.value.id]) {
    await fetch(`/api/wc26/streams/${form.value.id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${t}` },
    }).catch(() => {})
  }

  await loadAll()
  saving.value = false
  modal.value  = false
}

async function deleteOverride(m) {
  if (!confirm('Supprimer l\'override et restaurer les données API ?')) return
  const t = token()
  await fetch(`/api/wc26/overrides/${m.id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${t}` },
  }).catch(() => {})
  await loadAll()
}

onMounted(loadAll)
</script>
