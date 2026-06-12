import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAdminStore } from '@/stores/admin'

const routes = [
  // ── Auth ───────────────────────────────────────────────────
  { path: '/login',    name: 'login',    component: () => import('@/views/Login.vue'),    meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: () => import('@/views/Register.vue'), meta: { guestOnly: true } },

  // ── Public ─────────────────────────────────────────────────
  { path: '/',             name: 'home',      component: () => import('@/views/Home.vue')       },
  { path: '/groupes',      name: 'groups',    component: () => import('@/views/Groups.vue')      },
  { path: '/calendrier',   name: 'calendar',  component: () => import('@/views/Calendar.vue')    },
  { path: '/match/:id',    name: 'match',     component: () => import('@/views/MatchDetail.vue') },
  { path: '/joueurs',      name: 'joueurs',   component: () => import('@/views/Joueurs.vue')     },
  { path: '/equipe/:code', name: 'team',      component: () => import('@/views/TeamView.vue')    },
  { path: '/maroc',        name: 'morocco',   component: () => import('@/views/Morocco.vue')     },
  { path: '/bracket',      name: 'bracket',   component: () => import('@/views/Bracket.vue')     },
  { path: '/actualites',        name: 'news',        component: () => import('@/views/News.vue')       },
  { path: '/actualites/:slug',  name: 'news-detail', component: () => import('@/views/NewsDetail.vue') },

  // ── Admin Login (legacy password-based) ───────────────────
  {
    path: '/wc-admin',
    name: 'admin-login',
    component: () => import('@/views/admin/AdminLogin.vue'),
  },

  // ── Admin Protected ────────────────────────────────────────
  {
    path: '/admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { requiresAdmin: true },
    children: [
      { path: '',         redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'admin-dashboard', component: () => import('@/views/admin/AdminDashboard.vue') },
      { path: 'teams',     name: 'admin-teams',     component: () => import('@/views/admin/AdminTeams.vue')     },
      { path: 'players',   name: 'admin-players',   component: () => import('@/views/admin/AdminPlayers.vue')   },
      { path: 'scores',    name: 'admin-scores',    component: () => import('@/views/admin/AdminScores.vue')    },
      { path: 'stadiums',  name: 'admin-stadiums',  component: () => import('@/views/admin/AdminStadiums.vue')  },
      { path: 'users',     name: 'admin-users',     component: () => import('@/views/admin/AdminUsers.vue')     },
    ],
  },

  // Legacy /wc-admin children (keep working)
  {
    path: '/wc-admin',
    component: () => import('@/views/admin/AdminLayout.vue'),
    meta: { requiresAdmin: true },
    children: [
      { path: '/',        name: 'wc-admin-dashboard', component: () => import('@/views/admin/AdminDashboard.vue') },
      { path: 'teams',    name: 'wc-admin-teams',     component: () => import('@/views/admin/AdminTeams.vue')     },
      { path: 'players',  name: 'wc-admin-players',   component: () => import('@/views/admin/AdminPlayers.vue')   },
      { path: 'scores',   name: 'wc-admin-scores',    component: () => import('@/views/admin/AdminScores.vue')    },
      { path: 'stadiums', name: 'wc-admin-stadiums',  component: () => import('@/views/admin/AdminStadiums.vue')  },
      { path: 'squads',   name: 'wc-admin-squads',    component: () => import('@/views/AdminSquads.vue')          },
    ],
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to) => {
  const auth  = useAuthStore()
  const admin = useAdminStore()

  if (to.meta.guestOnly && auth.isLoggedIn) {
    return { path: '/' }
  }

  if (to.meta.requiresAdmin) {
    // Accept either Sanctum token OR legacy password auth
    if (!auth.isAdmin && !admin.isLoggedIn) {
      return { name: 'login' }
    }
  }
})

export default router
