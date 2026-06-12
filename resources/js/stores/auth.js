import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('wc26_user') || 'null'))
  const token = ref(localStorage.getItem('wc26_token') || null)

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isEditor   = computed(() => ['admin', 'editor'].includes(user.value?.role))

  function authHeaders() {
    return token.value ? { Authorization: `Bearer ${token.value}` } : {}
  }

  async function login(email, password) {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    if (!res.ok) {
      const err = await res.json()
      throw new Error(err.message || Object.values(err.errors || {})[0]?.[0] || 'Erreur de connexion')
    }
    const data = await res.json()
    setSession(data.user, data.token)
    return data.user
  }

  async function register(name, email, password, password_confirmation) {
    const res = await fetch('/api/auth/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password, password_confirmation }),
    })
    if (!res.ok) {
      const err = await res.json()
      const msg = err.message || Object.values(err.errors || {})[0]?.[0] || 'Erreur inscription'
      throw new Error(msg)
    }
    const data = await res.json()
    setSession(data.user, data.token)
    return data.user
  }

  async function logout() {
    if (token.value) {
      await fetch('/api/auth/logout', {
        method: 'POST',
        headers: authHeaders(),
      }).catch(() => {})
    }
    clearSession()
  }

  async function fetchProfile() {
    if (!token.value) return
    const res = await fetch('/api/auth/profile', { headers: authHeaders() })
    if (res.ok) {
      user.value = await res.json()
      localStorage.setItem('wc26_user', JSON.stringify(user.value))
    } else {
      clearSession()
    }
  }

  function setSession(u, t) {
    user.value  = u
    token.value = t
    localStorage.setItem('wc26_user',  JSON.stringify(u))
    localStorage.setItem('wc26_token', t)
  }

  function clearSession() {
    user.value  = null
    token.value = null
    localStorage.removeItem('wc26_user')
    localStorage.removeItem('wc26_token')
  }

  return { user, token, isLoggedIn, isAdmin, isEditor, authHeaders, login, register, logout, fetchProfile }
})
