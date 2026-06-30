<script setup>
import { computed, onMounted, ref } from 'vue'
import { useUserStore } from '@/stores/user'
import { useAuthStore } from '@/stores/auth'
import { Toast } from 'primevue'
import { Paginator } from 'primevue/paginator'
import { useToast } from 'primevue/usetoast'

// ========================================================================
// INITIALISATION DES STORES ET SERVICES
// ========================================================================
const userStore = useUserStore()
const toast = useToast()




// ========================================================================
// VARIABLES LOCALES
// ========================================================================
const isModalOpen = ref(false)
const modalMode = ref('create') // 'create' ou 'edit'
const showDeleteModal = ref(false)
const userToDelete = ref(null)

// Recherche et filtres
const searchQuery = ref('')
const filterRole = ref('')
const filterStatus = ref('')

// Données du formulaire
const formData = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  role: 'user',
  status: 'active',
})

const onPageChange = (event) => {
  userStore.fetchUsers(event.page + 1)
}

// ========================================================================
// PROPRIÉTÉ COMPUTÉE : FILTRER LES UTILISATEURS
// ========================================================================
const filteredUsers = computed(() => {
  return userStore.users.filter(user => {
    // Recherche par nom, prénom ou email
    const matchesSearch = 
      searchQuery.value === '' ||
      user.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())

    // Filtre par rôle
    const matchesRole = filterRole.value === '' || user.role === filterRole.value

    // Filtre par statut
    const matchesStatus = filterStatus.value === '' || user.status === filterStatus.value

    return matchesSearch && matchesRole && matchesStatus
  })
})

// ========================================================================
// CYCLE DE VIE
// ========================================================================
onMounted(async () => {
  try {
    await userStore.fetchUsers() // On charge les utilisateurs au chargement de la page
  } catch (err) {
    console.error('Erreur lors du chargement des utilisateurs:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des utilisateurs', life: 3000 })
  }
})

// ========================================================================
// FONCTIONS D'AFFICHAGE
// ========================================================================

// Ouvrir la modale
const openModal = (mode, user = null) => {
  modalMode.value = mode
  if (mode === 'edit' && user) {
    // Pré-remplir le formulaire avec les données de l'utilisateur
    formData.value = {
      first_name: user.first_name,
      last_name: user.last_name,
      email: user.email,
      phone: user.phone,
      password: '', // On ne pré-remplit pas le mot de passe
      role: user.role,
      status: user.status,
    }
    formData.value.userId = user.id // On stocke l'ID pour la modification
  } else {
    // Réinitialiser le formulaire pour la création
    resetForm()
  }
  isModalOpen.value = true
}

// Fermer la modale
const closeModal = () => {
  isModalOpen.value = false
  resetForm()
}

// Réinitialiser le formulaire
const resetForm = () => {
  formData.value = {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    role: 'user',
    status: 'active',
  }
}

// Obtenir la classe CSS du badge de rôle
const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-purple-100 text-purple-700',
    responsable_rh: 'bg-blue-100 text-blue-700',
    responsable_demande: 'bg-green-100 text-green-700',
    user: 'bg-gray-100 text-gray-700',
  }
  return classes[role] || classes.user
}

// Obtenir le label du rôle en français
const getRoleLabel = (role) => {
  const labels = {
    admin: 'Administrateur',
    responsable_rh: 'Responsable RH',
    responsable_demande: 'Responsable Demande',
    user: 'Utilisateur',
  }
  return labels[role] || role
}

// Obtenir la classe CSS du bouton de statut
const getStatusButtonClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-700 hover:bg-green-200',
    inactive: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    suspended: 'bg-red-100 text-red-700 hover:bg-red-200',
  }
  return classes[status] || classes.inactive
}

// Obtenir le label du statut en français
const getStatusLabel = (status) => {
  const labels = {
    active: 'Actif',
    inactive: 'Inactif',
    suspended: 'Suspendu',
  }
  return labels[status] || status
}

// ========================================================================
// FONCTIONS D'ACTION
// ========================================================================

// Soumettre le formulaire (créer ou modifier)
const handleSubmit = async () => {
  try {
    if (modalMode.value === 'create') {
      await userStore.createUser(formData.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur créé avec succès', life: 3000 })
    } else {
      await userStore.updateUser(formData.value.userId, formData.value)
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur mis à jour avec succès', life: 3000 })
    }
    closeModal() // On ferme la modale si ça a fonctionné
  } catch (err) {
    console.error('Erreur lors de la soumission:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
  }
}

// Basculer le statut d'un utilisateur
const toggleStatus = async (id) => {
  try {
    await userStore.toggleUserStatus(id)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Statut mis à jour avec succès', life: 3000 })
  } catch (err) {
    console.error('Erreur lors du changement de statut:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
  }
}

// Confirmer la suppression
const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

// Exécuter la suppression
const handleDelete = async () => {
  if (userToDelete.value) {
    try {
      await userStore.archiveUser(userToDelete.value.id)
      showDeleteModal.value = false
      userToDelete.value = null
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur archivé avec succès', life: 3000 })
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
      <i class="pi pi-users mr-3 text-amber-700"></i>
      Gestion des Utilisateurs
    </h1>

    <!-- MESSAGE D'ERREUR -->
    <div v-if="userStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
      {{ userStore.error }}
    </div>

    <!-- RECHERCHE ET FILTRES -->
    <div class="p-6 mb-6 bg-white rounded-xl shadow-lg">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- CHAMP DE RECHERCHE -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par nom, prénom ou email..."
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
            />
          </div>
        </div>

        <!-- FILTRE PAR RÔLE -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
          <select
            v-model="filterRole"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
          >
            <option value="">Tous les rôles</option>
            <option value="admin">Administrateur</option>
            <option value="responsable_rh">Responsable RH</option>
            <option value="responsable_demande">Responsable Demande</option>
            <option value="user">Utilisateur</option>
          </select>
        </div>

        <!-- FILTRE PAR STATUT -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select
            v-model="filterStatus"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
          >
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="inactive">Inactif</option>
            <option value="suspended">Suspendu</option>
          </select>
        </div>
      </div>
    </div>

    <!-- BOUTON AJOUTER UN UTILISATEUR -->
    <div class="mb-6">
      <button
        @click="openModal('create')"
        class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition"
      >
        <i class="pi pi-plus"></i>
        Ajouter un utilisateur
      </button>
    </div>

    <!-- TABLEAU DES UTILISATEURS -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nom & Prénom</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Téléphone</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Rôle</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Statut</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <!-- ÉTAT DE CHARGEMENT -->
            <tr v-if="userStore.loading">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                <p>Chargement des utilisateurs...</p>
              </td>
            </tr>

            <!-- LISTE DES UTILISATEURS -->
            <tr v-else v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-amber-700 rounded-full flex items-center justify-center text-white font-bold">
                    {{ user.first_name[0] }}{{ user.last_name[0] }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">{{ user.first_name }} {{ user.last_name }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-gray-600">{{ user.email }}</td>
              <td class="px-6 py-4 text-gray-600">{{ user.phone || '-' }}</td>
              <td class="px-6 py-4">
                <span :class="getRoleBadgeClass(user.role)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getRoleLabel(user.role) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <button
                  @click="toggleStatus(user.id)"
                  :class="getStatusButtonClass(user.status)"
                  class="px-3 py-1 rounded-full text-xs font-semibold transition"
                >
                  {{ getStatusLabel(user.status) }}
                </button>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <!-- BOUTON MODIFIER -->
                  <button
                    @click="openModal('edit', user)"
                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                    title="Modifier"
                  >
                    <i class="pi pi-pencil"></i>
                  </button>
                  <!-- BOUTON SUPPRIMER -->
                  <button
                    @click="confirmDelete(user)"
                    class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition"
                    title="Archiver"
                  >
                    <i class="pi pi-inbox"></i>
                  </button>
                </div>
              </td>
            </tr>

            <!-- AUCUN UTILISATEUR -->
            <tr v-if="!userStore.loading && filteredUsers.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p>Aucun utilisateur trouvé</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-6">
        <Paginator 
          :first="(userStore.pagination.currentPage - 1) * userStore.pagination.perPage" 
          :rows="userStore.pagination.perPage" 
          :total-rows="userStore.pagination.total"
          @page="onPageChange" 
        />
      </div>
    </div>

    <!-- MODALE DE CRÉATION / MODIFICATION -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- ARRIÈRE-PLAN -->
      <div @click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

      <!-- CONTENU DE LA MODALE -->
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-8">
        <!-- TITRE DE LA MODALE -->
        <h2 class="text-2xl font-bold text-gray-800  mb-6">
          {{ modalMode === 'create' ? 'Ajouter un utilisateur' : 'Modifier l\'utilisateur' }}
        </h2>

        <!-- FORMULAIRE -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- PRÉNOM -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
            <input
              v-model="formData.first_name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
            />
          </div>

          <!-- NOM -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
            <input
              v-model="formData.last_name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
            />
          </div>

          <!-- EMAIL -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input
              v-model="formData.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
            />
          </div>

          <!-- TÉLÉPHONE -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
            <input
              v-model="formData.phone"
              type="tel"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
            />
          </div>

          <!-- MOT DE PASSE (seulement pour la création) -->
          <div v-if="modalMode === 'create'">
            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe *</label>
            <input
              v-model="formData.password"
              type="password"
              required
              minlength="6"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition"
            />
          </div>

          <!-- RÔLE -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle *</label>
            <select
              v-model="formData.role"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition bg-white"
            >
              <option value="user">Utilisateur</option>
              <option value="responsable_demande">Responsable Demande</option>
              <option value="responsable_rh">Responsable RH</option>
              <option value="admin">Administrateur</option>
            </select>
          </div>

          <!-- STATUT -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
            <select
              v-model="formData.status"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition bg-white"
            >
              <option value="active">Actif</option>
              <option value="inactive">Inactif</option>
              <option value="suspended">Suspendu</option>
            </select>
          </div>

          <!-- BOUTONS DE LA MODALE -->
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
              :disabled="userStore.loading"
              class="flex-1 px-6 py-3 bg-amber-700 hover:bg-ambre-900 disabled:opacity-50 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2"
            >
              <i v-if="userStore.loading" class="pi pi-spin pi-spinner"></i>
              {{ modalMode === 'create' ? 'Créer' : 'Modifier' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODALE DE CONFIRMATION DE SUPPRESSION -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showDeleteModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-exclamation-triangle text-red-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Confirmer l'archivage ?</h3>
        <p class="text-gray-600 mb-6">
          Êtes-vous sûr de vouloir archiver <strong>{{ userToDelete?.first_name }} {{ userToDelete?.last_name }}</strong> ?
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
            :disabled="userStore.loading"
            class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold rounded-lg transition"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
