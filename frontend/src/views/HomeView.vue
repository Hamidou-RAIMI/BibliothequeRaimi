<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryStore } from '@/stores/category'
import { useReferenceStore } from '@/stores/reference'
import client from '@/api/client'
// import Header from '@/components/layout/Header.vue'
// import Footer from '@/components/layout/Footer.vue'

const router = useRouter()
const categoryStore = useCategoryStore()
const referenceStore = useReferenceStore()
const apiUrl = import.meta.env.VITE_API_URL

const selectedCategory = ref(null)
const searchQuery = ref('')

const filteredReferences = computed(() => {
  let refs = referenceStore.references
  
  // Filtrer par catégorie
  if (selectedCategory.value) {
    refs = refs.filter(ref => ref.category_id === selectedCategory.value)
  }
  
  // Filtrer par recherche
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

const loadPublicData = async () => {
  try {
    const [categoriesRes, referencesRes] = await Promise.all([
      client.get('/public/categories'),
      client.get('/public/references'),
    ])
    categoryStore.categories = categoriesRes.data.data
    referenceStore.references = referencesRes.data.data
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
  }
}

onMounted(() => {
  loadPublicData()
})

const handleClick = () => {
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-stone-100">

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-stone-800 to-stone-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <span class="text-amber-400 font-semibold">À LA UNE</span>
            <h1 class="text-5xl font-bold mt-4 mb-6">Les Misérables</h1>
            <p class="text-stone-300 text-lg mb-8">
              Découvrez ce chef-d'œuvre de Victor Hugo, une histoire emblématique de justice, de rédemption et d'amour.
            </p>
            <div class="flex gap-4">
              <button @click="handleClick" class="bg-amber-700 text-white px-6 py-3 rounded-lg hover:bg-amber-800 transition">
                Lire en ligne
              </button>
              <button @click="handleClick" class="border border-white text-white px-6 py-3 rounded-lg hover:bg-white hover:text-stone-900 transition">
                Télécharger
              </button>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">1862</div>
              <div class="text-stone-300 text-sm">Année de publication</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">48 300</div>
              <div class="text-stone-300 text-sm">Pages</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">124 000</div>
              <div class="text-stone-300 text-sm">Lectures</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">98%</div>
              <div class="text-stone-300 text-sm">Satisfaction</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Catalog Section -->
    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-10">
          <h2 class="text-3xl font-bold text-stone-800">Parcourir le catalogue</h2>
          <button @click="handleClick" class="text-amber-700 font-semibold hover:underline">Voir tout →</button>
        </div>
        
        <!-- Search Bar -->
        <div class="mb-10">
          <div class="relative max-w-6xl">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher une référence par titre, sous-titre ou ISBN..."
              class="w-full px-6 py-4 rounded-full border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            />
            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>
        
        <!-- Categories -->
        <div class="flex gap-3 mb-10 flex-wrap">
          <span
            @click="selectedCategory = null"
            :class="selectedCategory === null ? 'bg-amber-700 text-white' : 'bg-white text-stone-700 border'"
            class="px-4 py-2 rounded-full text-sm cursor-pointer hover:opacity-80 transition"
          >
            Toute la bibliothèque
          </span>
          <span
            v-for="category in categoryStore.categories"
            :key="category.id"
            @click="selectedCategory = category.id"
            :class="selectedCategory === category.id ? 'bg-amber-700 text-white' : 'bg-white text-stone-700 border'"
            class="px-4 py-2 rounded-full text-sm cursor-pointer hover:opacity-80 transition"
          >
            {{ category.name }}
          </span>
        </div>

        <!-- Books Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <div
            v-for="reference in filteredReferences"
            :key="reference.id"
            @click="handleClick"
            class="bg-white rounded-xl overflow-hidden shadow hover:shadow-lg transition cursor-pointer"
          >
            <img
              :src="reference.cover_image_url ? reference.cover_image_url : 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20placeholder&image_size=square_hd'"
              :alt="reference.title"
              class="w-full h-64 object-cover"
            />
            <div class="p-4">
              <h3 class="font-semibold text-stone-800 mb-1">{{ reference.title }}</h3>
              <p class="text-stone-500 text-sm mb-2">
                {{ reference.authors && reference.authors.length > 0 ? `${reference.authors[0].first_name} ${reference.authors[0].last_name}` : 'Auteur inconnu' }}
              </p>
              <div class="flex items-center gap-1 text-amber-500 text-sm">
                <i class="pi pi-star-fill"></i>
                <span>4.5</span>
                <span class="text-stone-400">(0)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-teal-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Enrichissez la bibliothèque collective</h2>
        <p class="text-teal-200 mb-8 max-w-2xl mx-auto">
          Partagez vos livres avec la communauté. Ensemble, construisons une bibliothèque accessible à tous.
        </p>
        <div class="flex justify-center gap-4">
          <button @click="handleClick" class="bg-white text-teal-900 px-6 py-3 rounded-lg hover:bg-teal-50 transition font-semibold">
            Ajouter une référence
          </button>
          <button @click="handleClick" class="border border-white text-white px-6 py-3 rounded-lg hover:bg-white hover:text-teal-900 transition">
            En savoir plus
          </button>
        </div>
      </div>
    </section>

   
  </div>
</template>
