<template>
  <div class="space-y-5">

    <div class="flex items-center justify-between">
      <p class="text-gray-400 text-sm">{{ stadiums.length }} stades</p>
      <button @click="openModal()" class="btn-admin-primary text-sm">
        <i class="fas fa-plus mr-2"></i>Ajouter stade
      </button>
    </div>

    <input v-model="search" placeholder="Rechercher un stade…" class="input-admin w-full max-w-xs" />

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="s in filtered" :key="s.id ?? s.name"
           class="bg-white/5 border border-white/10 rounded-2xl p-4">
        <div class="flex justify-between items-start mb-2">
          <div>
            <p class="text-white font-bold text-sm">{{ s.name }}</p>
            <p class="text-gray-500 text-xs flex items-center gap-1 mt-0.5">
              <i class="fas fa-location-dot text-xs"></i>
              {{ s.city ?? s.city_en ?? '—' }}, {{ countryFlag(s.country) }} {{ s.country ?? '' }}
            </p>
          </div>
          <div class="flex gap-1 shrink-0">
            <button @click="openModal(s)" class="btn-admin-ghost p-1.5">
              <i class="fas fa-pen text-xs"></i>
            </button>
            <button @click="deleteStadium(s)" class="btn-admin-danger p-1.5">
              <i class="fas fa-trash text-xs"></i>
            </button>
          </div>
        </div>
        <div class="flex gap-3 text-xs mt-3">
          <span class="flex items-center gap-1 text-blue-400">
            <i class="fas fa-users"></i>
            {{ s.capacity ? Number(s.capacity).toLocaleString() : '—' }}
          </span>
          <span v-if="s.matches" class="flex items-center gap-1 text-gray-500">
            <i class="fas fa-futbol"></i>
            {{ s.matches }} matchs
          </span>
        </div>
      </div>
      <div v-if="!filtered.length && !loading"
           class="col-span-3 text-center py-12 text-gray-600">
        <i class="fas fa-building-circle-xmark text-4xl mb-3 block"></i>
        Aucun stade trouvé
      </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
      <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
           style="background:rgba(0,0,0,.7)" @click.self="modal=false">
        <div class="bg-[#0d0d2b] border border-white/10 rounded-2xl p-6 w-full max-w-md">
          <h2 class="text-white font-bold mb-5">
            {{ form.id ? 'Modifier' : 'Ajouter' }} un stade
          </h2>
          <div class="space-y-3">
            <div>
              <label class="label-admin">Nom du stade</label>
              <input v-model="form.name" class="input-admin w-full" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="label-admin">Ville</label>
                <input v-model="form.city" class="input-admin w-full" />
              </div>
              <div>
                <label class="label-admin">Pays</label>
                <select v-model="form.country" class="input-admin w-full">
                  <option v-for="c in hostCountries" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="label-admin">Capacité</label>
              <input v-model.number="form.capacity" type="number" class="input-admin w-full" />
            </div>
          </div>
          <div class="flex gap-3 mt-6">
            <button @click="saveStadium" class="btn-admin-primary flex-1">
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

const stadiums     = ref([])
const loading      = ref(false)
const search       = ref('')
const modal        = ref(false)
const form         = ref({})
const hostCountries = ['USA','Canada','Mexico']

const flags = { USA: '🇺🇸', Canada: '🇨🇦', Mexico: '🇲🇽' }
const countryFlag = c => flags[c] ?? ''

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return stadiums.value.filter(s =>
    !q || s.name?.toLowerCase().includes(q) || s.city?.toLowerCase().includes(q)
  )
})

function openModal(s = null) {
  form.value = s ? { ...s } : { name: '', city: '', country: 'USA', capacity: null }
  modal.value = true
}

function saveStadium() {
  const idx = stadiums.value.findIndex(s => s.id === form.value.id || s.name === form.value.name)
  if (idx >= 0) stadiums.value[idx] = { ...form.value }
  else stadiums.value.push({ ...form.value, id: Date.now() })
  modal.value = false
}

function deleteStadium(s) {
  if (!confirm(`Supprimer ${s.name} ?`)) return
  stadiums.value = stadiums.value.filter(x => x !== s)
}

async function load() {
  loading.value = true
  try {
    const d = await fetch('/api/wc26/stadiums').then(r => r.json())
    stadiums.value = d.stadiums ?? d ?? []
  } catch {}
  loading.value = false
}

onMounted(load)
</script>
