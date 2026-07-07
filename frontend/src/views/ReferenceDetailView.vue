<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import client from '@/api/client'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const reference = ref(null)
const loading = ref(true)
const error = ref(null)
const showPdfViewer = ref(false)

const loadReference = async () => {
  try {
    loading.value = true
    error.value = null
    const res = await client.get(`/references/${route.params.id}`)
    reference.value = res.data.data
  } catch (err) {
    error.value = 'Erreur lors du chargement de la référence'
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Fonction pour télécharger le fichier PDF et incrémenter le compteur
const handleDownload = async () => {
  try {
    // Créer un lien pour télécharger le fichier
    const downloadUrl = `${import.meta.env.VITE_API_URL}/references/${reference.value.id}/download`
    window.open(downloadUrl, '_blank')

    // Incrémenter localement le compteur de téléchargements (pour mettre à jour l'affichage)
    reference.value.download_count = (reference.value.download_count || 0) + 1
  } catch (err) {
    console.error('Erreur lors du téléchargement:', err)
    // Afficher un message d'erreur si nécessaire
  }
}

// Calculer l'URL du PDF pour la visualisation
const pdfUrl = computed(() => {
  if (!reference.value || !reference.value.file_path) return ''
  const url = `${import.meta.env.VITE_STORAGE_URL}/${reference.value.file_path}#toolbar=0&navpanes=0&view=Fit`
  console.log('📄 PDF URL:', url)
  console.log('📁 File path:', reference.value.file_path)
  return url
})

// Ouvrir le visualiseur PDF
const openPdfViewer = () => {
  if (!pdfUrl.value) return
  showPdfViewer.value = true
}

onMounted(() => {
  loadReference()
})
</script>

<template>
  <div class="min-h-screen bg-stone-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Back Button -->
      <button 
        @click="router.back()" 
        class="flex items-center gap-2 text-stone-600 hover:text-stone-900 mb-8 transition"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Retour au catalogue
      </button>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-700"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-white rounded-xl shadow p-12 text-center">
        <div class="text-red-500 text-lg mb-2">Oups !</div>
        <p class="text-stone-600">{{ error }}</p>
      </div>

      <!-- Reference Detail -->
      <div v-else-if="reference" class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div>
          <!-- Cover Image (Taille réelle préservée) -->
          <div class="relative w-full flex justify-center bg-stone-100">
            <img 
              :src="reference.cover_image_url" 
              :alt="reference.title" 
              class="w-full max-h-[700px] object-contain"
            />
            
            <!-- Badges -->
            <div class="absolute top-4 left-4 flex gap-2">
              <div v-if="reference.is_new" class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow">
                Nouveau
              </div>
            </div>
          </div>

          <!-- Content -->
          <div class="p-8">
            <!-- Title & Subtitle -->
            <h1 class="text-3xl font-bold text-stone-900 mb-2">{{ reference.title }}</h1>
            <p v-if="reference.subtitle" class="text-xl text-stone-600 mb-4">{{ reference.subtitle }}</p>

            <!-- Authors -->
            <div class="flex items-center gap-2 mb-6">
              <span class="text-stone-500">Par</span>
              <span class="font-semibold text-amber-700">
                {{ reference.authors && reference.authors.length > 0 
                  ? reference.authors.map(a => `${a.first_name} ${a.last_name}`).join(', ') 
                  : 'Auteur inconnu' 
                }}
              </span>
            </div>

            <!-- Summary -->
            <div v-if="reference.summary" class="mb-8">
              <h3 class="text-lg font-semibold text-stone-800 mb-2">Résumé</h3>
              <p class="text-stone-600 leading-relaxed">{{ reference.summary }}</p>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
              <div class="bg-stone-50 p-4 rounded-lg">
                <div class="text-stone-500 text-sm mb-1">ISBN</div>
                <div class="font-semibold text-stone-800">{{ reference.isbn || 'Non renseigné' }}</div>
              </div>
              
              <div class="bg-stone-50 p-4 rounded-lg">
                <div class="text-stone-500 text-sm mb-1">Éditeur</div>
                <div class="font-semibold text-stone-800">{{ reference.publisher?.name || 'Non renseigné' }}</div>
              </div>
              
              <div class="bg-stone-50 p-4 rounded-lg">
                <div class="text-stone-500 text-sm mb-1">Année de publication</div>
                <div class="font-semibold text-stone-800">{{ reference.publication_year || 'Non renseigné' }}</div>
              </div>
              
              <div class="bg-stone-50 p-4 rounded-lg">
                <div class="text-stone-500 text-sm mb-1">Langue</div>
                <div class="font-semibold text-stone-800">{{ reference.language || 'Non renseigné' }}</div>
              </div>
              
              <div v-if="reference.number_of_pages" class="bg-stone-50 p-4 rounded-lg">
                <div class="text-stone-500 text-sm mb-1">Nombre de pages</div>
                <div class="font-semibold text-stone-800">{{ reference.number_of_pages }}</div>
              </div>
              
              <div class="bg-stone-50 p-4 rounded-lg">
                <div class="text-stone-500 text-sm mb-1">Catégorie</div>
                <div class="font-semibold text-stone-800">{{ reference.category?.name || 'Non renseigné' }}</div>
              </div>

              <div class="bg-stone-50 p-4 rounded-lg md:col-span-2">
                <div class="text-stone-500 text-sm mb-1">Téléchargements</div>
                <div class="font-semibold text-stone-800 flex items-center gap-2">
                  <i class="pi pi-download text-amber-700"></i>
                  {{ reference.download_count || 0 }} fois
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
              <button 
                v-if="reference.is_available"
                class="bg-amber-700 hover:bg-amber-800 text-white px-6 py-3 rounded-lg font-semibold transition"
              >
                Réserver ce livre
              </button>
              
              <button 
                v-if="reference.file_path"
                @click="handleDownload"
                class="border border-amber-700 bg-amber-50 hover:bg-amber-100 text-amber-700 px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2"
              >
                <i class="pi pi-download"></i>
                Télécharger le PDF
              </button>
              <button 
                v-if="reference.file_path"
                @click="openPdfViewer"
                class="border border-stone-300 hover:border-amber-700 text-stone-700 hover:text-amber-700 px-6 py-3 rounded-lg font-semibold transition flex items-center gap-2"
              >
                <i class="pi pi-book-open"></i>
                Lire en directe
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal PDF Viewer -->
    <div v-if="showPdfViewer" class="fixed inset-0 z-50 bg-black/90 flex flex-col">
      <!-- Barre de contrôle -->
      <div class="flex items-center justify-between px-4 py-3 bg-black/50">
        <h2 class="text-white font-semibold truncate">{{ reference.title }}</h2>
        <button 
          @click="showPdfViewer = false"
          class="text-white hover:text-amber-400 transition p-2 rounded-lg hover:bg-white/10"
        >
          <i class="pi pi-times text-2xl"></i>
        </button>
      </div>

      <!-- Iframe PDF -->
      <iframe 
        :src="pdfUrl"
        class="flex-1 w-full border-0"
        title="Lecteur PDF"
      ></iframe>
    </div>
  </div>
</template>
