<template>
  <div class="space-y-8">

    <!-- Hero Maroc -->
    <div class="card overflow-hidden">
      <div class="relative" style="background:linear-gradient(135deg,#1a0a0a 0%,#3d0f0f 40%,#2d1a0a 100%)">
        <div class="absolute inset-0 opacity-10"
             style="background: radial-gradient(ellipse at 30% 50%, #C1272D 0%, transparent 60%)"></div>
        <div class="relative z-10 p-8 flex flex-col md:flex-row items-center gap-8">
          <!-- Flag + info -->
          <div class="text-center">
            <img :src="flagImg('MAR')" alt="MAR" class="w-32 h-24 object-cover rounded-2xl mx-auto mb-3 shadow-lg" />
            <div class="text-xs text-gray-500 uppercase tracking-widest">FIFA Ranking</div>
            <div class="text-4xl font-black text-yellow-400">#14</div>
          </div>
          <div class="flex-1">
            <h1 class="text-4xl md:text-5xl font-black text-white mb-2">
              Les Lions de l'Atlas
            </h1>
            <p class="text-red-400 font-bold text-lg mb-4 flex items-center gap-2">
              <img :src="flagImg('MAR')" alt="MAR" class="w-6 h-4 object-cover rounded-sm" />
              Maroc · Groupe C · CAF
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div class="glass rounded-xl p-3 text-center">
                <div class="text-xs text-gray-500">Entraîneur</div>
                <div class="text-sm font-bold text-white mt-1">M. Ouahbi</div>
              </div>
              <div class="glass rounded-xl p-3 text-center">
                <div class="text-xs text-gray-500">Meilleur résultat</div>
                <div class="text-sm font-bold text-yellow-400 mt-1">Demi-finale 2022</div>
              </div>
              <div class="glass rounded-xl p-3 text-center">
                <div class="text-xs text-gray-500">Groupe C avec</div>
                <div class="flex items-center justify-center gap-1.5 mt-1">
                  <img :src="flagImg('BRA')" alt="BRA" class="w-6 h-4 object-cover rounded-sm" />
                  <img :src="flagImg('HAI')" alt="HAI" class="w-6 h-4 object-cover rounded-sm" />
                  <img :src="flagImg('SCO')" alt="SCO" class="w-6 h-4 object-cover rounded-sm" />
                </div>
              </div>
              <div class="glass rounded-xl p-3 text-center">
                <div class="text-xs text-gray-500">Confédération</div>
                <div class="text-sm font-bold text-white mt-1">CAF · Afrique</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Onglets -->
    <div class="flex gap-2 border-b border-white/10">
      <button v-for="tab in tabs" :key="tab"
              @click="activeTab = tab"
              class="px-4 py-2.5 text-sm font-semibold transition-colors border-b-2 -mb-px"
              :class="activeTab === tab
                ? 'text-yellow-400 border-yellow-400'
                : 'text-gray-500 border-transparent hover:text-gray-300'">
        {{ tab }}
      </button>
    </div>

    <!-- Matchs du Maroc -->
    <div v-if="activeTab === 'Matchs'">
      <div v-if="store.moroccoMatches.length" class="space-y-3">
        <RouterLink v-for="m in store.moroccoMatches" :key="m.id"
                    :to="`/match/${m.id}`"
                    class="card p-5 glass-hover flex items-center gap-4">
          <div class="text-sm text-gray-500 w-28 flex-shrink-0">
            <div class="font-bold text-yellow-400">{{ formatDate(m.date) }}</div>
            <div>{{ m.time }}</div>
            <div class="text-xs text-gray-700 mt-0.5">{{ m.city }}</div>
          </div>
          <div class="flex-1 flex items-center justify-center gap-4">
            <div class="text-center">
              <img :src="flagImg(m.homeCode)" :alt="m.homeCode" class="w-10 h-7 object-cover rounded mx-auto" />
              <TeamLink :code="m.homeCode" :name="m.homeName" :show-flag="false"
                        :cls="'text-sm font-bold justify-center mt-1 ' + (m.homeCode==='MAR' ? 'text-red-400' : '')"
                        @click.stop />
            </div>
            <div class="text-center w-20">
              <div v-if="m.scoreHome !== null"
                   class="text-2xl font-black"
                   :class="m.isLive ? 'text-red-400' : 'text-yellow-400'">
                {{ m.scoreHome }} — {{ m.scoreAway }}
              </div>
              <div v-else class="text-gray-600 font-black">VS</div>
              <div v-if="m.isLive" class="badge-live mt-1 text-[10px]">LIVE {{ m.elapsed }}'</div>
              <div v-else-if="m.status==='FT'" class="text-xs text-gray-500">FT</div>
            </div>
            <div class="text-center">
              <img :src="flagImg(m.awayCode)" :alt="m.awayCode" class="w-10 h-7 object-cover rounded mx-auto" />
              <TeamLink :code="m.awayCode" :name="m.awayName" :show-flag="false"
                        :cls="'text-sm font-bold justify-center mt-1 ' + (m.awayCode==='MAR' ? 'text-red-400' : '')"
                        @click.stop />
            </div>
          </div>
          <!-- Résultat Maroc -->
          <div class="w-16 text-center flex-shrink-0">
            <span v-if="marResult(m)"
                  class="text-xs font-black px-2.5 py-1 rounded-full"
                  :class="{
                    'bg-green-500/20 text-green-400': marResult(m) === 'V',
                    'bg-red-500/20 text-red-400':     marResult(m) === 'D',
                    'bg-gray-500/20 text-gray-400':   marResult(m) === 'N',
                  }">
              {{ marResult(m) === 'V' ? 'Victoire' : marResult(m) === 'D' ? 'Défaite' : 'Nul' }}
            </span>
          </div>
        </RouterLink>
      </div>
      <div v-else class="card p-10 text-center text-gray-600">
        <span class="text-5xl block mb-4">🇲🇦</span>
        Chargement du calendrier…
      </div>
    </div>

    <!-- Joueurs -->
    <div v-if="activeTab === 'Joueurs'">
      <div v-if="playersLoading" class="flex justify-center py-12">
        <i class="fas fa-circle-notch animate-spin text-yellow-400 text-2xl"></i>
      </div>
      <div v-else-if="players.length">
        <!-- Par position -->
        <div v-for="pos in positions" :key="pos" class="mb-6">
          <h3 class="text-sm font-black text-yellow-400 uppercase tracking-widest mb-3 flex items-center gap-2">
            <span>{{ posIcon(pos) }}</span> {{ pos }}
          </h3>
          <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-3">
            <div v-for="p in playersByPos(pos)" :key="p.id"
                 class="card p-4 flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center
                          text-yellow-400 font-black text-sm flex-shrink-0">
                {{ p.number ?? '—' }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-bold text-white text-sm truncate">{{ p.name }}</div>
                <div class="text-xs text-gray-500">{{ p.age ? p.age + ' ans' : '' }}</div>
              </div>
              <div class="text-right text-xs space-y-0.5">
                <div v-if="p.goals" class="text-yellow-400">⚽ {{ p.goals }}</div>
                <div v-if="p.rating" class="text-green-400">★ {{ p.rating }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else-if="playersError" class="card p-8 text-center text-red-400">
        <i class="fas fa-circle-exclamation text-3xl mb-3 block"></i>
        {{ playersError }}
      </div>
      <div v-else class="card p-8 text-center text-gray-600">Aucun joueur disponible</div>
    </div>

    <!-- Stats Groupe C -->
    <div v-if="activeTab === 'Groupe C'">
      <div class="card overflow-hidden">
        <div class="hero-bg px-5 py-4">
          <span class="text-yellow-400 font-black text-xl">GROUPE C</span>
        </div>
        <div class="divide-y divide-white/5">
          <div v-for="code in ['BRA','MAR','HAI','SCO']" :key="code"
               class="flex items-center px-5 py-4 gap-3"
               :class="code === 'MAR' ? 'bg-red-500/5' : ''">
            <TeamLink :code="code" :show-name="false" flag-size="text-2xl" />
            <TeamLink :code="code" :show-flag="false"
                      :cls="'flex-1 font-bold ' + (code === 'MAR' ? 'text-red-400' : '')" />
            <span class="text-xs text-gray-500">FIFA #{{ getTeam(code)?.ranking }}</span>
            <div class="flex gap-3 text-xs text-gray-600 font-mono">
              <span>0 J</span><span>0 G</span><span>0 N</span><span>0 P</span>
              <span class="font-black text-white">0 Pts</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { api, getTeam } from '@/services/api'
import TeamLink from '@/components/TeamLink.vue'
import { flagImg } from '@/utils/flag'

const store = useAppStore()
const activeTab = ref('Matchs')
const tabs = ['Matchs', 'Joueurs', 'Groupe C']

const players = ref([])
const playersLoading = ref(false)
const playersError   = ref('')
const positions = ['Gardien', 'Défenseur', 'Milieu', 'Attaquant']

function posIcon(p) {
  return { Gardien:'🧤', Défenseur:'🛡️', Milieu:'⚡', Attaquant:'⚽' }[p] ?? '👤'
}
function playersByPos(pos) {
  return players.value.filter(p => p.position === pos)
}

async function loadPlayers() {
  if (players.value.length) return
  playersLoading.value = true
  playersError.value = ''
  try {
    const res = await api.teamPlayers('MAR')
    players.value = res.squad ?? []
    if (!players.value.length) playersError.value = 'Aucun joueur disponible pour le moment.'
  } catch(e) {
    playersError.value = `Erreur : ${e.message}`
  } finally {
    playersLoading.value = false
  }
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', { day:'numeric', month:'short' })
}

function marResult(m) {
  if (m.scoreHome === null || m.scoreHome === undefined) return null
  const isHome = m.homeCode === 'MAR'
  const ms = isHome ? m.scoreHome : m.scoreAway
  const os = isHome ? m.scoreAway : m.scoreHome
  if (ms > os) return 'V'
  if (ms < os) return 'D'
  return 'N'
}

onMounted(() => loadPlayers())
</script>
