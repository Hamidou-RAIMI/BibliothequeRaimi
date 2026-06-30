<script setup>
import { onMounted, ref } from 'vue'
import { useDepositeRequestStore } from '@/stores/depositeRequest'
import { useCategoryStore } from '@/stores/category'
import { usePublisherStore } from '@/stores/publisher'
import { useAuthorStore } from '@/stores/author'
import { useToast } from 'primevue/usetoast'
import client from '@/api/client'

const depositeRequestStore = useDepositeRequestStore()
const categoryStore = useCategoryStore()
const publisherStore = usePublisherStore()
const authorStore = useAuthorStore()
const toast = useToast()
const apiUrl = import.meta.env.VITE_API_URL
const storageUrl = import.meta.env.VITE_STORAGE_URL

const showCreateModal = ref(false)
const showDetailModal = ref(false)
const currentStep = ref(1)

// Selected author and publisher
const selectedAuthor = ref(null)
const selectedPublisher = ref(null)

// Forms for creating new author/publisher
const newAuthorForm = ref({
    first_name: '',
    last_name: '',
    biography: '',
    nationality: '',
    birth_date: '',
    death_date: '',
})
const newPublisherForm = ref({
    name: '',
    description: '',
    country: '',
    website: '',
})
const showCreateAuthor = ref(false)
const showCreatePublisher = ref(false)

const formData = ref({
    title: '',
    description: '',
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

// Fichier PDF sélectionné
const pdfFile = ref(null)
const pdfFileName = ref('')

// Photo de couverture sélectionnée
const coverImageFile = ref(null)
const coverImagePreview = ref('')

const openCreateModal = () => {
  showCreateModal.value = true
  currentStep.value = 1
  resetForm()
}

const resetForm = () => {
  formData.value = {
    title: '',
    description: '',
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
  selectedAuthor.value = null
  selectedPublisher.value = null
  showCreateAuthor.value = false
  showCreatePublisher.value = false
  pdfFile.value = null
  pdfFileName.value = ''
  coverImageFile.value = null
  coverImagePreview.value = ''
}

// Fonction pour gérer la sélection du fichier PDF
const handlePdfChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    pdfFile.value = file
    pdfFileName.value = file.name
  }
}

// Fonction pour réinitialiser le fichier PDF
const resetPdfFile = () => {
  pdfFile.value = null
  pdfFileName.value = ''
  const pdfInput = document.getElementById('pdf-upload')
  if (pdfInput) pdfInput.value = ''
}

// Fonction pour gérer la sélection de la photo de couverture
const handleCoverImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    coverImageFile.value = file
    // Créer un aperçu de l'image
    coverImagePreview.value = URL.createObjectURL(file)
  }
}

// Fonction pour réinitialiser la photo de couverture
const resetCoverImage = () => {
  coverImageFile.value = null
  coverImagePreview.value = ''
  const coverInput = document.getElementById('cover-upload')
  if (coverInput) coverInput.value = ''
}

const closeCreateModal = () => {
  showCreateModal.value = false
  resetForm()
}

const goToNextStep = async () => {
  if (currentStep.value === 1 && !selectedAuthor.value && !showCreateAuthor.value) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez sélectionner un auteur', life: 3000 })
    return
  }
  if (currentStep.value === 2 && !selectedPublisher.value && !showCreatePublisher.value) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez sélectionner un éditeur', life: 3000 })
    return
  }

  if (currentStep.value < 3) {
    currentStep.value++
  }
}

const goToPreviousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const createNewAuthor = async () => {
  try {
    const author = await authorStore.createAuthor(newAuthorForm.value)
    selectedAuthor.value = author
    showCreateAuthor.value = false
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Auteur créé avec succès', life: 3000 })
  } catch (err) {
    console.error('Erreur lors de la création de l\'auteur:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la création de l\'auteur', life: 5000 })
  }
}

const createNewPublisher = async () => {
  try {
    const publisher = await publisherStore.createPublisher(newPublisherForm.value)
    selectedPublisher.value = publisher
    showCreatePublisher.value = false
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Éditeur créé avec succès', life: 3000 })
  } catch (err) {
    console.error('Erreur lors de la création de l\'éditeur:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la création de l\'éditeur', life: 5000 })
  }
}

const handleCreate = async () => {
    try {
        // Créer un FormData pour envoyer les données et les fichiers
        const formDataObj = new FormData()
        
        // Ajouter les champs du formulaire au FormData
        formDataObj.append('title', formData.value.title)
        formDataObj.append('description', formData.value.description)
        if (formData.value.subtitle) formDataObj.append('subtitle', formData.value.subtitle)
        if (formData.value.abstract) formDataObj.append('abstract', formData.value.abstract)
        if (formData.value.isbn) formDataObj.append('isbn', formData.value.isbn)
        if (formData.value.publication_year) formDataObj.append('publication_year', formData.value.publication_year)
        formDataObj.append('language', formData.value.language)
        formDataObj.append('document_type', formData.value.document_type)
        if (formData.value.category_id) formDataObj.append('category_id', formData.value.category_id)
        if (formData.value.publisher_id) formDataObj.append('publisher_id', selectedPublisher.value?.id || '')
        if (formData.value.pages) formDataObj.append('pages', formData.value.pages)
        
        // Ajouter le fichier PDF
        if (pdfFile.value) {
            formDataObj.append('pdf_file', pdfFile.value)
        }

        // Ajouter la photo de couverture
        if (coverImageFile.value) {
            formDataObj.append('cover_image', coverImageFile.value)
        }

        // Envoyer la requête avec le client axios
        const response = await client.post('/deposite-requests', formDataObj, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        // Mettre à jour le store avec la nouvelle demande
        await depositeRequestStore.fetchMyDepositeRequests()
        
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande de dépôt créée avec succès', life: 3000 })
        closeCreateModal()
    } catch (err) {
        console.error('Erreur lors de la création de la demande:', err)
        toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || 'Erreur lors de la création de la demande', life: 5000 })
    }
}

const openDetailModal = (request) => {
  depositeRequestStore.currentDepositeRequest = request
  showDetailModal.value = true
}

// Fonction pour supprimer une demande de dépôt
const handleDelete = async () => {
  try {
    // Récupérer l'ID de la demande courante
    const requestId = depositeRequestStore.currentDepositeRequest?.id
    if (!requestId) return

    // Envoyer la requête de suppression
    await client.delete(`/deposite-requests/${requestId}`)

    // Mettre à jour la liste des demandes
    await depositeRequestStore.fetchMyDepositeRequests()

    toast.add({ severity: 'success', summary: 'Succès', detail: 'Demande de dépôt supprimée avec succès', life: 3000 })
    closeDetailModal()
  } catch (err) {
    console.error('Erreur lors de la suppression de la demande:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || 'Erreur lors de la suppression de la demande', life: 5000 })
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  depositeRequestStore.currentDepositeRequest = null
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    assigned: 'Assignée',
    approved_by_manager: 'Approuvée par responsable',
    rejected_by_manager: 'Rejetée par responsable',
    second_review: 'Deuxième vérification',
    approved: 'Approuvée',
    rejected: 'Rejetée',
    published: 'Publiée',
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    assigned: 'bg-blue-100 text-blue-800',
    approved_by_manager: 'bg-green-100 text-green-800',
    rejected_by_manager: 'bg-red-100 text-red-800',
    second_review: 'bg-purple-100 text-purple-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    published: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  try {
    await Promise.all([
      depositeRequestStore.fetchMyDepositeRequests(),
      categoryStore.fetchCategories(),
      publisherStore.fetchPublishers(),
      authorStore.fetchAuthors(),
    ])
  } catch (err) {
    console.error(err)
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Mes Demandes de Dépôt</h1>
          <p class="text-gray-600">Gérez vos demandes de dépôt de références</p>
        </div>
        <button
          @click="openCreateModal"
          class="px-4 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition"
        >
          <i class="pi pi-plus mr-2"></i>
          Nouvelle demande
        </button>
      </div>

      <!-- Requests List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="depositeRequestStore.loading">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <i class="pi pi-spin pi-spinner text-4xl mb-3"></i>
                  <p>Chargement...</p>
                </td>
              </tr>
              <tr v-else-if="depositeRequestStore.depositeRequests.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <i class="pi pi-inbox text-4xl mb-3"></i>
                  <p>Aucune demande trouvée</p>
                </td>
              </tr>
              <tr v-else v-for="request in depositeRequestStore.depositeRequests" :key="request.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ request.title }}</div>
                </td>
                <td class="px-6 py-4">
                  <p v-if="request.description" class="text-sm text-gray-500 line-clamp-1">{{ request.description }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
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

        <!-- Step Indicator -->
        <div class="mb-8">
          <div class="flex justify-between items-center">
            <div class="flex flex-col items-center">
              <div
                :class="currentStep >= 1 ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-500'"
                class="w-10 h-10 rounded-full flex items-center justify-center font-semibold mb-2"
              >
                1
              </div>
              <span
                :class="currentStep >= 1 ? 'text-amber-700 font-semibold' : 'text-gray-500'"
                class="text-sm"
              >
                Auteur
              </span>
            </div>
            <div
              :class="currentStep >= 2 ? 'bg-amber-600' : 'bg-gray-200'"
              class="h-1 flex-1 mx-4"
            ></div>
            <div class="flex flex-col items-center">
              <div
                :class="currentStep >= 2 ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-500'"
                class="w-10 h-10 rounded-full flex items-center justify-center font-semibold mb-2"
              >
                2
              </div>
              <span
                :class="currentStep >= 2 ? 'text-amber-700 font-semibold' : 'text-gray-500'"
                class="text-sm"
              >
                Éditeur
              </span>
            </div>
            <div
              :class="currentStep >= 3 ? 'bg-amber-600' : 'bg-gray-200'"
              class="h-1 flex-1 mx-4"
            ></div>
            <div class="flex flex-col items-center">
              <div
                :class="currentStep >= 3 ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-500'"
                class="w-10 h-10 rounded-full flex items-center justify-center font-semibold mb-2"
              >
                3
              </div>
              <span
                :class="currentStep >= 3 ? 'text-amber-700 font-semibold' : 'text-gray-500'"
                class="text-sm"
              >
                Demande
              </span>
            </div>
          </div>
        </div>

        <!-- Step 1: Author -->
        <div v-if="currentStep === 1" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-800">Sélectionner ou créer un auteur</h3>
          <div v-if="!showCreateAuthor" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Auteur</label>
              <select
                v-model="selectedAuthor"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option :value="null">Sélectionner un auteur</option>
                <option v-for="author in authorStore.authors" :key="author.id" :value="author">
                  {{ author.first_name }} {{ author.last_name }}
                </option>
              </select>
            </div>
            <button
              @click="showCreateAuthor = true"
              class="text-amber-700 hover:text-amber-800 text-sm font-semibold"
            >
              + Créer un nouvel auteur
            </button>
          </div>
          <div v-else class="bg-gray-50 p-6 rounded-lg space-y-4">
            <h4 class="font-semibold text-gray-800">Nouvel auteur</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                <input
                  v-model="newAuthorForm.first_name"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                <input
                  v-model="newAuthorForm.last_name"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Biographie</label>
                <textarea
                  v-model="newAuthorForm.biography"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nationalité</label>
                <input
                  v-model="newAuthorForm.nationality"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
                <input
                  v-model="newAuthorForm.birth_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de décès</label>
                <input
                  v-model="newAuthorForm.death_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                />
              </div>
            </div>
            <div class="flex gap-4">
              <button
                @click="showCreateAuthor = false"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
              >
                Annuler
              </button>
              <button
                @click="createNewAuthor"
                :disabled="!newAuthorForm.first_name || !newAuthorForm.last_name"
                class="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
              >
                Créer l'auteur
              </button>
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <button
              @click="goToNextStep"
              :disabled="!selectedAuthor"
              class="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
            >
              Suivant
            </button>
          </div>
        </div>

        <!-- Step 2: Publisher -->
        <div v-else-if="currentStep === 2" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-800">Sélectionner ou créer un éditeur</h3>
          <div v-if="!showCreatePublisher" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Éditeur</label>
              <select
                v-model="selectedPublisher"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option :value="null">Sélectionner un éditeur</option>
                <option v-for="publisher in publisherStore.publishers" :key="publisher.id" :value="publisher">
                  {{ publisher.name }}
                </option>
              </select>
            </div>
            <button
              @click="showCreatePublisher = true"
              class="text-amber-700 hover:text-amber-800 text-sm font-semibold"
            >
              + Créer un nouvel éditeur
            </button>
          </div>
          <div v-else class="bg-gray-50 p-6 rounded-lg space-y-4">
            <h4 class="font-semibold text-gray-800">Nouvel éditeur</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                <input
                  v-model="newPublisherForm.name"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                  v-model="newPublisherForm.description"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                ></textarea>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                  <input
                    v-model="newPublisherForm.country"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Site web</label>
                  <input
                    v-model="newPublisherForm.website"
                    type="url"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                  />
                </div>
              </div>
            </div>
            <div class="flex gap-4">
              <button
                @click="showCreatePublisher = false"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
              >
                Annuler
              </button>
              <button
                @click="createNewPublisher"
                :disabled="!newPublisherForm.name"
                class="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
              >
                Créer l'éditeur
              </button>
            </div>
          </div>
          <div class="flex justify-between mt-6">
            <button
              @click="goToPreviousStep"
              class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
            >
              Précédent
            </button>
            <button
              @click="goToNextStep"
              :disabled="!selectedPublisher"
              class="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
            >
              Suivant
            </button>
          </div>
        </div>

        <!-- Step 3: Request -->
        <div v-else-if="currentStep === 3" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-800">Informations sur la demande de dépôt</h3>
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
              <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Description de la demande"
              ></textarea>
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
              <label class="block text-sm font-semibold text-gray-700 mb-2">Résumé</label>
              <textarea
                v-model="formData.abstract"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Résumé de la référence"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">ISBN</label>
              <input
                v-model="formData.isbn"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="ISBN"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Année de publication</label>
              <input
                v-model="formData.publication_year"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Année"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Langue</label>
              <select
                v-model="formData.language"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option value="fr">Français</option>
                <option value="en">Anglais</option>
                <option value="ar">Arabe</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Type de document</label>
              <select
                v-model="formData.document_type"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option value="livre">Livre</option>
                <option value="article">Article</option>
                <option value="these">Thèse</option>
                <option value="rapport">Rapport</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Catégorie</label>
              <select
                v-model="formData.category_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option value="">Sélectionner une catégorie</option>
                <option v-for="cat in categoryStore.categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Pages</label>
              <input
                v-model="formData.pages"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
                placeholder="Nombre de pages"
              />
            </div>
            <!-- Upload du PDF -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Fichier PDF *</label>
          <div class="space-y-2">
            <!-- Si un fichier est sélectionné, afficher son nom et un bouton pour annuler -->
            <div v-if="pdfFileName" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
              <span class="text-sm text-gray-700">
                <i class="pi pi-file-pdf mr-2 text-red-600"></i>
                {{ pdfFileName }}
              </span>
              <button
                @click="resetPdfFile"
                type="button"
                class="text-red-600 hover:text-red-800 text-sm font-semibold"
              >
                <i class="pi pi-times"></i>
              </button>
            </div>
            <!-- Sinon, afficher le champ d'upload -->
            <input
              v-else
              id="pdf-upload"
              type="file"
              accept=".pdf"
              @change="handlePdfChange"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            />
          </div>
        </div>

        <!-- Upload de la photo de couverture -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Photo de couverture</label>
          <div class="space-y-2">
            <!-- Si une image est sélectionnée, afficher l'aperçu -->
            <div v-if="coverImagePreview" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
              <img :src="coverImagePreview" alt="Aperçu de la couverture" class="w-24 h-32 object-cover rounded" />
              <button
                @click="resetCoverImage"
                type="button"
                class="text-red-600 hover:text-red-800 text-sm font-semibold"
              >
                <i class="pi pi-times mr-1"></i> Supprimer
              </button>
            </div>
            <!-- Sinon, afficher le champ d'upload -->
            <input
              v-else
              id="cover-upload"
              type="file"
              accept="image/jpeg,image/png,image/jpg"
              @change="handleCoverImageChange"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            />
          </div>
        </div>
          </div>
          <div class="flex justify-between mt-6">
            <button
              @click="goToPreviousStep"
              class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
            >
              Précédent
            </button>
            <button
              @click="handleCreate"
              :disabled="!formData.title || !formData.description || !pdfFile || depositeRequestStore.loading"
              class="flex-1 px-4 py-3 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
            >
              Soumettre la demande
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal && depositeRequestStore.currentDepositeRequest" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="closeDetailModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Détails de la demande</h2>
          <button @click="closeDetailModal" class="text-gray-400 hover:text-gray-600 text-2xl">
            &times;
          </button>
        </div>
        <div class="space-y-6">
          <!-- Photo de couverture -->
          <div v-if="depositeRequestStore.currentDepositeRequest.cover_image">
            <label class="text-sm font-medium text-gray-500 mb-2 block">Photo de couverture</label>
            <img
              :src="`${storageUrl}/${depositeRequestStore.currentDepositeRequest.cover_image}`"
              :alt="depositeRequestStore.currentDepositeRequest.title"
              class="w-64 h-80 object-cover rounded-lg shadow"
            />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Titre</label>
            <p class="text-lg font-semibold text-gray-800">{{ depositeRequestStore.currentDepositeRequest.title }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.subtitle">
            <label class="text-sm font-medium text-gray-500">Sous-titre</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.subtitle }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.abstract">
            <label class="text-sm font-medium text-gray-500">Résumé</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.abstract }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.isbn">
            <label class="text-sm font-medium text-gray-500">ISBN</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.isbn }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.publication_year">
            <label class="text-sm font-medium text-gray-500">Année de publication</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.publication_year }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Langue</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.language }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Type de document</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.document_type }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.category">
            <label class="text-sm font-medium text-gray-500">Catégorie</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.category?.name }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.publisher">
            <label class="text-sm font-medium text-gray-500">Éditeur</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.publisher?.name }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.pages">
            <label class="text-sm font-medium text-gray-500">Pages</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.pages }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.description">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.description }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Statut</label>
            <p>
              <span
                :class="getStatusClass(depositeRequestStore.currentDepositeRequest.status)"
                class="px-3 py-1 rounded-full text-xs font-semibold"
              >
                {{ getStatusLabel(depositeRequestStore.currentDepositeRequest.status) }}
              </span>
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Demandeur</label>
              <p class="text-gray-700">
                {{ depositeRequestStore.currentDepositeRequest.applicant?.first_name }}
                {{ depositeRequestStore.currentDepositeRequest.applicant?.last_name }}
              </p>
            </div>
            <div v-if="depositeRequestStore.currentDepositeRequest.assignedManager">
              <label class="text-sm font-medium text-gray-500">Responsable</label>
              <p class="text-gray-700">
                {{ depositeRequestStore.currentDepositeRequest.assignedManager.first_name }}
                {{ depositeRequestStore.currentDepositeRequest.assignedManager.last_name }}
              </p>
            </div>
          </div>
          <!-- Fichier PDF proposé -->
          <div v-if="depositeRequestStore.currentDepositeRequest.proposed_file">
            <label class="text-sm font-medium text-gray-500">Fichier PDF</label>
            <a
              :href="`${storageUrl}/${depositeRequestStore.currentDepositeRequest.proposed_file}`"
              target="_blank"
              class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-200 transition font-semibold"
            >
              <i class="pi pi-file-pdf"></i>
              Télécharger le PDF
            </a>
          </div>

          <!-- Boutons d'action -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button
              @click="handleDelete"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition flex items-center gap-2"
            >
              <i class="pi pi-trash"></i>
              Supprimer la demande
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
