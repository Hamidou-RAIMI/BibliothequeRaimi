<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useReferenceStore } from '@/stores/reference'
import { useAuthorStore } from '@/stores/author'
import { usePublisherStore } from '@/stores/publisher'
import { useCategoryStore } from '@/stores/category'
import { Toast } from 'primevue'
import { useToast } from 'primevue/usetoast'
import client from '@/api/client'


// ========================================================================
// INITIALISATION DES STORES ET SERVICES
// ========================================================================
const referenceStore = useReferenceStore()
const authorStore = useAuthorStore()
const publisherStore = usePublisherStore()
const categoryStore = useCategoryStore()
const toast = useToast()
const apiUrl = import.meta.env.VITE_API_URL
const router = useRouter()

// Fonction pour revenir à la liste des références
const goBack = () => {
  router.push('/admin/references')
}

// ========================================================================
// VARIABLES LOCALES
// ========================================================================
const showDetailModal = ref(false)
const currentReference = ref(null)
const searchQuery = ref('')
const users = ref([])

// ========================================================================
// PROPRIÉTÉS COMPUTÉES
// ========================================================================
const filteredReferences = computed(() => {
  return referenceStore.archivedReferences.filter(reference => {
    return searchQuery.value === '' ||
      reference.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (reference.subtitle && reference.subtitle.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (reference.isbn && reference.isbn.includes(searchQuery.value))
  })
})

const getDocumentTypeLabel = (type) => {
  const labels = {
    livre: 'Livre',
    memoire: 'Mémoire',
    these: 'Thèse',
    article: 'Article',
    revue: 'Revue',
    rapport: 'Rapport',
    guide: 'Guide',
    autre: 'Autre',
  }
  return labels[type] || type
}

const getLanguageLabel = (lang) => {
  const labels = {
    fr: 'Français',
    en: 'Anglais',
    autre: 'Autre',
  }
  return labels[lang] || lang
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Brouillon',
    published: 'Publié',
    archived: 'Archivé',
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-yellow-100 text-yellow-800',
    published: 'bg-green-100 text-green-800',
    archived: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || classes.draft
}

// ========================================================================
// CYCLE DE VIE
// ========================================================================
onMounted(async () => {
  try {
    await Promise.all([
      referenceStore.fetchArchivedReferences(),
      authorStore.fetchAuthors(),
      publisherStore.fetchPublishers(),
      categoryStore.fetchCategories(),
      client.get('/users').then(r => users.value = r.data.data),
    ])
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des données', life: 3000 })
  }
})

// ========================================================================
// FONCTIONS D'ACTION
// ========================================================================
const openDetailModal = (reference) => {
  currentReference.value = reference
  showDetailModal.value = true
}

const restoreReference = async (reference) => {
  try {
    await referenceStore.restoreReference(reference.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence restaurée avec succès', life: 3000 })
  } catch (err) {
    console.error('Erreur lors de la restauration:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
  }
}
</script>

<template>
  <div class="p-6 max-w-8xl mx-auto">
    <Toast />
    <!-- TITRE DE LA PAGE ET BOUTON RETOUR -->
    <div class="flex items-center gap-4 mb-8">
      <button
        @click="goBack"
        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
        title="Retour"
      >
        <i class="pi pi-arrow-left text-xl"></i>
      </button>
      <h1 class="text-3xl font-bold text-gray-800">
        <i class="pi pi-archive mr-3 text-orange-700"></i>
        Archives des Références
      </h1>
    </div>

    <!-- MESSAGE D'ERREUR -->
    <div v-if="referenceStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
      {{ referenceStore.error }}
    </div>

    <!-- RECHERCHE -->
    <div class="p-6 mb-6 bg-white rounded-xl shadow-lg">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par titre, sous-titre ou ISBN..."
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- TABLEAU DES RÉFÉRENCES ARCHIVÉES -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Couverture</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Titre</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Type</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Langue</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Année</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <!-- ÉTAT DE CHARGEMENT -->
            <tr v-if="referenceStore.loading">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                <p>Chargement des références...</p>
              </td>
            </tr>

            <!-- LISTE DES RÉFÉRENCES ARCHIVÉES -->
            <tr v-else v-for="reference in filteredReferences" :key="reference.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <div v-if="reference.cover_image_url" class="w-16 h-20 overflow-hidden rounded-lg border border-gray-200">
                  <img :src="reference.cover_image_url" :alt="reference.title" class="w-full h-full object-cover">
                </div>
                <div v-else class="w-16 h-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                  <i class="pi pi-image text-gray-400 text-xl"></i>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <p class="font-semibold text-gray-800">{{ reference.title }}</p>
                  <p v-if="reference.subtitle" class="text-sm text-gray-500">{{ reference.subtitle }}</p>
                  <p v-if="reference.isbn" class="text-xs text-gray-400">{{ reference.isbn }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-gray-600">{{ getDocumentTypeLabel(reference.document_type) }}</td>
              <td class="px-6 py-4 text-gray-600">{{ getLanguageLabel(reference.language) }}</td>
              <td class="px-6 py-4 text-gray-600">{{ reference.publication_year || '-' }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openDetailModal(reference)"
                    class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition"
                    title="Voir les détails"
                  >
                    <i class="pi pi-eye"></i>
                  </button>
                  <button
                    @click="restoreReference(reference)"
                    class="p-2 text-amber-600 hover:bg-amber-100 rounded-lg transition"
                    title="Restaurer"
                  >
                    <i class="pi pi-undo"></i>
                  </button>
                </div>
              </td>
            </tr>

            <!-- AUCUNE RÉFÉRENCE -->
            <tr v-if="!referenceStore.loading && filteredReferences.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p>Aucune référence archivée</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODALE DÉTAILS -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showDetailModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Détails de la référence</h2>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">
            &times;
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="md:col-span-1">
            <div v-if="currentReference?.cover_image_url" class="w-full h-64 overflow-hidden rounded-lg border border-gray-200">
              <img :src="currentReference.cover_image_url" :alt="currentReference.title" class="w-full h-full object-cover">
            </div>
            <div v-else class="w-full h-64 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
              <i class="pi pi-image text-gray-400 text-5xl"></i>
            </div>
          </div>
          <div class="md:col-span-2 space-y-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Titre</label>
              <p class="text-lg font-semibold text-gray-800">{{ currentReference?.title }}</p>
            </div>
            <div v-if="currentReference?.subtitle">
              <label class="text-sm font-medium text-gray-500">Sous-titre</label>
              <p class="text-gray-700">{{ currentReference.subtitle }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div v-if="currentReference?.document_type">
                <label class="text-sm font-medium text-gray-500">Type</label>
                <p class="text-gray-700">{{ getDocumentTypeLabel(currentReference.document_type) }}</p>
              </div>
              <div v-if="currentReference?.language">
                <label class="text-sm font-medium text-gray-500">Langue</label>
                <p class="text-gray-700">{{ getLanguageLabel(currentReference.language) }}</p>
              </div>
              <div v-if="currentReference?.publication_year">
                <label class="text-sm font-medium text-gray-500">Année de publication</label>
                <p class="text-gray-700">{{ currentReference.publication_year }}</p>
              </div>
              <div v-if="currentReference?.isbn">
                <label class="text-sm font-medium text-gray-500">ISBN</label>
                <p class="text-gray-700">{{ currentReference.isbn }}</p>
              </div>
            </div>
            <div v-if="currentReference?.abstract">
              <label class="text-sm font-medium text-gray-500">Résumé</label>
              <p class="text-gray-700">{{ currentReference.abstract }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div v-if="currentReference?.category_id">
                <label class="text-sm font-medium text-gray-500">Catégorie</label>
                <p class="text-gray-700">{{ categoryStore.categories.find(c => c.id === currentReference?.category_id)?.name || categoryStore.categories.find(c => c.id === currentReference?.category_id)?.nom || '-' }}</p>
              </div>
              <div v-if="currentReference?.publisher_id">
                <label class="text-sm font-medium text-gray-500">Éditeur</label>
                <p class="text-gray-700">{{ publisherStore.publishers.find(p => p.id === currentReference?.publisher_id)?.name || '-' }}</p>
              </div>
              <div v-if="currentReference?.uploaded_by">
                <label class="text-sm font-medium text-gray-500">Ajouté par</label>
                <p class="text-gray-700">{{ (typeof currentReference?.uploaded_by === 'object' && currentReference?.uploaded_by !== null) ? `${currentReference?.uploaded_by?.first_name} ${currentReference?.uploaded_by?.last_name}` : '-' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
