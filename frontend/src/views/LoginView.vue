<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const errorMsg = ref('')

const handleLogin = async () => {
  errorMsg.value = ''
  try {
    await authStore.login(email.value, password.value)
    // Redirect to correct dashboard based on role
    const role = authStore.user.role
    switch(role) {
      case 'admin':
        router.push('/admin/dashboard')
        break
      case 'responsable_d':
        router.push('/responsable_d/dashboard')
        break
      case 'responsable_rh':
        router.push('/responsable_rh/dashboard')
        break
      case 'user':
        router.push('/user/dashboard')
        break
      default:
        router.push('/')
    }
  } catch (err) {
    errorMsg.value = authStore.error
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
      <h1 class="text-3xl font-bold text-gray-800 mb-2 text-center">Login</h1>
      <p class="text-gray-600 text-center mb-8">Connectez-vous à votre compte</p>
      
      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input 
            v-model="email" 
            type="email" 
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="test@example.com"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
          <input 
            v-model="password" 
            type="password" 
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="password123"
          />
        </div>

        <button 
          :disabled="authStore.loading"
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2 px-4 rounded-lg transition"
        >
          {{ authStore.loading ? 'Connexion...' : 'Se connecter' }}
        </button>
      </form>

      <p v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
        {{ errorMsg }}
      </p>

      <p class="mt-6 text-center text-gray-600">
        Pas encore inscrit? 
        <router-link to="/register" class="text-blue-600 hover:underline font-semibold">
          S'inscrire
        </router-link>
      </p>
    </div>
  </div>
</template>
