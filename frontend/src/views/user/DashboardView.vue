<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useReferenceStore } from '@/stores/reference'
import { useCategoryStore } from '@/stores/category'
import client from '@/api/client'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'


const router = useRouter()
const authStore = useAuthStore()
const referenceStore = useReferenceStore()
const categoryStore = useCategoryStore()
const toast = useToast()

const selectedCategory = ref(null)
const searchQuery = ref('')
const loading = ref(true)
const selectedReferenceForSummary = ref(null)



// Fonction pour réinitialiser les filtres
const resetFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = null
}

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

const openSummary = (reference) => {
  selectedReferenceForSummary.value = reference
}
const closeSummary = () => {
  selectedReferenceForSummary.value = null
}



const goToReference = (referenceId) => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
  } else if (authStore.user?.status !== 'active') {
    toast.add({
      severity: 'warn',
      summary: 'Compte en attente',
      detail: 'Votre compte doit être activé pour voir les détails des références.',
      life: 3000
    })
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
<section v-if="!authStore.isAuthenticated" class="bg-gradient-to-br from-stone-800 to-stone-900 text-white min-h-screen flex items-center relative py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      
      <!-- Colonne Gauche : Contenu éditorial et accroche -->
      <div class="space-y-6">
        <div class="inline-flex items-center gap-2 bg-stone-700/50 border border-stone-600 px-3 py-1 rounded-full text-amber-400 text-sm font-semibold tracking-wide w-fit">
           Bienvenue
        </div>
        
        <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">
          HighFive <span class="text-amber-500">Bibliothèque</span>
        </h1>
        
        <p class="text-stone-300 text-lg leading-relaxed">
          Votre portail vers la connaissance ! Explorez gratuitement notre catalogue complet d'ouvrages, d'articles et de mémoires classés par catégorie. Vous pouvez lire le résumé de chaque référence librement, sans avoir besoin de vous connecter.
        </p>

        <div class="bg-stone-800/60 border border-stone-700/50 p-6 rounded-2xl shadow-inner space-y-3">
          <h2 class="font-semibold text-amber-400">Prêt à aller plus loin ?</h2>
          <p class="text-stone-400 text-sm leading-relaxed">
            Créez votre compte ou connectez-vous pour accéder aux détails complets des documents, proposer de nouveaux dépôts de références et suivre vos demandes en direct. Rejoignez notre communauté !
          </p>
        </div>
      </div>

      <!-- Colonne Droite : Appel à l'action (CTA) -->
      <div class="flex flex-col items-center justify-center bg-stone-800/40 border border-stone-700 p-10 rounded-3xl backdrop-blur-sm text-center shadow-xl lg:max-w-md lg:justify-self-end w-full">
        <h3 class="text-xl font-bold text-white mb-2">Accéder à votre espace</h3>
        <p class="text-stone-400 text-sm mb-8">
          Identifiez-vous pour débloquer toutes les fonctionnalités de la bibliothèque.
        </p>
        
        <router-link 
          to="/login"
          class="w-full bg-amber-600 hover:bg-amber-700 text-white px-8 py-4 rounded-xl font-bold transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg shadow-amber-900/40 text-center"
        >
          Se connecter
        </router-link>
      </div>

    </div>

  </div>

  <!-- Flèche vers le bas -->
  <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce hidden sm:block">
    <i class="pi pi-chevron-down text-3xl text-stone-400/80"></i>
  </div>
</section>


    <!-- Header pour connectés -->
    <header v-else class="bg-with-600 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl text-amber-800 font-bold mb-2">Bienvenue, {{ authStore.user?.first_name }} !</h1>
        <p class="text-gray-500">Explorez le catalogue complet</p>
        
        <div v-if="authStore.user?.status !== 'active'" class="mt-4 bg-amber-500  border border-yellow-500/50 rounded-lg p-4">
          <p class="text-yellow-200 text-sm mb-3">
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
        
        <div class="flex flex-col md:flex-row gap-4 items-center mb-10">
          
          <!-- Search Bar -->
          <div class="relative w-full md:max-w-2xl">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par titre, sous-titre ou ISBN..."
              class="w-full px-6 py-4 rounded-full border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition shadow-sm"
            />
          </div>
          
          <!-- Categories Filter -->
          <div class="w-full md:max-w-xs">
            <select
              v-model="selectedCategory"
              class="w-full px-6 py-4 rounded-full border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition shadow-sm bg-white text-stone-700"
            >
              <option :value="null">Toutes les catégories</option>
              <option
                v-for="category in categoryStore.categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Reset Button -->
          <button
            @click="resetFilters"
            class="px-6 py-4 bg-stone-200 hover:bg-stone-300 text-stone-700 rounded-full font-medium transition"
          >
            Réinitialiser
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
              <button
  @click.stop="openSummary(reference)"
  class="mt-2 text-sm text-amber-700 hover:text-amber-800 font-medium underline"
>
  Lire résumé
</button>
            </div>
          </div>
        </div>
      </div>


      <!-- Modal pour le résumé -->
<div v-if="selectedReferenceForSummary" class="fixed inset-0 z-50 flex items-center justify-center">
  <!-- Overlay (fond sombre) -->
  <div @click="closeSummary" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
  
  <!-- Contenu de la modale -->
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
    <!-- Bouton fermer -->
    <button @click="closeSummary" class="absolute top-4 right-4 text-stone-500 hover:text-stone-700">
      <i class="pi pi-times text-xl"></i>
    </button>
    <div class="mb-6 flex justify-center">
  <img
    :src="selectedReferenceForSummary.cover_image_url"
    :alt="selectedReferenceForSummary.title"
    class="w-48 h-64 object-cover rounded-xl shadow-md"
  />
</div>
    
    <!-- Titre de la référence -->
    <h3 class="text-2xl font-bold text-stone-800 mb-2">{{ selectedReferenceForSummary.title }}</h3>
    <!-- Sous-titre si disponible -->
    <p v-if="selectedReferenceForSummary.subtitle" class="text-stone-500 mb-6">{{ selectedReferenceForSummary.subtitle }}</p>
    
    <!-- Résumé -->
    <div>
      <h4 class="text-lg font-semibold text-stone-700 mb-3">Résumé</h4>
      <p class="text-stone-600 leading-relaxed">{{ selectedReferenceForSummary.abstract || 'Aucun résumé disponible pour cette référence.' }}</p>
    </div>
  </div>
</div>
    </section>
    <Toast position="top-right" />
  </div>
</template>
