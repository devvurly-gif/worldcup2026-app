<template>
  <div class="min-h-screen flex" style="background:#07071a">

    <!-- Sidebar -->
    <aside class="w-56 shrink-0 flex flex-col border-r border-white/5"
           style="background:#0d0d2b">
      <!-- Brand -->
      <div class="px-5 py-5 border-b border-white/5">
        <div class="flex items-center gap-2">
          <span class="text-2xl">⚽</span>
          <div>
            <p class="text-white font-black text-sm leading-tight">WC 2026</p>
            <p class="text-yellow-400 text-xs font-bold">Admin Panel</p>
          </div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 py-4 px-3 space-y-0.5 overflow-y-auto">
        <NavItem to="/wc-admin/"        icon="fa-gauge"        label="Dashboard" />
        <NavItem to="/wc-admin/teams"   icon="fa-shield-halved" label="Équipes" />
        <NavItem to="/wc-admin/players" icon="fa-users"        label="Joueurs" />
        <NavItem to="/wc-admin/scores"  icon="fa-futbol"       label="Scores" />
        <NavItem to="/wc-admin/stadiums" icon="fa-building"    label="Stades" />
        <NavItem to="/wc-admin/squads"  icon="fa-file-excel"   label="Import XLS" />

        <div class="pt-3 mt-3 border-t border-white/5 text-[11px] text-gray-600 px-3 uppercase tracking-widest">
          Front
        </div>
        <NavItem to="/joueurs"       icon="fa-users"        label="Vue Joueurs" />

        <div class="pt-3 mt-3 border-t border-white/5">
          <NavItem to="/" icon="fa-globe" label="Voir le site" :external="true" />
        </div>
      </nav>

      <!-- User / Logout -->
      <div class="px-3 py-4 border-t border-white/5">
        <button @click="handleLogout"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg
                       text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors text-sm">
          <i class="fas fa-right-from-bracket w-4 text-center"></i>
          <span>Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 flex flex-col min-h-screen overflow-hidden">
      <!-- Top bar -->
      <header class="flex items-center justify-between px-6 py-4 border-b border-white/5"
              style="background:#0d0d2b">
        <div>
          <h1 class="text-white font-bold text-lg">{{ pageTitle }}</h1>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500">
          <i class="fas fa-circle text-green-400 text-xs animate-pulse"></i>
          Admin connecté
        </div>
      </header>

      <!-- Page content -->
      <div class="flex-1 overflow-y-auto p-6">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminStore } from '@/stores/admin'
import NavItem from './components/NavItem.vue'

const route  = useRoute()
const router = useRouter()
const admin  = useAdminStore()

const pageTitles = {
  'admin-dashboard': 'Dashboard',
  'admin-teams':     'Gestion des Équipes',
  'admin-players':   'Gestion des Joueurs',
  'admin-scores':    'Gestion des Scores',
  'admin-stadiums':  'Stades',
  'admin-squads':    'Import Effectifs XLS',
}

const pageTitle = computed(() => pageTitles[route.name] ?? 'Admin')

function handleLogout() {
  admin.logout()
  router.push({ name: 'admin-login' })
}
</script>
