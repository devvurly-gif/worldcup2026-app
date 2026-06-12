<template>
  <div>

    <!-- ── CONTROLS ──────────────────────────────────────── -->
    <div class="sticky top-0 z-40 bg-[#0a0a2e]/95 backdrop-blur border-b border-white/10 px-4 py-3">
      <div class="max-w-7xl mx-auto flex flex-wrap gap-3 items-center">

        <div class="relative flex-1 min-w-44">
          <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
          <input v-model="search" placeholder="Joueur, équipe, club…"
                 class="w-full pl-8 pr-3 py-2 bg-black/30 border border-white/15 rounded-lg
                        text-sm text-white focus:outline-none focus:border-brand transition-colors" />
        </div>

        <select v-model="teamFilter"
                class="bg-black/30 border border-white/15 rounded-lg px-3 py-2
                       text-sm text-white focus:outline-none focus:border-brand min-w-44">
          <option value="">Toutes les équipes</option>
          <option v-for="t in TEAM_OPTIONS" :key="t.fr" :value="t.fr">
            {{ t.fr }}
          </option>
        </select>

        <select v-model="posFilter"
                class="bg-black/30 border border-white/15 rounded-lg px-3 py-2
                       text-sm text-white focus:outline-none focus:border-brand">
          <option value="">Tous postes</option>
          <option value="Gardien">🧤 Gardiens</option>
          <option value="Défenseur">🛡️ Défenseurs</option>
          <option value="Milieu">⚙️ Milieux</option>
          <option value="Attaquant">⚡ Attaquants</option>
        </select>

        <div class="flex gap-1">
          <button @click="view='grid'"
                  :class="view==='grid' ? 'bg-brand text-black' : 'bg-black/30 text-gray-400 border border-white/15'"
                  class="w-9 h-9 rounded-lg text-sm transition-colors flex items-center justify-center">
            <i class="fas fa-grid-2"></i>
          </button>
          <button @click="view='list'"
                  :class="view==='list' ? 'bg-brand text-black' : 'bg-black/30 text-gray-400 border border-white/15'"
                  class="w-9 h-9 rounded-lg text-sm transition-colors flex items-center justify-center">
            <i class="fas fa-list"></i>
          </button>
        </div>

        <span class="text-gray-500 text-xs whitespace-nowrap ml-auto">
          {{ filtered.length.toLocaleString() }} joueur{{ filtered.length > 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6">

      <!-- ── GRID ───────────────────────────────────────── -->
      <div v-if="view === 'grid'"
           class="grid gap-3"
           style="grid-template-columns: repeat(auto-fill, minmax(155px, 1fr))">

        <div v-for="p in paginated" :key="p.code + '|' + p.name"
             @click="selected = p"
             class="bg-white/5 border border-white/10 rounded-xl overflow-hidden cursor-pointer
                    transition-all hover:-translate-y-1 hover:shadow-xl hover:border-brand/50">
          <!-- Avatar -->
          <div class="aspect-square flex items-center justify-center relative overflow-hidden"
               :class="posBgLight(p.position)">
            <img v-if="p.photo" :src="p.photo" :alt="p.name"
                 class="w-full h-full object-cover object-top absolute inset-0" />
            <img v-else :src="flagImg(p.code)" :alt="p.code"
                 class="w-16 h-11 object-cover rounded opacity-50" />
            <span class="absolute top-2 right-2 text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wide text-white"
                  :class="posBg(p.position)">
              {{ posShort(p.position) }}
            </span>
            <span v-if="p.number" class="absolute bottom-2 left-2 text-[11px] font-black text-white/60">
              #{{ p.number }}
            </span>
          </div>
          <!-- Info -->
          <div class="p-2.5">
            <p class="text-white text-xs font-semibold leading-tight truncate">{{ p.name }}</p>
            <p class="text-gray-500 text-[11px] mt-0.5 truncate">{{ p.team }}</p>
            <p v-if="p.club" class="text-yellow-500/70 text-[10px] mt-0.5 truncate">{{ p.club }}</p>
            <p v-if="p.birth_date" class="text-gray-600 text-[10px] mt-1">
              {{ calcAge(p.birth_date) }} ans
            </p>
          </div>
        </div>

        <div v-if="!filtered.length" class="col-span-full text-center py-20 text-gray-600">
          <i class="fas fa-magnifying-glass text-4xl block mb-3"></i>
          <p>Aucun joueur trouvé</p>
        </div>
      </div>

      <!-- ── LIST ──────────────────────────────────────── -->
      <div v-else class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-white/10 text-xs text-gray-500 uppercase tracking-wider">
                <th class="px-3 py-3 text-left w-8">#</th>
                <th @click="sort('name')"     class="th-sort">Joueur        <SortIcon field="name"     :current="sortField" :dir="sortDir" /></th>
                <th @click="sort('team')"     class="th-sort">Équipe        <SortIcon field="team"     :current="sortField" :dir="sortDir" /></th>
                <th @click="sort('position')" class="th-sort">Poste         <SortIcon field="position" :current="sortField" :dir="sortDir" /></th>
                <th class="px-4 py-3 text-left">Club</th>
                <th @click="sort('age')"      class="th-sort">Âge           <SortIcon field="age"      :current="sortField" :dir="sortDir" /></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in paginated" :key="p.code + '|' + p.name"
                  @click="selected = p"
                  class="border-b border-white/5 hover:bg-white/5 transition-colors cursor-pointer">
                <td class="px-3 py-2 text-gray-600 text-xs">{{ (page-1)*PAGE_SIZE + i + 1 }}</td>
                <td class="px-4 py-2.5">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 flex items-center justify-center"
                         :class="p.photo ? '' : posBgLight(p.position)">
                      <img v-if="p.photo" :src="p.photo" :alt="p.name"
                           class="w-full h-full object-cover object-top" />
                      <img v-else :src="flagImg(p.code)" :alt="p.code"
                           class="w-full h-full object-cover" />
                    </div>
                    <div>
                      <p class="text-white font-medium">{{ p.name }}</p>
                      <p v-if="p.number" class="text-gray-600 text-[10px]">#{{ p.number }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-2.5 text-gray-400 text-xs">{{ p.team }}</td>
                <td class="px-4 py-2.5">
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase text-white"
                        :class="posBg(p.position)">
                    {{ posShort(p.position) }}
                  </span>
                </td>
                <td class="px-4 py-2.5 text-yellow-500/70 text-xs">{{ p.club ?? '—' }}</td>
                <td class="px-4 py-2.5 text-gray-500 text-xs">
                  {{ p.birth_date ? calcAge(p.birth_date) + ' ans' : '—' }}
                </td>
              </tr>
              <tr v-if="!filtered.length">
                <td colspan="6" class="px-4 py-16 text-center text-gray-600">
                  Aucun joueur trouvé
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── PAGINATION ────────────────────────────────── -->
      <div v-if="totalPages > 1" class="flex items-center justify-center gap-1.5 mt-6 flex-wrap">
        <PagBtn @click="page=1"           :disabled="page===1">«</PagBtn>
        <PagBtn @click="page--"           :disabled="page===1">‹</PagBtn>
        <template v-for="n in pageRange" :key="n">
          <span v-if="n==='…'" class="text-gray-600 px-1">…</span>
          <button v-else @click="page=n"
                  class="w-9 h-9 rounded-lg text-sm transition-colors"
                  :class="n===page ? 'bg-brand text-black font-bold' : 'bg-white/5 border border-white/10 text-gray-400 hover:bg-white/10'">
            {{ n }}
          </button>
        </template>
        <PagBtn @click="page++"           :disabled="page===totalPages">›</PagBtn>
        <PagBtn @click="page=totalPages"  :disabled="page===totalPages">»</PagBtn>
        <span class="text-gray-600 text-xs ml-1">{{ page }}/{{ totalPages }}</span>
      </div>

    </div>

    <!-- ── MODAL ─────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="selected" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.75)" @click.self="selected=null">
          <div class="bg-[#1e293b] border border-white/10 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl">

            <div class="h-96 flex items-center justify-center relative overflow-hidden" :class="posBgLight(selected.position)">
              <img v-if="selected.photo" :src="selected.photo" :alt="selected.name"
                   class="absolute inset-0 w-full h-full object-cover object-top" />
              <img v-else :src="flagImg(selected.code)" :alt="selected.code"
                   class="w-32 h-24 object-cover rounded-xl opacity-40" />
              <span class="absolute top-3 left-3 text-[11px] font-black px-2.5 py-1 rounded-full uppercase tracking-wide text-white"
                    :class="posBg(selected.position)">
                {{ selected.position }}
              </span>
              <span v-if="selected.number"
                    class="absolute bottom-3 left-3 text-white/50 font-black text-lg">#{{ selected.number }}</span>
              <button @click="selected=null"
                      class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/50 hover:bg-black/80
                             text-white flex items-center justify-center transition-colors">
                <i class="fas fa-xmark text-sm"></i>
              </button>
            </div>

            <div class="p-5">
              <h2 class="text-white text-xl font-black">{{ selected.name }}</h2>
              <p class="text-gray-400 text-sm mt-0.5 flex items-center gap-1.5">
                <img :src="flagImg(selected.code)" :alt="selected.code" class="w-5 h-3.5 object-cover rounded-sm" />
                {{ selected.team }}
              </p>
              <p v-if="selected.club" class="text-yellow-400 text-sm font-semibold mt-1">
                <i class="fas fa-building-columns mr-1"></i>{{ selected.club }}
              </p>

              <div class="grid grid-cols-2 gap-2.5 mt-4">
                <div v-for="(f) in modalFields(selected)" :key="f.label"
                     class="bg-white/5 rounded-xl p-3">
                  <p class="text-gray-600 text-[10px] uppercase tracking-wider mb-1">{{ f.label }}</p>
                  <p class="text-white text-sm font-semibold">{{ f.value }}</p>
                </div>
              </div>

              <div class="mt-4">
                <button @click="selected=null"
                        class="w-full py-2.5 rounded-xl text-sm font-bold
                               bg-white/5 hover:bg-white/10 text-gray-300 border border-white/10 transition-colors">
                  Fermer
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, defineComponent, h } from 'vue'
import SortIcon from '@/components/SortIcon.vue'
import { usePlayers, calcAge, posShort, posBg, posBgLight, TEAM_OPTIONS, POSITIONS } from '@/composables/usePlayers'
import { flagImg } from '@/utils/flag'

const PAGE_SIZE = 48

const { players, loadApiClubs } = usePlayers()

const search     = ref('')
const teamFilter = ref('')
const posFilter  = ref('')
const view       = ref('grid')
const page       = ref(1)
const selected   = ref(null)
const sortField  = ref('team')
const sortDir    = ref('asc')

// ── Filtered + sorted ────────────────────────────────────
const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  let list = players.value

  if (teamFilter.value) list = list.filter(p => p.team === teamFilter.value)
  if (posFilter.value)  list = list.filter(p => p.position === posFilter.value)
  if (q) list = list.filter(p =>
    p.name.toLowerCase().includes(q) ||
    p.team.toLowerCase().includes(q) ||
    (p.club ?? '').toLowerCase().includes(q)
  )

  return [...list].sort((a, b) => {
    const va = sortField.value === 'age'
      ? (a.birth_date ? calcAge(a.birth_date) : 99)
      : (a[sortField.value] ?? '').toString().toLowerCase()
    const vb = sortField.value === 'age'
      ? (b.birth_date ? calcAge(b.birth_date) : 99)
      : (b[sortField.value] ?? '').toString().toLowerCase()
    return sortDir.value === 'asc'
      ? (va < vb ? -1 : va > vb ? 1 : 0)
      : (va > vb ? -1 : va < vb ? 1 : 0)
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / PAGE_SIZE)))
const paginated  = computed(() => filtered.value.slice((page.value-1)*PAGE_SIZE, page.value*PAGE_SIZE))

watch([search, teamFilter, posFilter], () => page.value = 1)

const pageRange = computed(() => {
  const total = totalPages.value, cur = page.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const s = new Set([1, total, cur, cur-1, cur+1].filter(n => n >= 1 && n <= total))
  const sorted = [...s].sort((a, b) => a - b)
  const r = []
  for (let i = 0; i < sorted.length; i++) {
    if (i > 0 && sorted[i] - sorted[i-1] > 1) r.push('…')
    r.push(sorted[i])
  }
  return r
})

function sort(f) {
  if (sortField.value === f) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  else { sortField.value = f; sortDir.value = 'asc' }
}

function modalFields(p) {
  return [
    { label: 'Poste',       value: p.position },
    { label: 'Âge',         value: p.birth_date ? calcAge(p.birth_date) + ' ans' : '—' },
    { label: 'Né(e) le',    value: p.birth_date ?? '—' },
    { label: 'N° maillot',  value: p.number ? '#' + p.number : '—' },
  ]
}

// Inline pagination button component
const PagBtn = defineComponent({
  props: { disabled: Boolean },
  emits: ['click'],
  setup(props, { slots, emit }) {
    return () => h('button', {
      disabled: props.disabled,
      onClick: () => emit('click'),
      class: 'px-3 py-1.5 rounded-lg text-sm bg-white/5 border border-white/10 text-gray-400 ' +
             'disabled:opacity-30 hover:bg-white/10 transition-colors'
    }, slots.default?.())
  }
})

onMounted(loadApiClubs)
</script>

<style scoped>
.th-sort { @apply px-4 py-3 text-left cursor-pointer hover:text-white transition-colors select-none; }
.modal-enter-active, .modal-leave-active { transition: opacity .2s, transform .2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(.95); }
</style>
