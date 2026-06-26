<script setup>
// ==============================================
// VUE POUR LA GESTION DES CATÉGORIES
// ==============================================
// Ce composant Vue permet d'afficher, créer, modifier et supprimer des catégories
import { computed, onMounted, ref } from 'vue'
import { useCategoryStore } from '@/stores/category'
import { Toast } from 'primevue'
import { useToast } from 'primevue/usetoast'

// Initialisation des stores et services
const categoryStore = useCategoryStore()
const toast = useToast()

// ==============================================
// VARIABLES RÉACTIVES (ÉTAT LOCAL)
// ==============================================
const isModalOpen = ref(false)         // Contrôle l'affichage de la modale de création/modification
const modalMode = ref('create')        // Définit si la modale est en mode "création" ou "modification"
const showDeleteModal = ref(false)     // Contrôle l'affichage de la modale de confirmation de suppression
const categoryToDelete = ref(null)    // Stocke la catégorie sélectionnée pour suppression
const searchQuery = ref('')            // Texte de recherche pour filtrer les catégories

// Données du formulaire de catégorie
const formData = ref({
    name: '',
    description: '',
    status: 'active',
})

// ==============================================
// PROPRIÉTÉ COMPUTÉE : FILTRAGE DES CATÉGORIES
// ==============================================
// Filtre les catégories en fonction du texte de recherche
const filteredCategories = computed(() => {
    return categoryStore.categories.filter(category => {
        return searchQuery.value === '' || category.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    })
})

// ==============================================
// CYCLE DE VIE
// ==============================================
// Charge les catégories au montage du composant
onMounted(async () => {
    try {
        await categoryStore.fetchCategories()
    } catch (err) {
        console.error('Erreur lors du chargement:', err)
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des catégories', life: 3000 })
    }
})

// ==============================================
// FONCTIONS D'AFFICHAGE DES MODALES
// ==============================================
/**
 * Ouvre la modale de création ou modification de catégorie
 * @param string mode - "create" pour créer, "edit" pour modifier
 * @param Object|null category - Catégorie à modifier (seulement si mode = "edit")
 */
const openModal = (mode, category = null) => {
    modalMode.value = mode
    if (mode === 'edit' && category) {
        formData.value = {
            id: category.id,
            name: category.name,
            description: category.description || '',
            status: category.status || 'active',
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
        name: '',
        description: '',
        status: 'active',
    }
}

// ==============================================
// FONCTIONS D'ACTION (CRUD)
// ==============================================
/**
 * Soumet le formulaire : crée une nouvelle catégorie ou met à jour une catégorie existante
 */
const handleSubmit = async () => {
    try {
        if (modalMode.value === 'create') {
            await categoryStore.createCategory(formData.value)
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie créée avec succès', life: 3000 })
        } else {
            await categoryStore.updateCategory(formData.value.id, formData.value)
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie mise à jour avec succès', life: 3000 })
        }
        closeModal()
    } catch (err) {
        console.error('Erreur lors de la soumission:', err)
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
    }
}

/**
 * Ouvre la modale de confirmation de suppression
 * @param Object category - Catégorie à supprimer
 */
const confirmDelete = (category) => {
    categoryToDelete.value = category
    showDeleteModal.value = true
}

/**
 * Exécute la suppression de la catégorie
 */
const handleDelete = async () => {
    if (categoryToDelete.value) {
        try {
            await categoryStore.deleteCategory(categoryToDelete.value.id)
            showDeleteModal.value = false
            categoryToDelete.value = null
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Catégorie supprimée avec succès', life: 3000 })
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
            <i class="pi pi-tags mr-3 text-amber-700"></i>
            Gestion des Catégories
        </h1>

        <!-- Message d'erreur si le store a rencontré un problème -->
        <div v-if="categoryStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            {{ categoryStore.error }}
        </div>

        <!-- Barre de recherche des catégories -->
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
                            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton pour ajouter une nouvelle catégorie -->
        <div class="mb-6">
            <button
                @click="openModal('create')"
                class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition"
            >
                <i class="pi pi-plus"></i>
                Ajouter une catégorie
            </button>
        </div>

        <!-- Tableau affichant la liste des catégories -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nom</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Description</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Statut</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- État de chargement : affiché tant que les données ne sont pas prêtes -->
                        <tr v-if="categoryStore.loading">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                                <p>Chargement des catégories...</p>
                            </td>
                        </tr>
                        
                        <!-- Liste des catégories filtrées -->
                        <tr v-else v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ category.name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ category.description || '-' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        category.status === 'active' 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    {{ category.status === 'active' ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <!-- Bouton de modification -->
                                    <button
                                        @click="openModal('edit', category)"
                                        class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                                        title="Modifier"
                                    >
                                        <i class="pi pi-pencil"></i>
                                    </button>
                                    <!-- Bouton de suppression -->
                                    <button
                                        @click="confirmDelete(category)"
                                        class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"
                                        title="Supprimer"
                                    >
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Message si aucune catégorie ne correspond à la recherche -->
                        <tr v-if="!categoryStore.loading && filteredCategories.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i class="pi pi-inbox text-4xl mb-3"></i>
                                <p>Aucune catégorie trouvée</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modale de création/modification d'une catégorie -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Arrière-plan semi-transparent -->
            <div @click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            
            <!-- Contenu de la modale -->
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    {{ modalMode === 'create' ? 'Ajouter une catégorie' : 'Modifier la catégorie' }}
                </h2>
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Champ nom (prend toute la largeur en desktop) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                            <input
                                v-model="formData.name"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                            />
                        </div>
                        <!-- Champ statut -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                            <select
                                v-model="formData.status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white"
                            >
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>
                    </div>
                    <!-- Champ description (texte long) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea
                            v-model="formData.description"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
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
                            :disabled="categoryStore.loading"
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2"
                        >
                            <i v-if="categoryStore.loading" class="pi pi-spin pi-spinner"></i>
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
                    Êtes-vous sûr de vouloir supprimer <strong>{{ categoryToDelete?.name }}</strong> ? Cette action est irréversible.
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
                        :disabled="categoryStore.loading"
                        class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold rounded-lg transition"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
