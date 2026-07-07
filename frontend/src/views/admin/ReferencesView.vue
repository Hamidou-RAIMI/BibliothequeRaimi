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
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const currentUserName = ref('')
const router = useRouter()
const referenceStore = useReferenceStore()
const authorStore = useAuthorStore()
const publisherStore = usePublisherStore()
const categoryStore = useCategoryStore()
const toast = useToast()

const isModalOpen = ref(false)
const modalMode = ref('create')
const showDeleteModal = ref(false)
const referenceToDelete = ref(null)
const showDetailModal = ref(false)
const currentReference = ref(null)
const searchQuery = ref('')

// Variables pour l'image de couverture
const coverImageFile = ref(null)
const coverImagePreview = ref('')

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
  is_new: true,
})

const filteredReferences = computed(() => {
  return referenceStore.references.filter(reference => {
    return searchQuery.value === '' || 
      reference.title.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
      (reference.isbn && reference.isbn.toLowerCase().includes(searchQuery.value.toLowerCase()))
  })
})

const openModal = (mode, reference = null) => {
  modalMode.value = mode
  if (mode === 'edit' && reference) {
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
      uploaded_by: (typeof reference.uploaded_by === 'object' && reference.uploaded_by !== null) ? reference.uploaded_by.id : (reference.uploaded_by || ''),
      cover_image: reference.cover_image || '',
      file_path: reference.file_path || '',
      pages: reference.pages || '',
      status: reference.status,
      is_new: reference.is_new || false,
    }
    currentUserName.value = authStore.user ? `${authStore.user.first_name} ${authStore.user.last_name}` : ''
    if (reference.cover_image) {
      coverImagePreview.value = `${import.meta.env.VITE_API_URL}/storage/${reference.cover_image}`
    }
  } else {
    resetForm()
  }
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
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
    is_new: true,
  }
  coverImageFile.value = null
  coverImagePreview.value = ''
}

const handleCoverImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    coverImageFile.value = file
    coverImagePreview.value = URL.createObjectURL(file)
  }
}

const resetCoverImage = () => {
  coverImageFile.value = null
  coverImagePreview.value = ''
}

const handleSubmit = async () => {
  try {
    if (!formData.value.uploaded_by) {
      formData.value.uploaded_by = authStore.user?.id
    }
    const submitData = { ...formData.value }
    delete submitData.cover_image
    if (coverImageFile.value) {
      submitData.cover_image = coverImageFile.value
    }

    if (modalMode.value === 'create') {
      await referenceStore.createReference(submitData)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence créée avec succès', life: 3000 })
    } else {
      await referenceStore.updateReference(formData.value.id, submitData)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence modifiée avec succès', life: 3000 })
    }
    closeModal()
  } catch (err) {
    console.error('Erreur lors de la sauvegarde:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: err.response?.data?.message || 'Erreur lors de la sauvegarde', life: 5000 })
  }
}

const openDeleteModal = (reference) => {
  referenceToDelete.value = reference
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  referenceToDelete.value = null
}

const handleDelete = async () => {
  try {
    await referenceStore.archiveReference(referenceToDelete.value.id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Référence archivée avec succès', life: 3000 })
    closeDeleteModal()
  } catch (err) {
    console.error('Erreur lors de l\'archivage:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'archivage', life: 5000 })
  }
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Brouillon',
    published: 'Publié',
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-yellow-100 text-yellow-800',
    published: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  try {
    await Promise.all([
      referenceStore.fetchReferences(),
      authorStore.fetchAuthors(),
      publisherStore.fetchPublishers(),
      categoryStore.fetchCategories(),
    ])
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion des Références</h1>
          <p class="text-gray-600">Gérez les références documentaires de la bibliothèque</p>
        </div>
        <div class="flex gap-4">
          <router-link
            to="/admin/references/archives"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition"
          >
            <i class="pi pi-archive mr-2"></i>
            Archives
          </router-link>
          <button
            @click="openModal('create')"
            class="px-4 py-2 bg-amber-700 text-white rounded-lg hover:bg-amber-800 transition"
          >
            <i class="pi pi-plus mr-2"></i>
            Ajouter une référence
          </button>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="relative">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher une référence..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
          />
        </div>
      </div>

      <!-- References Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Couverture</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Langue</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="referenceStore.loading">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <i class="pi pi-spin pi-spinner text-4xl mb-3"></i>
                  <p>Chargement des références...</p>
                </td>
              </tr>
              <tr v-else-if="filteredReferences.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <i class="pi pi-inbox text-4xl mb-3"></i>
                  <p>Aucune référence trouvée</p>
                </td>
              </tr>
              <tr v-else v-for="reference in filteredReferences" :key="reference.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                  <img
                    :src="reference.cover_image_url || 'https://via.placeholder.com/100x100'"
                    :alt="reference.title"
                    class="w-16 h-20 object-cover rounded"
                  />
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ reference.title }}</div>
                  <div v-if="reference.subtitle" class="text-sm text-gray-500">{{ reference.subtitle }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ reference.isbn || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ reference.language || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(reference.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                    {{ getStatusLabel(reference.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex justify-end gap-2">
                    <button
                      @click="openModal('edit', reference)"
                      class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                      title="Modifier"
                    >
                      <i class="pi pi-pencil"></i>
                    </button>
                    <button
                      @click="openDeleteModal(reference)"
                      class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"
                      title="Archiver"
                    >
                      <i class="pi pi-inbox"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">
            {{ modalMode === 'create' ? 'Ajouter une référence' : 'Modifier la référence' }}
          </h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">
            &times;
          </button>
        </div>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Titre *</label>
              <input
                v-model="formData.title"
                type="text"
                required
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
              <label class="block text-sm font-semibold text-gray-700 mb-2">Résumé</label>
              <textarea
                v-model="formData.abstract"
                rows="4"
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
              <label class="block text-sm font-semibold text-gray-700 mb-2">Éditeur</label>
              <select
                v-model="formData.publisher_id"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option value="">Sélectionner un éditeur</option>
                <option v-for="pub in publisherStore.publishers" :key="pub.id" :value="pub.id">
                  {{ pub.name }}
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
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
              <select
                v-model="formData.status"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              >
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Image de couverture</label>
              <div v-if="coverImagePreview" class="mb-4">
                <img :src="coverImagePreview" alt="Preview" class="w-32 h-40 object-cover rounded" />
                <button @click="resetCoverImage" type="button" class="mt-2 text-red-600 text-sm hover:underline">
                  Supprimer l'image
                </button>
              </div>
              <input
                type="file"
                accept="image/*"
                @change="handleCoverImageChange"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              />
            </div>
            <div class="md:col-span-2 flex items-center">
              <input
                v-model="formData.is_new"
                type="checkbox"
                id="is_new"
                class="w-4 h-4 text-amber-700 focus:ring-amber-500 border-gray-300 rounded"
              />
              <label for="is_new" class="ml-2 block text-sm text-gray-700">Marquer comme nouveau</label>
            </div>
          </div>
          <div class="flex gap-4 pt-6">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="!formData.title || referenceStore.loading"
              class="flex-1 px-4 py-3 bg-amber-700 hover:bg-amber-800 text-white rounded-lg transition font-semibold disabled:opacity-50"
            >
              {{ modalMode === 'create' ? 'Créer' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="closeDeleteModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Archiver la référence</h2>
        <p class="text-gray-600 mb-6">
          Êtes-vous sûr de vouloir archiver la référence "{{ referenceToDelete?.title }}" ?
        </p>
        <div class="flex gap-4">
          <button
            @click="closeDeleteModal"
            class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition font-semibold"
          >
            Annuler
          </button>
          <button
            @click="handleDelete"
            :disabled="referenceStore.loading"
            class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-semibold disabled:opacity-50"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles if needed */
</style>
