<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const handleLoginClick = () => {
  router.push('/login')
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/')
}

const handleNavigateHome = () => {
  if (authStore.isAuthenticated) {
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
  } else {
    router.push('/')
  }
}
</script>

<template>
  <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
      <div @click="handleNavigateHome" class="flex items-center gap-2 cursor-pointer">
        <i class="pi pi-book text-amber-700 text-2xl"></i>
        <span class="text-xl font-bold text-stone-800">Bibliothèque</span>
      </div>
      <nav class="hidden md:flex gap-8">
        <a href="#" class="text-stone-600 hover:text-amber-700">Accueil</a>
        <a href="#" class="text-stone-600 hover:text-amber-700">Catalogue</a>
        <a href="#" class="text-stone-600 hover:text-amber-700">À propos</a>
      </nav>
      <div v-if="authStore.isAuthenticated" class="flex items-center gap-4">
        <span class="text-stone-700">{{ authStore.user?.name || authStore.user?.email }}</span>
        <button @click="handleLogout" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
          Se déconnecter
        </button>
      </div>
      <button v-else @click="handleLoginClick" class="bg-amber-700 text-white px-4 py-2 rounded-lg hover:bg-amber-800 transition">
        Se connecter
      </button>
    </div>
  </header>
</template>

<style scoped></style>
