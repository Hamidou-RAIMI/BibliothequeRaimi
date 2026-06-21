import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

export const useUserStore = defineStore('user', () => {
  // STATE : état des données
  const users = ref([]) // Liste des utilisateurs
  const currentUser = ref(null) // Utilisateur sélectionné (pour modification)
  const loading = ref(false) // Indique chargement en cours
  const error = ref(null) // Stocke les erreurs

  // ========================================================================
  // ACTION 1 : RÉCUPÉRER TOUS LES UTILISATEURS
  // ========================================================================
  const fetchUsers = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/users')
      users.value = response.data.data // On stocke la liste des utilisateurs
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des utilisateurs'
      console.error('Erreur fetchUsers:', err)
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 2 : RÉCUPÉRER UN SEUL UTILISATEUR
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
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 3 : CRÉER UN NOUVEL UTILISATEUR
  // ========================================================================
  const createUser = async (userData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post('/users', userData)
      users.value.push(response.data.data) // On ajoute le nouvel utilisateur à la liste
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
  // ACTION 4 : MODIFIER UN UTILISATEUR EXISTANT
  // ========================================================================
  const updateUser = async (id, userData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.put(`/users/${id}`, userData)
      // On met à jour l'utilisateur dans la liste locale
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
  // ACTION 5 : SUPPRIMER UN UTILISATEUR
  // ========================================================================
  const deleteUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.delete(`/users/${id}`)
      // On retire l'utilisateur de la liste locale
      users.value = users.value.filter(u => u.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression de l\'utilisateur'
      console.error('Erreur deleteUser:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 6 : ACTIVER / DÉSACTIVER UN UTILISATEUR
  // ========================================================================
  const toggleUserStatus = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/users/${id}/toggle-status`)
      // On met à jour l'utilisateur dans la liste locale
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
    currentUser,
    loading,
    error,
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    toggleUserStatus,
  }
})
