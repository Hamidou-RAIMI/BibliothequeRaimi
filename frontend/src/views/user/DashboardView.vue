<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

onMounted(async () => {
  // Vérifier si l'utilisateur est déjà connecté au chargement
  if (!authStore.user) {
    await authStore.fetchUser()
  }
})

// const handleLogout = async () => {
//   await authStore.logout()
//   router.push('/login')
// }
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900"> HighFive Bibliothèque</h1>
        <div v-if="authStore.isAuthenticated" class="text-gray-600">
          <span class="font-semibold">{{ authStore.user.name }}</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-12">
      <div v-if="authStore.isAuthenticated" class="space-y-6">
        <!-- Welcome Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 border-l-4 border-blue-600">
          <h2 class="text-2xl font-bold text-gray-800 mb-2">
            Bienvenue {{ authStore.user.name }} 
          </h2>
          <p class="text-gray-600 mb-6">
            Vous êtes connecté avec succès. Explorez votre bibliothèque.
          </p>
          
          <div class="space-y-2 text-sm">
            <p><span class="font-semibold text-gray-700">Email:</span> <code class="bg-gray-100 px-2 py-1 rounded">{{ authStore.user.email }}</code></p>
            <p v-if="authStore.user.email_verified_at"><span class="font-semibold text-gray-700">Email vérifié:</span> <span class="text-green-600 font-semibold">✓</span></p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <h3 class="font-bold text-gray-800 mb-2">Mes Livres</h3>
            <p class="text-gray-600 text-sm">Gérez votre collection de livres</p>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <h3 class="font-bold text-gray-800 mb-2">Catégories</h3>
            <p class="text-gray-600 text-sm">Organisez vos lectures par thème</p>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <h3 class="font-bold text-gray-800 mb-2">Auteurs</h3>
            <p class="text-gray-600 text-sm">Explorez vos auteurs préférés</p>
          </div>
        </div>

        <!-- Logout Button -->
        <!-- <div class="flex justify-end">
          <button 
            @click="handleLogout"
            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition"
          >
            Déconnexion
          </button>
        </div> -->
      </div>
      <!-- Not Authenticated -->
      <div v-else class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">connectez vous pour lire</h2>
        <p class="text-gray-600 mb-6">
          Veuillez vous connecter pour accéder à votre bibliothèque.
        </p>
        
        <router-link 
          to="/login"
          class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition"
        >
          Se connecter →
        </router-link>
      </div>
    </main>
  </div>
</template>

