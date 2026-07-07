import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

export const useUserStore = defineStore('user', () => {
  // STATE : état des données
  const users = ref([]) // Liste des utilisateurs (non archivés)
  const archivedUsers = ref([]) // Liste des utilisateurs archivés
  const currentUser = ref(null) // Utilisateur sélectionné (pour modification)
  const loading = ref(false) // Indique chargement en cours
  const error = ref(null) // Stocke les erreurs
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
    hasMorePages: false,
  })
  const archivedPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
    hasMorePages: false,
  })

  // ========================================================================
  // ACTION 1 : RÉCUPÉRER TOUS LES UTILISATEURS (NON ARCHIVÉS)
  // ========================================================================
  const fetchUsers = async (page = 1, filters = {}) => {
     loading.value = true
  error.value = null
  
  try {
    const params = {
      page,
      per_page: pagination.value.perPage,
      ...filters,
    }
    
    // Enlever les paramètres vides
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === '' || params[key] === undefined) {
        delete params[key]
      }
    })
    
    const response = await client.get('/users', { params })
    users.value = response.data.users
    pagination.value = response.data.pagination
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des utilisateurs'
    console.error('Erreur fetch users:', err)
    
  } finally {
    loading.value = false
  }
  }

  // ========================================================================
  // ACTION 2 : RÉCUPÉRER TOUS LES UTILISATEURS ARCHIVÉS
  // ========================================================================
  const fetchArchivedUsers = async (page = 1) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get(`/users/archived?page=${page}&per_page=${archivedPagination.value.perPage}`)
      archivedUsers.value = response.data.data
      archivedPagination.value = {
        currentPage: response.data.pagination.current_page,
        lastPage: response.data.pagination.last_page,
        perPage: response.data.pagination.per_page,
        total: response.data.pagination.total,
        hasMorePages: response.data.pagination.has_more_pages,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des utilisateurs archivés'
      console.error('Erreur fetchArchivedUsers:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 3 : RÉCUPÉRER UN SEUL UTILISATEUR
  // ========================================================================
  const fetchUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get(`/users/${id}`)
      currentUser.value = response.data.data // On stocke l'utilisateur sélectionné
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de l\'utilisateur'
      console.error('Erreur fetchUser:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 4 : CRÉER UN NOUVEL UTILISATEUR
  // ========================================================================
  const createUser = async (userData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post('/users', userData)
      users.value.unshift(response.data.data) // On ajoute le nouvel utilisateur au debut de la liste
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de l\'utilisateur'
      console.error('Erreur createUser:', err)
      throw err // On re-lance l'erreur pour que le composant puisse la gérer
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 5 : MODIFIER UN UTILISATEUR EXISTANT
  // ========================================================================
  const updateUser = async (id, userData) => {
    loading.value = true
    error.value = null
    try {
      const data = { ...userData }
      if (data.password === '') {
        delete data.password
      }
      const response = await client.put(`/users/${id}`, data)
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification de l\'utilisateur'
      console.error('Erreur updateUser:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 6 : ARCHIVER UN UTILISATEUR
  // ========================================================================
  const archiveUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.delete(`/users/${id}`)
      users.value = users.value.filter(u => u.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de l\'archivage de l\'utilisateur'
      console.error('Erreur archiveUser:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 7 : DÉSARCHIVER UN UTILISATEUR
  // ========================================================================
  const restoreUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/users/${id}/restore`)
      archivedUsers.value = archivedUsers.value.filter(u => u.id !== id)
      users.value.push(response.data.data)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la désarchivage de l\'utilisateur'
      console.error('Erreur restoreUser:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 8 : ACTIVER / DÉSACTIVER UN UTILISATEUR
  // ========================================================================
  const toggleUserStatus = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/users/${id}/toggle-status`)
      const index = users.value.findIndex(u => u.id === id)
      if (index !== -1) {
        users.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du changement de statut'
      console.error('Erreur toggleUserStatus:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // On retourne toutes les variables et fonctions pour les utiliser dans les composants
  return {
    users,
    archivedUsers,
    currentUser,
    loading,
    error,
    pagination,
    archivedPagination,
    fetchUsers,
    fetchArchivedUsers,
    fetchUser,
    createUser,
    updateUser,
    archiveUser,
    restoreUser,
    toggleUserStatus,
  }
})
