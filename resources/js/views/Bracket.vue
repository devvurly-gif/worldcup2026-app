<template>
  <div class="space-y-8">

    <div class="flex items-center justify-between mb-2">
      <h1 class="section-title mb-0">
        <i class="fas fa-trophy text-yellow-400"></i>
        Bracket — Phase Éliminatoire
      </h1>
      <span class="text-xs text-gray-600 glass px-3 py-1.5 rounded-lg">
        32 équipes · 5 tours · 32 matchs
      </span>
    </div>

    <!-- Notice avant tournoi -->
    <div class="card p-5 border-yellow-500/20 border bg-yellow-500/5 flex items-start gap-3">
      <i class="fas fa-info-circle text-yellow-400 mt-0.5"></i>
      <div>
        <p class="text-yellow-400 font-bold text-sm">Bracket disponible dès le 2 Juillet 2026</p>
        <p class="text-gray-500 text-xs mt-1">
          Les 32 qualifiés (1ers et 2emes des 12 groupes + 8 meilleurs 3emes) seront connus à la fin de la phase de groupes.
          Le bracket sera mis à jour automatiquement en temps réel.
        </p>
      </div>
    </div>

    <!-- Bracket visuel horizontal scroll -->
    <div class="overflow-x-auto pb-4">
      <div class="min-w-[900px]">

        <!-- Légende des rounds -->
        <div class="grid grid-cols-5 text-center mb-4">
          <div v-for="r in rounds" :key="r.label" class="text-xs font-black text-yellow-400 uppercase tracking-widest">
            {{ r.label }}
          </div>
        </div>

        <!-- Bracket grid -->
        <div class="relative grid grid-cols-5 gap-0 items-center" style="min-height:900px">

          <!-- Round of 32 — 16 matchs col 1 -->
          <div class="flex flex-col justify-around h-full gap-1 px-2">
            <BracketSlot v-for="(m, i) in r32" :key="'r32-'+i" :match="m" :round="'R32'" />
          </div>

          <!-- R16 — 8 matchs col 2 -->
          <div class="flex flex-col justify-around h-full gap-1 px-2">
            <BracketSlot v-for="(m, i) in r16" :key="'r16-'+i" :match="m" :round="'R16'" />
          </div>

          <!-- QF — 4 matchs col 3 -->
          <div class="flex flex-col justify-around h-full gap-1 px-2">
            <BracketSlot v-for="(m, i) in qf" :key="'qf-'+i" :match="m" :round="'QF'" />
          </div>

          <!-- SF — 2 matchs col 4 -->
          <div class="flex flex-col justify-around h-full gap-1 px-2">
            <BracketSlot v-for="(m, i) in sf" :key="'sf-'+i" :match="m" :round="'SF'" />
          </div>

          <!-- Finale col 5 -->
          <div class="flex flex-col justify-center h-full gap-4 px-2">
            <div class="text-center mb-2">
              <span class="text-xs font-black text-yellow-400 uppercase tracking-widest">🏆 Finale</span>
            </div>
            <BracketSlot :match="final" round="F" :highlight="true" />
            <div class="mt-4 text-center">
              <span class="text-xs text-gray-600 uppercase tracking-widest">3ème Place</span>
            </div>
            <BracketSlot :match="thirdPlace" round="3P" />
          </div>

        </div>
      </div>
    </div>

    <!-- Légende -->
    <div class="card p-5">
      <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Légende</p>
      <div class="flex flex-wrap gap-4 text-xs">
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded bg-green-500/30 border border-green-500"></span>
          <span class="text-gray-400">Qualifié</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded bg-red-500/30 border border-red-500"></span>
          <span class="text-gray-400">Éliminé</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded bg-yellow-500/30 border border-yellow-500"></span>
          <span class="text-gray-400">En cours</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="w-3 h-3 rounded bg-gray-800 border border-gray-700"></span>
          <span class="text-gray-400">À venir</span>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useSeoMeta } from '@/composables/useSeoMeta'
useSeoMeta({ title: 'Tableau de la Phase Finale – Mondial 2026', description: 'Tableau complet de la phase finale de la Coupe du Monde FIFA 2026.', path: '/bracket' })
import { computed, defineComponent, h } from 'vue'
import { useAppStore } from '@/stores/app'
import { getTeam } from '@/services/api'
import { RouterLink } from 'vue-router'
import { flagImg } from '@/utils/flag'

const store = useAppStore()

// Bracket slot component inline
const BracketSlot = defineComponent({
  props: {
    match: { type: Object, default: null },
    round: { type: String, default: '' },
    highlight: { type: Boolean, default: false }
  },
  setup(props) {
    return () => {
      const m = props.match
      const baseClass = `card p-3 text-xs ${props.highlight ? 'border border-yellow-500/40 bg-yellow-500/5' : ''}`

      if (!m) {
        // Placeholder vide
        return h('div', { class: baseClass + ' opacity-40' }, [
          h('div', { class: 'flex items-center gap-2 py-1 border-b border-white/5' }, [
            h('span', { class: 'w-5 h-5 rounded bg-gray-800' }),
            h('span', { class: 'flex-1 bg-gray-800 h-3 rounded' }),
          ]),
          h('div', { class: 'flex items-center gap-2 py-1 mt-1' }, [
            h('span', { class: 'w-5 h-5 rounded bg-gray-800' }),
            h('span', { class: 'flex-1 bg-gray-800 h-3 rounded' }),
          ]),
        ])
      }

      const homeTeam = getTeam(m.homeCode)
      const awayTeam = getTeam(m.awayCode)
      const hasScore = m.scoreHome !== null && m.scoreHome !== undefined

      const teamRow = (team, name, score, isWinner) =>
        h('div', {
          class: `flex items-center gap-2 py-1 ${isWinner ? 'text-yellow-400 font-bold' : 'text-gray-400'}`
        }, [
          h(RouterLink, {
            to: team?.code ? `/equipe/${team.code}` : '#',
            class: 'flex items-center gap-1.5 flex-1 min-w-0 hover:text-yellow-400 transition-colors',
            onClick: (e) => e.stopPropagation(),
          }, [
            h('img', { src: flagImg(team?.code), alt: team?.code ?? '', class: 'w-6 h-4 object-cover rounded-sm shrink-0' }),
            h('span', { class: 'flex-1 truncate text-[11px]' }, team?.name ?? name ?? '?'),
          ]),
          hasScore ? h('span', {
            class: `font-black text-sm ml-1 ${isWinner ? 'text-yellow-400' : 'text-gray-600'}`
          }, String(score)) : null,
        ])

      const homeWins = hasScore && m.scoreHome > m.scoreAway
      const awayWins = hasScore && m.scoreAway > m.scoreHome

      return h(RouterLink, {
        to: `/match/${m.id}`,
        class: baseClass + ' block glass-hover'
      }, [
        teamRow(homeTeam, m.homeName, m.scoreHome, homeWins),
        h('div', { class: 'border-t border-white/5' }),
        teamRow(awayTeam, m.awayName, m.scoreAway, awayWins),
        m.isLive ? h('div', { class: 'mt-1 text-center' }, [
          h('span', { class: 'badge-live text-[9px]' }, `LIVE ${m.elapsed}'`)
        ]) : null,
        m.status === 'FT' && !m.isLive ? h('div', { class: 'text-center text-[9px] text-gray-600 mt-0.5' }, 'Terminé') : null,
      ])
    }
  }
})

const rounds = [
  { label: 'Huitièmes (R32)' },
  { label: 'Quarts (R16)' },
  { label: 'Demi-finales' },
  { label: 'Finale' },
  { label: 'Champion' },
]

// Knockout matches depuis le store
const knockouts = computed(() => store.knockoutMatches)

const r32 = computed(() => {
  const ms = knockouts.value.filter(m => m.phase === 'Huitièmes' || m.round === 'Round of 32')
  return padArray(ms, 16)
})
const r16 = computed(() => {
  const ms = knockouts.value.filter(m => m.phase === 'Quarts' || m.round === 'Quarter-final')
  return padArray(ms, 8)
})
const qf = computed(() => {
  const ms = knockouts.value.filter(m => m.phase === 'Demi-finale' || m.round === 'Semi-final')
  return padArray(ms, 4)
})
const sf = computed(() => {
  const ms = knockouts.value.filter(m => m.phase === 'Finale' || m.round === 'Final')
  return padArray(ms, 2)
})
const final = computed(() => {
  return knockouts.value.find(m => m.round === 'Final' || (m.phase === 'Finale' && !m.isThirdPlace)) ?? null
})
const thirdPlace = computed(() => {
  return knockouts.value.find(m => m.isThirdPlace || m.round === '3rd Place Final') ?? null
})

function padArray(arr, len) {
  const res = [...arr]
  while (res.length < len) res.push(null)
  return res.slice(0, len)
}
</script>

