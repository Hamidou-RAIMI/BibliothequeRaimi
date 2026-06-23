<script setup>
import { computed, onMounted, ref } from 'vue'
import { useReferenceStore } from '@/stores/reference'
import { useAuthorStore } from '@/stores/author'
import { usePublisherStore } from '@/stores/publisher'
import { Toast } from 'primevue'
import { useToast } from 'primevue/usetoast'
import client from '@/api/client'

// ========================================================================
// INITIALISATION DES STORES ET SERVICES
// ========================================================================
const referenceStore = useReferenceStore()
const authorStore = useAuthorStore()
const publisherStore = usePublisherStore()
const toast = useToast()

// ========================================================================
// VARIABLES LOCALES
// ========================================================================
const isModalOpen = ref(false)
const modalMode = ref('create') // 'create' ou 'edit'
const currentStep = ref(1) // 1: Auteur, 2: Éditeur, 3: Référence
const showDeleteModal = ref(false)
const referenceToDelete = ref(null)

const categories = ref([])
const users = ref([])

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

// Recherche
const searchQuery = ref('')
const searchAuthorsQuery = ref('')
const searchPublishersQuery = ref('')

// Données du formulaire de référence
const formData = ref({
  title: '',
  subtitle: '',
  abstract: '',
  isbn: '',
  publication_year: '',
  language: 'fr',
  document_type: 'livre',
  category_id: '',
  publisher_id: '',
  uploaded_by: '',
  cover_image: '',
  file_path: '',
  pages: '',
  status: 'draft',
})

// ========================================================================
// PROPRIÉTÉS COMPUTÉES
// ========================================================================
const filteredReferences = computed(() => {
  return referenceStore.references.filter(reference => {
    return searchQuery.value === '' ||
      reference.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (reference.subtitle && reference.subtitle.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (reference.isbn && reference.isbn.includes(searchQuery.value))
  })
})

const filteredAuthors = computed(() => {
  return authorStore.authors.filter(author => {
    const fullName = `${author.first_name} ${author.last_name}`.toLowerCase()
    return searchAuthorsQuery.value === '' || fullName.includes(searchAuthorsQuery.value.toLowerCase())
  })
})

const filteredPublishers = computed(() => {
  return publisherStore.publishers.filter(publisher => {
    return searchPublishersQuery.value === '' || 
      publisher.name.toLowerCase().includes(searchPublishersQuery.value.toLowerCase())
  })
})

// ========================================================================
// CYCLE DE VIE
// ========================================================================
onMounted(async () => {
  try {
    await Promise.all([
      referenceStore.fetchReferences(),
      authorStore.fetchAuthors(),
      publisherStore.fetchPublishers(),
      client.get('/categories').then(r => categories.value = r.data.data),
      client.get('/users').then(r => users.value = r.data.data),
    ])
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des données', life: 3000 })
  }
})

// ========================================================================
// FONCTIONS D'AFFICHAGE
// ========================================================================
const openModal = (mode, reference = null) => {
  modalMode.value = mode
  currentStep.value = 1
  selectedAuthor.value = null
  selectedPublisher.value = null
  showCreateAuthor.value = false
  showCreatePublisher.value = false
  
  if (mode === 'edit' && reference) {
    currentStep.value = 3
    formData.value = {
      id: reference.id,
      title: reference.title,
      subtitle: reference.subtitle || '',
      abstract: reference.abstract || '',
      isbn: reference.isbn || '',
      publication_year: reference.publication_year || '',
      language: reference.language,
      document_type: reference.document_type,
      category_id: reference.category_id || '',
      publisher_id: reference.publisher_id || '',
      uploaded_by: reference.uploaded_by || '',
      cover_image: reference.cover_image || '',
      file_path: reference.file_path || '',
      pages: reference.pages || '',
      status: reference.status,
    }
    if (reference.publisher_id) {
      selectedPublisher.value = publisherStore.publishers.find(p => p.id === reference.publisher_id)
    }
  } else {
    resetForm()
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  currentStep.value = 1
  resetForm()
}

const resetForm = () => {
  formData.value = {
    title: '',
    subtitle: '',
    abstract: '',
    isbn: '',
    publication_year: '',
    language: 'fr',
    document_type: 'livre',
    category_id: '',
    publisher_id: '',
    uploaded_by: '',
    cover_image: '',
    file_path: '',
    pages: '',
    status: 'draft',
  }
  newAuthorForm.value = {
    first_name: '',
    last_name: '',
    biography: '',
    nationality: '',
    birth_date: '',
    death_date: '',
  }
  newPublisherForm.value = {
    name: '',
    description: '',
    country: '',
    website: '',
  }
  selectedAuthor.value = null
  selectedPublisher.value = null
  showCreateAuthor.value = false
  showCreatePublisher.value = false
}

// ========================================================================
// FONCTIONS DE NAVIGATION ENTRE ÉTAPES
// ========================================================================
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

// ========================================================================
// FONCTIONS DE CRÉATION D'AUTEUR/ÉDITEUR
// ========================================================================
const createNewAuthor = async () => {
  try {
    const author = await authorStore.createAuthor(newAuthorForm.value)
    selectedAuthor.value = author
    showCreateAuthor.value = false
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Auteur créé avec succès', life: 3000 })
  } catch (err) {
    console.error('Erreur lors de la création de l\'auteur:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la création de l\'auteur', life: 3000 })
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
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la création de l\'éditeur', life: 3000 })
  }
}

// ========================================================================
// LABEL HELPERS
// ========================================================================
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
// FONCTIONS D'ACTION
// ========================================================================
const handleSubmit = async () => {
  try {
    if (selectedPublisher.value) {
      formData.value.publisher_id = selectedPublisher.value.id
    }
    if (modalMode.value === 'create') {
      await referenceStore.createReference(formData.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence créée avec succès', life: 3000 })
    } else {
      await referenceStore.updateReference(formData.value.id, formData.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence mise à jour avec succès', life: 3000 })
    }
    closeModal()
  } catch (err) {
    console.error('Erreur lors de la soumission:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
  }
}

const toggleStatus = async (id) => {
  try {
    await referenceStore.toggleReferenceStatus(id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Statut mis à jour avec succès', life: 3000 })
  } catch (err) {
    console.error('Erreur lors du changement de statut:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
  }
}

const confirmDelete = (reference) => {
  referenceToDelete.value = reference
  showDeleteModal.value = true
}

const handleDelete = async () => {
  if (referenceToDelete.value) {
    try {
      await referenceStore.deleteReference(referenceToDelete.value.id)
      showDeleteModal.value = false
      referenceToDelete.value = null
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence supprimée avec succès', life: 3000 })
    } catch (err) {
      console.error('Erreur lors de la suppression:', err)
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
    }
  }
}
</script>

<template>
  <div class="p-6 max-w-8xl mx-auto">
    <Toast />
    <!-- TITRE DE LA PAGE -->
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
      <i class="pi pi-book mr-3 text-amber-700"></i>
      Gestion des Références
    </h1>

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
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- BOUTON AJOUTER UNE RÉFÉRENCE -->
    <div class="mb-6">
      <button
        @click="openModal('create')"
        class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition"
      >
        <i class="pi pi-plus"></i>
        Ajouter une référence
      </button>
    </div>

    <!-- TABLEAU DES RÉFÉRENCES -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Titre</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Type</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Langue</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Année</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Statut</th>
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

            <!-- LISTE DES RÉFÉRENCES -->
            <tr v-else v-for="reference in filteredReferences" :key="reference.id" class="hover:bg-gray-50 transition">
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
              <td class="px-6 py-4">
                <span :class="getStatusClass(reference.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getStatusLabel(reference.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openModal('edit', reference)"
                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                    title="Modifier"
                  >
                    <i class="pi pi-pencil"></i>
                  </button>
                  <button
                    @click="toggleStatus(reference.id)"
                    class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition"
                    title="Changer le statut"
                  >
                    <i class="pi pi-eye"></i>
                  </button>
                  <button
                    @click="confirmDelete(reference)"
                    class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"
                    title="Supprimer"
                  >
                    <i class="pi pi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <!-- AUCUNE RÉFÉRENCE -->
            <tr v-if="!referenceStore.loading && filteredReferences.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p>Aucune référence trouvée</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODALE MULTI-ÉTAPES -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <!-- INDICATEUR DES ÉTAPES -->
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
                Référence
              </span>
            </div>
          </div>
        </div>

        <!-- ÉTAPE 1: SÉLECTION/CRÉATION D'AUTEUR -->
        <div v-if="currentStep === 1 && modalMode === 'create'">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">
            Sélectionner un auteur
          </h2>

          <!-- RECHERCHE D'AUTEUR -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <div class="relative">
              <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input
                v-model="searchAuthorsQuery"
                type="text"
                placeholder="Rechercher un auteur par nom..."
                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
              />
            </div>
          </div>

          <!-- LISTE DES AUTEURS -->
          <div class="max-h-64 overflow-y-auto mb-6 border rounded-lg p-3">
            <div
              v-for="author in filteredAuthors"
              :key="author.id"
              @click="selectedAuthor = author"
              :class="selectedAuthor?.id === author.id ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'"
              class="p-4 border rounded-lg mb-3 cursor-pointer transition"
            >
              <div class="font-semibold text-gray-800">
                {{ author.first_name }} {{ author.last_name }}
              </div>
              <div v-if="author.nationality" class="text-sm text-gray-500">
                {{ author.nationality }}
              </div>
            </div>
          </div>

          <!-- BOUTON CRÉER AUTEUR -->
          <button
            @click="showCreateAuthor = !showCreateAuthor"
            class="mb-4 text-amber-600 hover:text-amber-800 font-semibold flex items-center gap-2"
          >
            <i class="pi pi-plus"></i>
            {{ showCreateAuthor ? 'Annuler' : 'Créer un nouvel auteur' }}
          </button>

          <!-- FORMULAIRE CRÉATION AUTEUR -->
          <div v-if="showCreateAuthor" class="border-t pt-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
                <input
                  v-model="newAuthorForm.first_name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input
                  v-model="newAuthorForm.last_name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nationalité</label>
                <input
                  v-model="newAuthorForm.nationality"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
                <input
                  v-model="newAuthorForm.birth_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de décès</label>
                <input
                  v-model="newAuthorForm.death_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Biographie</label>
              <textarea
                v-model="newAuthorForm.biography"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
              ></textarea>
            </div>
            <button
              @click="createNewAuthor"
              class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-semibold transition"
            >
              Créer l'auteur
            </button>
          </div>
        </div>

        <!-- ÉTAPE 2: SÉLECTION/CRÉATION D'ÉDITEUR -->
        <div v-if="currentStep === 2 && modalMode === 'create'">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">
            Sélectionner un éditeur
          </h2>

          <!-- RECHERCHE D'ÉDITEUR -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <div class="relative">
              <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input
                v-model="searchPublishersQuery"
                type="text"
                placeholder="Rechercher un éditeur par nom..."
                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
              />
            </div>
          </div>

          <!-- LISTE DES ÉDITEURS -->
          <div class="max-h-64 overflow-y-auto mb-6 border rounded-lg p-3">
            <div
              v-for="publisher in filteredPublishers"
              :key="publisher.id"
              @click="selectedPublisher = publisher"
              :class="selectedPublisher?.id === publisher.id ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300'"
              class="p-4 border rounded-lg mb-3 cursor-pointer transition"
            >
              <div class="font-semibold text-gray-800">{{ publisher.name }}</div>
              <div v-if="publisher.country" class="text-sm text-gray-500">{{ publisher.country }}</div>
            </div>
          </div>

          <!-- BOUTON CRÉER ÉDITEUR -->
          <button
            @click="showCreatePublisher = !showCreatePublisher"
            class="mb-4 text-amber-600 hover:text-amber-800 font-semibold flex items-center gap-2"
          >
            <i class="pi pi-plus"></i>
            {{ showCreatePublisher ? 'Annuler' : 'Créer un nouvel éditeur' }}
          </button>

          <!-- FORMULAIRE CRÉATION ÉDITEUR -->
          <div v-if="showCreatePublisher" class="border-t pt-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input
                  v-model="newPublisherForm.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                <input
                  v-model="newPublisherForm.country"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Site web</label>
                <input
                  v-model="newPublisherForm.website"
                  type="url"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="newPublisherForm.description"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
              ></textarea>
            </div>
            <button
              @click="createNewPublisher"
              class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-semibold transition"
            >
              Créer l'éditeur
            </button>
          </div>
        </div>

        <!-- ÉTAPE 3: FORMULAIRE DE RÉFÉRENCE -->
        <div v-if="currentStep === 3">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">
            {{ modalMode === 'create' ? 'Ajouter une référence' : 'Modifier la référence' }}
          </h2>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                <input
                  v-model="formData.title"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sous-titre</label>
                <input
                  v-model="formData.subtitle"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Résumé</label>
                <textarea
                  v-model="formData.abstract"
                  rows="3"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                <input
                  v-model="formData.isbn"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Année de publication</label>
                <input
                  v-model="formData.publication_year"
                  type="number"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Langue *</label>
                <select
                  v-model="formData.language"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition bg-white"
                >
                  <option value="fr">Français</option>
                  <option value="en">Anglais</option>
                  <option value="autre">Autre</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type de document *</label>
                <select
                  v-model="formData.document_type"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition bg-white"
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                <select
                  v-model="formData.category_id"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
                >
                  <option value="">-- Choisir une catégorie --</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name || cat.nom || `Catégorie ${cat.id}` }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Éditeur</label>
                <select
                  v-model="formData.publisher_id"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
                >
                  <option value="">-- Choisir un éditeur --</option>
                  <option v-for="pub in publisherStore.publishers" :key="pub.id" :value="pub.id">{{ pub.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ajouté par</label>
                <select
                  v-model="formData.uploaded_by"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
                >
                  <option value="">-- Choisir un utilisateur --</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                <select
                  v-model="formData.status"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition bg-white"
                >
                  <option value="draft">Brouillon</option>
                  <option value="published">Publié</option>
                  <option value="archived">Archivé</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pages</label>
                <input
                  v-model="formData.pages"
                  type="number"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
                />
              </div>
            </div>
          </form>
        </div>

        <!-- BOUTONS DE NAVIGATION -->
        <div class="flex gap-4 pt-6 mt-6 border-t">
          <button
            v-if="currentStep > 1"
            type="button"
            @click="goToPreviousStep"
            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
          >
            Précédent
          </button>
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
          >
            Annuler
          </button>
          <button
            v-if="currentStep < 3 && modalMode === 'create'"
            type="button"
            @click="goToNextStep"
            class="flex-1 px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition"
          >
            Suivant
          </button>
          <button
            v-if="currentStep === 3"
            type="button"
            @click="handleSubmit"
            :disabled="referenceStore.loading"
            class="flex-1 px-6 py-3 bg-amber-700 hover:bg-amber-800 disabled:opacity-50 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2"
          >
            <i v-if="referenceStore.loading" class="pi pi-spin pi-spinner"></i>
            {{ modalMode === 'create' ? 'Créer' : 'Modifier' }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODALE DE CONFIRMATION DE SUPPRESSION -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showDeleteModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-exclamation-triangle text-red-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Confirmer la suppression ?</h3>
        <p class="text-gray-600 mb-6">
          Êtes-vous sûr de vouloir supprimer <strong>{{ referenceToDelete?.title }}</strong> ? Cette action est irréversible.
        </p>
        <div class="flex gap-4">
          <button
            @click="showDeleteModal = false"
            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
          >
            Annuler
          </button>
          <button
            @click="handleDelete"
            :disabled="referenceStore.loading"
            class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold rounded-lg transition"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
