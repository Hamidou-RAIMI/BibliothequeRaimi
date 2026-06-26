<script setup>
import { onMounted, ref } from 'vue'
import { useDepositeRequestStore } from '@/stores/depositeRequest'
import { useCategoryStore } from '@/stores/category'
import { usePublisherStore } from '@/stores/publisher'
import { useToast } from 'primevue/usetoast'

const depositeRequestStore = useDepositeRequestStore()
const categoryStore = useCategoryStore()
const publisherStore = usePublisherStore()
const toast = useToast()

const showCreateModal = ref(false)
const showDetailModal = ref(false)

const formData = ref({
  title: '',
  description: '',
  proposed_file: '',
  subtitle: '',
  abstract: '',
  isbn: '',
  publication_year: '',
  language: 'fr',
  document_type: 'livre',
  category_id: '',
  publisher_id: '',
  pages: '',
})

onMounted(async () => {
  try {
    await Promise.all([
      depositeRequestStore.fetchMyDepositeRequests(),
      categoryStore.fetchCategories(),
      publisherStore.fetchPublishers(),
    ])
  } catch (err) {
    console.error(err)
  }
})

const openCreateModal = () => {
  showCreateModal.value = true
}

const resetForm = () => {
  formData.value = {
    title: '',
    description: '',
    proposed_file: '',
    subtitle: '',
    abstract: '',
    isbn: '',
    publication_year: '',
    language: 'fr',
    document_type: 'livre',
    category_id: '',
    publisher_id: '',
    pages: '',
  }
}

const closeCreateModal = () => {
  showCreateModal.value = false
  resetForm()
}

const openDetailModal = (request) => {
  depositeRequestStore.currentDepositeRequest = request
  showDetailModal.value = true
}

const handleCreate = async () => {
  try {
    await depositeRequestStore.createDepositeRequest(formData.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande de dépôt créée avec succès', life: 3000 })
    closeCreateModal()
  } catch (err) {
    console.error(err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || 'Erreur lors de la création', life: 3000 })
  }
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    assigned: 'Affectée',
    reassigned: 'Réaffectée',
    approved_by_manager: 'Approuvée par responsable',
    rejected_by_manager: 'Rejetée par responsable',
    published: 'Publiée',
    rejected: 'Rejetée',
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    assigned: 'bg-blue-100 text-blue-800',
    reassigned: 'bg-purple-100 text-purple-800',
    approved_by_manager: 'bg-green-100 text-green-800',
    rejected_by_manager: 'bg-red-100 text-red-800',
    published: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }
  return classes[status] || classes.pending
}

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

const getCategoryName = (categoryId) => {
  if (!categoryId) return '-'
  const category = categoryStore.categories.find(c => c.id === categoryId)
  return category?.name || category?.nom || `Catégorie ${categoryId}`
}

const getPublisherName = (publisherId) => {
  if (!publisherId) return '-'
  const publisher = publisherStore.publishers.find(p => p.id === publisherId)
  return publisher?.name || '-'
}
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">
        <i class="pi pi-file-edit mr-3 text-amber-700"></i>
        Mes Demandes de Dépôt
      </h1>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition flex items-center gap-2"
      >
        <i class="pi pi-plus"></i>
        Nouvelle Demande
      </button>
    </div>

    <!-- Error -->
    <div v-if="depositeRequestStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
      {{ depositeRequestStore.error }}
    </div>

    <!-- Requests List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div v-if="depositeRequestStore.loading" class="p-12 text-center text-gray-500">
        <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
        <p>Chargement des demandes...</p>
      </div>
      <div v-else-if="depositeRequestStore.depositeRequests.length === 0" class="p-12 text-center text-gray-500">
        <i class="pi pi-inbox text-4xl mb-3"></i>
        <p>Vous n'avez pas encore de demandes de dépôt</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Titre</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Statut</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Responsable</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="request in depositeRequestStore.depositeRequests" :key="request.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div>
                  <p class="font-semibold text-gray-800">{{ request.title }}</p>
                  <p v-if="request.description" class="text-sm text-gray-500 line-clamp-1">{{ request.description }}</p>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(request.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getStatusLabel(request.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-600">
                {{ request.assignedManager ? `${request.assignedManager.first_name} ${request.assignedManager.last_name}` : '-' }}
              </td>
              <td class="px-6 py-4 text-gray-600">
                {{ new Date(request.created_at).toLocaleDateString('fr-FR') }}
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  @click="openDetailModal(request)"
                  class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                  title="Voir détails"
                >
                  <i class="pi pi-eye"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="closeCreateModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Nouvelle Demande de Dépôt</h2>
          <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600 text-2xl">
            &times;
          </button>
        </div>
        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Titre *</label>
              <input
                v-model="formData.title"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Titre de la référence"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Sous-titre</label>
              <input
                v-model="formData.subtitle"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Sous-titre (optionnel)"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Description de la demande"
              ></textarea>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Résumé</label>
              <textarea
                v-model="formData.abstract"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Résumé de la référence (optionnel)"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">ISBN</label>
              <input
                v-model="formData.isbn"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="ISBN (optionnel)"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Année de publication</label>
              <input
                v-model="formData.publication_year"
                type="number"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Année (optionnel)"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Langue</label>
              <select
                v-model="formData.language"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
              >
                <option value="fr">Français</option>
                <option value="en">Anglais</option>
                <option value="autre">Autre</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Type de document</label>
              <select
                v-model="formData.document_type"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
              >
                <option value="livre">Livre</option>
                <option value="memoire">Mémoire</option>
                <option value="these">Thèse</option>
                <option value="article">Article</option>
                <option value="revue">Revue</option>
                <option value="rapport">Rapport</option>
                <option value="guide">Guide</option>
                <option value="autre">Autre</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie</label>
              <select
                v-model="formData.category_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
              >
                <option value="">Sélectionner une catégorie (optionnel)</option>
                <option v-for="category in categoryStore.categories" :key="category.id" :value="category.id">
                  {{ category.name || category.nom }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Éditeur</label>
              <select
                v-model="formData.publisher_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
              >
                <option value="">Sélectionner un éditeur (optionnel)</option>
                <option v-for="publisher in publisherStore.publishers" :key="publisher.id" :value="publisher.id">
                  {{ publisher.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Pages</label>
              <input
                v-model="formData.pages"
                type="number"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Nombre de pages (optionnel)"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Fichier proposé *</label>
              <input
                v-model="formData.proposed_file"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Chemin ou lien vers le fichier"
              />
            </div>
          </div>
          <div class="flex gap-3 pt-4">
            <button
              @click="closeCreateModal"
              class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
            >
              Annuler
            </button>
            <button
              @click="handleCreate"
              :disabled="!formData.title || !formData.proposed_file || depositeRequestStore.loading"
              class="flex-1 px-4 py-3 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
            >
              Soumettre la demande
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showDetailModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Détails de la demande</h2>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">
            &times;
          </button>
        </div>
        <div v-if="depositeRequestStore.currentDepositeRequest" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-500">Titre</label>
              <p class="text-lg font-semibold text-gray-800">{{ depositeRequestStore.currentDepositeRequest.title }}</p>
            </div>
            <div v-if="depositeRequestStore.currentDepositeRequest.subtitle">
              <label class="block text-sm font-semibold text-gray-500">Sous-titre</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.subtitle }}</p>
            </div>
            <div class="md:col-span-2" v-if="depositeRequestStore.currentDepositeRequest.description">
              <label class="block text-sm font-semibold text-gray-500">Description</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.description }}</p>
            </div>
            <div class="md:col-span-2" v-if="depositeRequestStore.currentDepositeRequest.abstract">
              <label class="block text-sm font-semibold text-gray-500">Résumé</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.abstract }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">ISBN</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.isbn || '-' }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">Année de publication</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.publication_year || '-' }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">Langue</label>
              <p class="text-gray-700">{{ getLanguageLabel(depositeRequestStore.currentDepositeRequest.language) }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">Type de document</label>
              <p class="text-gray-700">{{ getDocumentTypeLabel(depositeRequestStore.currentDepositeRequest.document_type) }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">Catégorie</label>
              <p class="text-gray-700">{{ getCategoryName(depositeRequestStore.currentDepositeRequest.category_id) }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">Éditeur</label>
              <p class="text-gray-700">{{ getPublisherName(depositeRequestStore.currentDepositeRequest.publisher_id) }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-500">Pages</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.pages || '-' }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-500">Fichier proposé</label>
              <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.proposed_file || '-' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-6 border-t border-gray-200">
            <div>
              <label class="text-sm font-semibold text-gray-500">Statut</label>
              <p>
                <span
                  :class="getStatusClass(depositeRequestStore.currentDepositeRequest.status)"
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                >
                  {{ getStatusLabel(depositeRequestStore.currentDepositeRequest.status) }}
                </span>
              </p>
            </div>
            <div v-if="depositeRequestStore.currentDepositeRequest.assignedManager">
              <label class="text-sm font-semibold text-gray-500">Responsable</label>
              <p class="text-gray-700">
                {{ depositeRequestStore.currentDepositeRequest.assignedManager.first_name }}
                {{ depositeRequestStore.currentDepositeRequest.assignedManager.last_name }}
              </p>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-500">Date de création</label>
              <p class="text-gray-700">
                {{ new Date(depositeRequestStore.currentDepositeRequest.created_at).toLocaleDateString('fr-FR') }}
              </p>
            </div>
          </div>

          <!-- Reviews -->
          <div v-if="depositeRequestStore.currentDepositeRequest.reviews && depositeRequestStore.currentDepositeRequest.reviews.length > 0" class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Historique des avis</h3>
            <div class="space-y-4">
              <div
                v-for="review in depositeRequestStore.currentDepositeRequest.reviews"
                :key="review.id"
                class="p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <p class="font-semibold text-gray-800">
                      {{ review.reviewer?.first_name }} {{ review.reviewer?.last_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ review.reviewer_role === 'admin' ? 'Administrateur' : 'Responsable demande' }}
                    </p>
                  </div>
                  <span
                    :class="review.decision === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ review.decision === 'approved' ? 'Approuvé' : 'Rejeté' }}
                  </span>
                </div>
                <p v-if="review.justification" class="text-sm text-gray-600">{{ review.justification }}</p>
                <p class="text-xs text-gray-400 mt-2">
                  {{ new Date(review.created_at).toLocaleDateString('fr-FR') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
