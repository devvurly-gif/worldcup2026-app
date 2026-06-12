import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const KEY = 'wc_admin_pw'

export const useAdminStore = defineStore('admin', () => {
  const password = ref(sessionStorage.getItem(KEY) || '')

  const isLoggedIn = computed(() => !!password.value)

  async function login(pw) {
    const res = await fetch('/api/wc26/squads', {
      headers: { 'X-Admin-Password': pw }
    })
    // Verify by trying an admin-only ping — just check squads endpoint responds
    if (res.ok) {
      password.value = pw
      sessionStorage.setItem(KEY, pw)
      return true
    }
    return false
  }

  function logout() {
    password.value = ''
    sessionStorage.removeItem(KEY)
  }

  function headers() {
    return { 'X-Admin-Password': password.value, 'Content-Type': 'application/json' }
  }

  return { password, isLoggedIn, login, logout, headers }
})
