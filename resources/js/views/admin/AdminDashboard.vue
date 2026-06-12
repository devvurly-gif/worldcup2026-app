<template>
  <div class="space-y-6">

    <!-- Stats cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard icon="fa-shield-halved" color="blue"   label="Équipes"   :value="stats.teams" />
      <StatCard icon="fa-users"         color="green"  label="Joueurs"   :value="stats.players" />
      <StatCard icon="fa-futbol"        color="yellow" label="Matchs"    :value="stats.fixtures" />
      <StatCard icon="fa-building"      color="purple" label="Stades"    :value="stats.stadiums" />
    </div>

    <!-- Quick actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

      <!-- Import serveur rapide -->
      <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
        <p class="text-white font-bold mb-1 flex items-center gap-2">
          <i class="fas fa-bolt text-yellow-400"></i>
          Import rapide squads
        </p>
        <p class="text-gray-500 text-xs mb-4">
          Importe <code class="text-yellow-400">squads_wc2026.xlsx</code> depuis le serveur (48 équipes).
        </p>
        <button @click="importServer"
                :disabled="importing"
                class="w-full py-2.5 rounded-xl text-sm font-bold
                       bg-yellow-500 hover:bg-yellow-400 text-black transition-colors
                       disabled:opacity-40">
          <i v-if="importing" class="fas fa-circle-notch animate-spin mr-2"></i>
          <i v-else class="fas fa-file-import mr-2"></i>
          {{ importing ? 'Import en cours…' : 'Importer maintenant' }}
        </button>
        <p v-if="importMsg" class="mt-3 text-xs"
           :class="importMsg.ok ? 'text-green-400' : 'text-red-400'">
          {{ importMsg.text }}
        </p>
      </div>

      <!-- Derniers matchs -->
      <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
        <p class="text-white font-bold mb-3 flex items-center gap-2">
          <i class="fas fa-clock text-blue-400"></i>
          Matchs du jour
        </p>
        <div v-if="todayFixtures.length" class="space-y-2">
          <div v-for="m in todayFixtures.slice(0,4)" :key="m.id"
               class="flex items-center justify-between text-xs py-1.5 border-b border-white/5">
            <span class="text-gray-300 font-medium">{{ m.home }} vs {{ m.away }}</span>
            <span class="text-yellow-400 font-bold">
              {{ m.homeScore != null ? `${m.homeScore} - ${m.awayScore}` : m.time }}
            </span>
          </div>
        </div>
        <p v-else class="text-gray-600 text-xs text-center py-4">
          <i class="fas fa-calendar-xmark block text-2xl mb-2"></i>
          Aucun match aujourd'hui
        </p>
      </div>
    </div>

    <!-- Squads status -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <p class="text-white font-bold flex items-center gap-2">
          <i class="fas fa-users text-green-400"></i>
          Effectifs importés
          <span class="text-gray-500 font-normal text-sm">({{ squads.teams?.length ?? 0 }} / 48)</span>
        </p>
        <RouterLink to="/wc-admin/players" class="text-xs text-yellow-400 hover:underline">
          Gérer →
        </RouterLink>
      </div>
      <div class="flex flex-wrap gap-2">
        <span v-for="t in squads.teams ?? []" :key="t"
              class="text-xs px-2.5 py-1 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">
          {{ t }}
        </span>
        <span v-if="!squads.teams?.length" class="text-gray-600 text-xs">
          Aucun effectif importé — utilisez le bouton ci-dessus
        </span>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAdminStore } from '@/stores/admin'
import StatCard from './components/StatCard.vue'

const admin = useAdminStore()

const squads       = ref({})
const fixtures     = ref([])
const stadiumCount = ref(0)
const importing    = ref(false)
const importMsg    = ref(null)

const stats = computed(() => ({
  teams:    squads.value.teams?.length ?? 0,
  players:  squads.value.total_players ?? 0,
  fixtures: fixtures.value.length,
  stadiums: stadiumCount.value,
}))

const todayFixtures = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return fixtures.value.filter(m => m.date === today)
})

async function importServer() {
  importing.value = true
  importMsg.value = null
  try {
    const res = await fetch('/api/wc26/squads/import-server', {
      method: 'POST',
      headers: { 'X-Admin-Password': admin.password },
    })
    const data = await res.json()
    if (data.success) {
      importMsg.value = { ok: true, text: data.message }
      await loadSquads()
    } else {
      importMsg.value = { ok: false, text: data.error }
    }
  } catch (e) {
    importMsg.value = { ok: false, text: e.message }
  } finally {
    importing.value = false
  }
}

async function loadSquads() {
  try { squads.value = await fetch('/api/wc26/squads').then(r => r.json()) } catch {}
}
async function loadFixtures() {
  try {
    const d = await fetch('/api/wc26/fixtures').then(r => r.json())
    fixtures.value = d.fixtures ?? d ?? []
  } catch {}
}
async function loadStadiums() {
  try {
    const d = await fetch('/api/wc26/stadiums').then(r => r.json())
    stadiumCount.value = (d.stadiums ?? d ?? []).length
  } catch {}
}

onMounted(() => {
  loadSquads()
  loadFixtures()
  loadStadiums()
})
</script>
