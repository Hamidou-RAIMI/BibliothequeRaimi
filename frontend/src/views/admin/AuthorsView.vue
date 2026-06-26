<script setup>
// ==============================================
// VUE POUR LA GESTION DES AUTEURS
// ==============================================
// Ce composant Vue permet d'afficher, créer, modifier et supprimer des auteurs
import { computed, onMounted, ref } from 'vue'
import { useAuthorStore } from '@/stores/author'
import { Toast } from 'primevue'
import { useToast } from 'primevue/usetoast'

// Initialisation des stores et services
const authorStore = useAuthorStore()
const toast = useToast()

// ==============================================
// VARIABLES RÉACTIVES (ÉTAT LOCAL)
// ==============================================
const isModalOpen = ref(false)       // Contrôle l'affichage de la modale de création/modification
const modalMode = ref('create')      // Définit si la modale est en mode "création" ou "modification"
const showDeleteModal = ref(false)   // Contrôle l'affichage de la modale de confirmation de suppression
const authorToDelete = ref(null)     // Stocke l'auteur sélectionné pour suppression
const searchQuery = ref('')          // Texte de recherche pour filtrer les auteurs

// Données du formulaire d'auteur
const formData = ref({
  first_name: '',
  last_name: '',
  biography: '',
  nationality: '',
  birth_date: '',
  death_date: ''
})

// ==============================================
// PROPRIÉTÉ COMPUTÉE : FILTRAGE DES AUTEURS
// ==============================================
// Filtre les auteurs en fonction du texte de recherche
const filteredAuthors = computed(() => {
  return authorStore.authors.filter(author => {
    const fullName = `${author.first_name} ${author.last_name}`.toLowerCase()
    return searchQuery.value === '' || fullName.includes(searchQuery.value.toLowerCase())
  })
})

// ==============================================
// CYCLE DE VIE
// ==============================================
// Charge les auteurs au montage du composant
onMounted(async () => {
  try {
    await authorStore.fetchAuthors()
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des auteurs', life: 3000 })
  }
})

// ==============================================
// FONCTIONS D'AFFICHAGE DES MODALES
// ==============================================
/**
 * Ouvre la modale de création ou modification d'auteur
 * @param string mode - "create" pour créer, "edit" pour modifier
 * @param Object|null author - Auteur à modifier (seulement si mode = "edit")
 */
const openModal = (mode, author = null) => {
  modalMode.value = mode
  if (mode === 'edit' && author) {
    formData.value = {
      id: author.id,
      first_name: author.first_name,
      last_name: author.last_name,
      biography: author.biography || '',
      nationality: author.nationality || '',
      birth_date: author.birth_date || '',
      death_date: author.death_date || ''
    }
  } else {
    resetForm()
  }
  isModalOpen.value = true
}

/**
 * Ferme la modale et réinitialise le formulaire
 */
const closeModal = () => {
  isModalOpen.value = false
  resetForm()
}

/**
 * Réinitialise les champs du formulaire à leur valeur par défaut
 */
const resetForm = () => {
  formData.value = {
    first_name: '',
    last_name: '',
    biography: '',
    nationality: '',
    birth_date: '',
    death_date: ''
  }
}

// ==============================================
// FONCTIONS D'ACTION (CRUD)
// ==============================================
/**
 * Soumet le formulaire : crée un nouvel auteur ou met à jour un auteur existant
 */
const handleSubmit = async () => {
  try {
    if (modalMode.value === 'create') {
      await authorStore.createAuthor(formData.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Auteur créé avec succès', life: 3000 })
    } else {
      await authorStore.updateAuthor(formData.value.id, formData.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Auteur mis à jour avec succès', life: 3000 })
    }
    closeModal()
  } catch (err) {
    console.error('Erreur lors de la soumission:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
  }
}

/**
 * Ouvre la modale de confirmation de suppression
 * @param Object author - Auteur à supprimer
 */
const confirmDelete = (author) => {
  authorToDelete.value = author
  showDeleteModal.value = true
}

/**
 * Exécute la suppression de l'auteur
 */
const handleDelete = async () => {
  if (authorToDelete.value) {
    try {
      await authorStore.deleteAuthor(authorToDelete.value.id)
      showDeleteModal.value = false
      authorToDelete.value = null
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Auteur supprimé avec succès', life: 3000 })
    } catch (err) {
      console.error('Erreur lors de la suppression:', err)
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
    }
  }
}
</script>

<template>
  <div class="p-6 max-w-8xl mx-auto">
    <!-- Composant Toast pour les notifications -->
    <Toast />
    
    <!-- Titre de la page -->
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
      <i class="pi pi-user mr-3 text-amber-700"></i>
      Gestion des Auteurs
    </h1>

    <!-- Message d'erreur si le store a rencontré un problème -->
    <div v-if="authorStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
      {{ authorStore.error }}
    </div>

    <!-- Barre de recherche des auteurs -->
    <div class="p-6 mb-6 bg-white rounded-xl shadow-lg">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par nom..."
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Bouton pour ajouter un nouvel auteur -->
    <div class="mb-6">
      <button
        @click="openModal('create')"
        class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition"
      >
        <i class="pi pi-plus"></i>
        Ajouter un auteur
      </button>
    </div>

    <!-- Tableau affichant la liste des auteurs -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nom</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Prénom</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nationalité</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date de naissance</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <!-- État de chargement : affiché tant que les données ne sont pas prêtes -->
            <tr v-if="authorStore.loading">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                <p>Chargement des auteurs...</p>
              </td>
            </tr>
            
            <!-- Liste des auteurs filtrés -->
            <tr v-else v-for="author in filteredAuthors" :key="author.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 font-semibold text-gray-800">{{ author.last_name }}</td>
              <td class="px-6 py-4 text-gray-600">{{ author.first_name }}</td>
              <td class="px-6 py-4 text-gray-600">{{ author.nationality || '-' }}</td>
              <td class="px-6 py-4 text-gray-600">{{ author.birth_date || '-' }}</td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <!-- Bouton de modification -->
                  <button
                    @click="openModal('edit', author)"
                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                    title="Modifier"
                  >
                    <i class="pi pi-pencil"></i>
                  </button>
                  <!-- Bouton de suppression -->
                  <button
                    @click="confirmDelete(author)"
                    class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"
                    title="Supprimer"
                  >
                    <i class="pi pi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            
            <!-- Message si aucun auteur ne correspond à la recherche -->
            <tr v-if="!authorStore.loading && filteredAuthors.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p>Aucun auteur trouvé</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modale de création/modification d'un auteur -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Arrière-plan semi-transparent -->
      <div @click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      
      <!-- Contenu de la modale -->
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
          {{ modalMode === 'create' ? 'Ajouter un auteur' : 'Modifier l\'auteur' }}
        </h2>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Champ prénom -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
              <input
                v-model="formData.first_name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              />
            </div>
            <!-- Champ nom -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
              <input
                v-model="formData.last_name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              />
            </div>
            <!-- Champ nationalité -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nationalité</label>
              <input
                v-model="formData.nationality"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              />
            </div>
            <!-- Champ date de naissance -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
              <input
                v-model="formData.birth_date"
                type="date"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              />
            </div>
            <!-- Champ date de décès -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date de décès</label>
              <input
                v-model="formData.death_date"
                type="date"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              />
            </div>
          </div>
          <!-- Champ biographie (texte long) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Biographie</label>
            <textarea
              v-model="formData.biography"
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            ></textarea>
          </div>
          <!-- Boutons Annuler et Valider -->
          <div class="flex gap-4 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="authorStore.loading"
              class="flex-1 px-6 py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2"
            >
              <i v-if="authorStore.loading" class="pi pi-spin pi-spinner"></i>
              {{ modalMode === 'create' ? 'Créer' : 'Modifier' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modale de confirmation de suppression -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showDeleteModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-exclamation-triangle text-red-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Confirmer la suppression ?</h3>
        <p class="text-gray-600 mb-6">
          Êtes-vous sûr de vouloir supprimer <strong>{{ authorToDelete?.first_name }} {{ authorToDelete?.last_name }}</strong> ? Cette action est irréversible.
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
            :disabled="authorStore.loading"
            class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold rounded-lg transition"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
