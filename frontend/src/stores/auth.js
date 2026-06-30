import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import client, { BASE_URL } from '@/api/client'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const loadingInitial = ref(true) // Nouvel état : chargement initial
  const error = ref(null)

  const isAuthenticated = computed(() => !!user.value)

  // Récupérer le token CSRF (étape 1 de Sanctum)
  const fetchCsrfToken = async () => {
    try {
      await client.get(`${BASE_URL}/sanctum/csrf-cookie`)
    } catch (err) {
      console.error('Erreur CSRF:', err)
    }
  }

  // Login
  const login = async (email, password) => {
    loading.value = true
    error.value = null
    try {
      await fetchCsrfToken()
      const response = await client.post('/login', { email, password })
      user.value = response.data.user
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur de connexion'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Register
  const register = async (firstName, lastName, email, phone, password, passwordConfirmation) => {
    loading.value = true
    error.value = null
    try {
      await fetchCsrfToken()
      const response = await client.post('/register', {
        first_name: firstName,
        last_name: lastName,
        email,
        phone,
        password,
        password_confirmation: passwordConfirmation,
      })
      user.value = response.data.user
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur d\'inscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Logout
  const logout = async () => {
    loading.value = true
    try {
      await client.post('/logout')
      user.value = null
    } catch (err) {
      console.error('Erreur logout:', err)
    } finally {
      loading.value = false
    }
  }

  // Récupérer l'utilisateur actuel
  const fetchUser = async () => {
    try {
      const response = await client.get('/user')
      user.value = response.data
    } catch (err) {
      user.value = null
    } finally {
      loadingInitial.value = false
    }
  }

  return {
    user,
    loading,
    loadingInitial,
    error,
    isAuthenticated,
    login,
    register,
    logout,
    fetchUser,
  }
})
