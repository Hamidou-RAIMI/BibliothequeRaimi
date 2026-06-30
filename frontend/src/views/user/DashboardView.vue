<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useReferenceStore } from '@/stores/reference'
import { useCategoryStore } from '@/stores/category'
import client from '@/api/client'

const router = useRouter()
const authStore = useAuthStore()
const referenceStore = useReferenceStore()
const categoryStore = useCategoryStore()

const selectedCategory = ref(null)
const searchQuery = ref('')
const loading = ref(true)

const filteredReferences = computed(() => {
  let refs = referenceStore.references
  
  if (selectedCategory.value) {
    refs = refs.filter(ref => ref.category_id === selectedCategory.value)
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    refs = refs.filter(ref => 
      ref.title.toLowerCase().includes(query) ||
      (ref.subtitle && ref.subtitle.toLowerCase().includes(query)) ||
      (ref.isbn && ref.isbn.includes(query))
    )
  }
  
  return refs
})

const loadData = async () => {
  try {
    loading.value = true
    const [categoriesRes, referencesRes] = await Promise.all([
      authStore.isAuthenticated ? client.get('/categories') : client.get('/public/categories'),
      authStore.isAuthenticated ? client.get('/references') : client.get('/public/references'),
    ])
    categoryStore.categories = categoriesRes.data.data
    referenceStore.references = referencesRes.data.data
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
  } finally {
    loading.value = false
  }
}

const goToReference = (referenceId) => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
  } else if (authStore.user?.status !== 'active') {
    // If authenticated but not active, maybe show a message
    alert('Votre compte doit être activé pour voir les détails des références.')
  } else {
    router.push(`/references/${referenceId}`)
  }
}

onMounted(async () => {
  if (!authStore.user && authStore.isAuthenticated) {
    await authStore.fetchUser()
  }
  loadData()
})
</script>

<template>
  <div class="min-h-screen bg-stone-100">
    <!-- Hero Section pour les non connectés -->
    <section v-if="!authStore.isAuthenticated" class="bg-gradient-to-br from-stone-800 to-stone-900 text-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <span class="text-amber-400 font-semibold">Bienvenue</span>
          <h1 class="text-4xl font-bold mt-4 mb-6">HighFive Bibliothèque</h1>
          <p class="text-stone-300 text-lg mb-8 max-w-2xl mx-auto">
            Explorez notre catalogue de références. Connectez-vous pour accéder aux détails et profiter de toutes les fonctionnalités.
          </p>
          <router-link 
            to="/login"
            class="bg-amber-700 hover:bg-amber-800 text-white px-8 py-3 rounded-lg font-semibold transition"
          >
            Se connecter
          </router-link>
        </div>
      </div>
    </section>

    <!-- Header pour connectés -->
    <header v-else class="bg-with-600 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl text-amber-800 font-bold mb-2">Bienvenue, {{ authStore.user?.first_name }} !</h1>
        <p class="text-gray-500">Explorez le catalogue complet</p>
        
        <div v-if="authStore.user?.status !== 'active'" class="mt-4 bg-amber-500  border border-yellow-500/50 rounded-lg p-4">
          <p class="text-yellow-200 text-sm " >
            ⚠️ Votre compte est en attente d'activation. Vous pouvez parcourir le catalogue, mais pas accéder aux détails.
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
          <router-link 
            to="/user/demandes" 
            class="bg-white/10 backdrop-blur rounded-xl p-5 hover:bg-white/20 transition-all"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-amber-700 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-amber-700">Mes Demandes</h3>
                <p class="text-gray-800 text-sm">Gérer vos dépôts</p>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </header>

    <!-- Catalogue Section (pour TOUS les utilisateurs) -->
    <section class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-stone-800 mb-8">
          {{ authStore.isAuthenticated ? 'Catalogue Complet' : 'Parcourir le Catalogue' }}
        </h2>
        
        <!-- Search Bar -->
        <div class="mb-8">
          <div class="relative max-w-2xl">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par titre, sous-titre ou ISBN..."
              class="w-full px-6 py-4 rounded-full border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition shadow-sm"
            />
          </div>
        </div>
        
        <!-- Categories Filter -->
        <div class="flex gap-3 mb-10 flex-wrap">
          <button
            @click="selectedCategory = null"
            :class="selectedCategory === null ? 'bg-amber-700 text-white' : 'bg-white text-stone-700 border hover:bg-stone-50'"
            class="px-4 py-2 rounded-full text-sm cursor-pointer transition font-medium"
          >
            Tous
          </button>
          <button
            v-for="category in categoryStore.categories"
            :key="category.id"
            @click="selectedCategory = category.id"
            :class="selectedCategory === category.id ? 'bg-amber-700 text-white' : 'bg-white text-stone-700 border hover:bg-stone-50'"
            class="px-4 py-2 rounded-full text-sm cursor-pointer transition font-medium"
          >
            {{ category.name }}
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-700"></div>
        </div>

        <!-- No Results -->
        <div v-else-if="filteredReferences.length === 0" class="bg-white rounded-xl shadow p-12 text-center">
          <h3 class="text-xl font-semibold text-stone-700 mb-2">Aucune référence trouvée</h3>
          <p class="text-stone-500">Essayez de modifier vos filtres</p>
        </div>

        <!-- References Grid -->
        <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <div
            v-for="reference in filteredReferences"
            :key="reference.id"
            @click="goToReference(reference.id)"
            class="bg-white rounded-xl overflow-hidden shadow hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:-translate-y-2 relative group"
          >
            <!-- Badges -->
            <div v-if="reference.is_new" class="absolute top-3 left-3 z-10 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow">
              Nouveau
            </div>
            
            <div v-if="authStore.isAuthenticated" 
              :class="reference.is_available ? 'bg-green-500' : 'bg-red-500'"
              class="absolute top-3 right-3 z-10 text-white px-2 py-1 rounded-full text-xs font-semibold shadow"
            >
              {{ reference.is_available ? 'Dispo' : 'Indispo' }}
            </div>
            
            <div class="relative overflow-hidden">
              <img
                :src="reference.cover_image_url"
                :alt="reference.title"
                class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
            
            <div class="p-4">
              <div v-if="reference.categories && reference.categories.length > 0" class="flex flex-wrap gap-1 mb-2">
                <span 
                  v-for="(cat, idx) in reference.categories.slice(0, 2)" 
                  :key="cat.id"
                  class="bg-stone-100 text-stone-600 px-2 py-0.5 rounded text-xs"
                >
                  {{ cat.name }}
                </span>
              </div>
              
              <h3 class="font-semibold text-stone-800 mb-1 line-clamp-2">{{ reference.title }}</h3>
              <p class="text-stone-500 text-sm mb-2 line-clamp-1">
                {{ reference.authors && reference.authors.length > 0 
                  ? reference.authors.map(a => `${a.first_name} ${a.last_name}`).join(', ') 
                  : 'Auteur inconnu' 
                }}
              </p>
              <div class="flex items-center gap-1 text-amber-500 text-sm">
                <span class="font-bold">{{ authStore.isAuthenticated && authStore.user?.status === 'active' ? 'Voir détails' : 'En attente d activation' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
