<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { Toast } from 'primevue'
import { Paginator } from 'primevue/paginator'
import { useToast } from 'primevue/usetoast'

const userStore = useUserStore()
const toast = useToast()
const router = useRouter()

// Fonction pour revenir à la liste des utilisateurs
const goBack = () => {
  router.push('/admin/utilisateurs')
}

const searchQuery = ref('')
const filterRole = ref('')
const showRestoreModal = ref(false)
const userToRestore = ref(null)

const onPageChange = (event) => {
  userStore.fetchArchivedUsers(event.page + 1)
}

const filteredArchivedUsers = computed(() => {
  return userStore.archivedUsers.filter(user => {
    const matchesSearch =
      searchQuery.value === '' ||
      user.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesRole = filterRole.value === '' || user.role === filterRole.value

    return matchesSearch && matchesRole
  })
})

onMounted(async () => {
  try {
    await userStore.fetchArchivedUsers()
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des utilisateurs archivés', life: 3000 })
  }
})

const confirmRestore = (user) => {
  userToRestore.value = user
  showRestoreModal.value = true
}

const handleRestore = async () => {
  if (userToRestore.value) {
    try {
      await userStore.restoreUser(userToRestore.value.id)
      showRestoreModal.value = false
      userToRestore.value = null
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur désarchivé avec succès', life: 3000 })
    } catch (err) {
      console.error('Erreur lors de la désarchivage:', err)
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de l\'opération', life: 3000 })
    }
  }
}

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-purple-100 text-purple-700',
    responsable_rh: 'bg-blue-100 text-blue-700',
    responsable_demande: 'bg-green-100 text-green-700',
    user: 'bg-gray-100 text-gray-700',
  }
  return classes[role] || classes.user
}

const getRoleLabel = (role) => {
  const labels = {
    admin: 'Administrateur',
    responsable_rh: 'Responsable RH',
    responsable_demande: 'Responsable Demande',
    user: 'Utilisateur',
  }
  return labels[role] || role
}
</script>

<template>
  <div class="p-6 max-w-8xl mx-auto">
    <Toast />
    <div class="flex items-center gap-4 mb-8">
      <button
        @click="goBack"
        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
        title="Retour"
      >
        <i class="pi pi-arrow-left text-xl"></i>
      </button>
      <h1 class="text-3xl font-bold text-gray-800">
        <i class="pi pi-users mr-3 text-gray-700"></i>
        Utilisateurs Archivés
      </h1>
    </div>

    <div v-if="userStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
      {{ userStore.error }}
    </div>

    <div class="p-6 mb-6 bg-white rounded-xl shadow-lg">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher par nom, prénom ou email..."
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 outline-none transition"
            />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
          <select
            v-model="filterRole"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 outline-none transition bg-white"
          >
            <option value="">Tous les rôles</option>
            <option value="admin">Administrateur</option>
            <option value="responsable_rh">Responsable RH</option>
            <option value="responsable_demande">Responsable Demande</option>
            <option value="user">Utilisateur</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nom & Prénom</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Rôle</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-if="userStore.loading">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                <p>Chargement des utilisateurs archivés...</p>
              </td>
            </tr>

            <tr v-else v-for="user in filteredArchivedUsers" :key="user.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center text-white font-bold">
                    {{ user.first_name[0] }}{{ user.last_name[0] }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">{{ user.first_name }} {{ user.last_name }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-gray-600">{{ user.email }}</td>
              <td class="px-6 py-4">
                <span :class="getRoleBadgeClass(user.role)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getRoleLabel(user.role) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  @click="confirmRestore(user)"
                  class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition"
                  title="Désarchiver"
                >
                  <i class="pi pi-undo"></i>
                </button>
              </td>
            </tr>

            <tr v-if="!userStore.loading && filteredArchivedUsers.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p>Aucun utilisateur archivé trouvé</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-6">
        <Paginator 
          :first="(userStore.archivedPagination.currentPage - 1) * userStore.archivedPagination.perPage" 
          :rows="userStore.archivedPagination.perPage" 
          :total-rows="userStore.archivedPagination.total"
          @page="onPageChange" 
        />
      </div>
    </div>

    <div v-if="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showRestoreModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-undo text-green-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Confirmer la désarchivage ?</h3>
        <p class="text-gray-600 mb-6">
          Êtes-vous sûr de vouloir désarchiver <strong>{{ userToRestore?.first_name }} {{ userToRestore?.last_name }}</strong> ?
        </p>
        <div class="flex gap-4">
          <button
            @click="showRestoreModal = false"
            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
          >
            Annuler
          </button>
          <button
            @click="handleRestore"
            :disabled="userStore.loading"
            class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white font-semibold rounded-lg transition"
          >
            Désarchiver
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
