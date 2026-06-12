import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api, mapFixture, TEAMS, getTeam, NAME_TO_CODE } from '@/services/api'

export const useAppStore = defineStore('app', () => {

  // ── State ──────────────────────────────────────────────
  const fixtures    = ref([])
  const liveMatches = ref([])
  const standings   = ref({})
  const status      = ref({ msg: '', type: 'idle' }) // idle|loading|ok|live|error
  const quota       = ref({ count: 0, date: '' })
  const lastUpdate  = ref(null)
  let   _pollTimer  = null
  let   _fixturesLoaded = false

  // ── Computed ───────────────────────────────────────────
  const liveCount = computed(() => liveMatches.value.length)

  const todayMatches = computed(() => {
    const today = new Date().toISOString().split('T')[0]
    return fixtures.value.filter(m => m.date === today)
  })

  const upcomingMatches = computed(() => {
    const today = new Date().toISOString().split('T')[0]
    return fixtures.value
      .filter(m => m.date >= today && !m.isLive)
      .sort((a,b) => a.date.localeCompare(b.date) || a.time.localeCompare(b.time))
      .slice(0, 10)
  })

  const groupsFromFixtures = computed(() => {
    const g = {}
    fixtures.value.forEach(m => {
      if (!m.group) return
      if (!g[m.group]) g[m.group] = new Set()
      if (m.homeCode && m.homeCode !== 'TBD') g[m.group].add(m.homeCode)
      if (m.awayCode && m.awayCode !== 'TBD') g[m.group].add(m.awayCode)
    })
    const result = {}
    Object.keys(g).sort().forEach(k => { result[k] = [...g[k]] })
    return result
  })

  const moroccoMatches = computed(() =>
    fixtures.value.filter(m => m.homeCode === 'MAR' || m.awayCode === 'MAR')
  )

  const knockoutMatches = computed(() =>
    fixtures.value.filter(m => m.phase && m.phase !== 'Groupes')
  )

  // ── Actions ────────────────────────────────────────────
  async function loadFixtures() {
    if (_fixturesLoaded) return
    try {
      setStatus('⏳ Chargement du calendrier…', 'loading')
      const res = await api.fixtures()
      if (res.data?.length > 0) {
        fixtures.value = res.data.map(mapFixture)
        _fixturesLoaded = true
        incQuota()
        setStatus(`✅ ${res.data.length} matchs chargés`, 'ok')
      }
    } catch(e) {
      setStatus('⚠️ API hors ligne', 'error')
    }
  }

  async function loadLive() {
    try {
      const res = await api.live()
      liveMatches.value = (res.data ?? []).map(mapFixture)
      incQuota()

      // Merge scores dans fixtures
      liveMatches.value.forEach(lf => {
        const idx = fixtures.value.findIndex(m =>
          m.homeCode === lf.homeCode && m.awayCode === lf.awayCode
        )
        if (idx >= 0) {
          fixtures.value[idx] = { ...fixtures.value[idx], ...lf }
        }
      })
    } catch(e) { /* silencieux */ }
  }

  async function loadStandings() {
    try {
      const res = await api.standings()
      if (res.groups) standings.value = res.groups
      incQuota()
    } catch(e) { /* silencieux */ }
  }

  async function refresh() {
    setStatus('🔄 Actualisation…', 'loading')
    await Promise.allSettled([loadLive(), loadStandings()])
    const now = new Date().toLocaleTimeString('fr-FR')
    lastUpdate.value = now
    const msg = liveCount.value > 0
      ? `🔴 ${liveCount.value} match(s) EN DIRECT — ${now}`
      : `✅ Synchronisé — ${now}`
    setStatus(msg, liveCount.value > 0 ? 'live' : 'ok')
  }

  function startPolling() {
    loadFixtures()
    refresh()
    const delay = () => liveCount.value > 0 ? 30_000 : 1_800_000
    const tick = async () => {
      await refresh()
      _pollTimer = setTimeout(tick, delay())
    }
    _pollTimer = setTimeout(tick, delay())
  }

  function stopPolling() {
    clearTimeout(_pollTimer)
  }

  async function forceRefresh() {
    clearTimeout(_pollTimer)
    await refresh()
    _pollTimer = setTimeout(async function tick() {
      await refresh()
      _pollTimer = setTimeout(tick, liveCount.value > 0 ? 30_000 : 1_800_000)
    }, liveCount.value > 0 ? 30_000 : 1_800_000)
  }

  // ── Helpers ────────────────────────────────────────────
  function setStatus(msg, type) { status.value = { msg, type } }

  function incQuota() {
    const today = new Date().toISOString().split('T')[0]
    if (quota.value.date !== today) quota.value = { count: 0, date: today }
    quota.value.count++
    try { localStorage.setItem('wc26_quota', JSON.stringify(quota.value)) } catch {}
  }

  function loadQuota() {
    try {
      const s = localStorage.getItem('wc26_quota')
      if (s) quota.value = JSON.parse(s)
    } catch {}
  }

  function getStanding(code) {
    for (const grp of Object.values(standings.value)) {
      if (!Array.isArray(grp)) continue
      const row = grp.find(r => {
        const name = r.team?.name ?? ''
        const found = Object.entries(NAME_TO_CODE ?? {}).find(([n]) => n === name)
        return found?.[1] === code
      })
      if (row) return row
    }
    return null
  }

  loadQuota()

  return {
    fixtures, liveMatches, standings, status, quota,
    lastUpdate, liveCount, todayMatches, upcomingMatches,
    groupsFromFixtures, moroccoMatches, knockoutMatches,
    loadFixtures, loadLive, loadStandings, refresh,
    startPolling, stopPolling, forceRefresh, getStanding,
  }
})
