import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

// ==============================================
// STORE PINIA POUR LA GESTION DES ÉDITEURS
// ==============================================
// Ce store centralise l'état et les actions liées aux éditeurs
export const usePublisherStore = defineStore('publisher', () => {
  // ========== ÉTAT (STATE) ==========
  const publishers = ref([])       // Liste de tous les éditeurs
  const currentPublisher = ref(null) // Éditeur actuellement sélectionné (pour modification)
  const loading = ref(false)        // Indique si une requête est en cours
  const error = ref(null)           // Stocke les erreurs éventuelles

  // ========== ACTIONS ==========

  /**
   * Récupère tous les éditeurs depuis l'API
   */
  const fetchPublishers = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/publishers')
      publishers.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des éditeurs'
      console.error('Erreur fetchPublishers:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Récupère un éditeur spécifique par son ID
   * @param {number} id - Identifiant de l'éditeur
   */
  const fetchPublisher = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get(`/publishers/${id}`)
      currentPublisher.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de l\'éditeur'
      console.error('Erreur fetchPublisher:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crée un nouvel éditeur
   * @param {Object} publisherData - Données de l'éditeur à créer
   */
  const createPublisher = async (publisherData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post('/publishers', publisherData)
      publishers.value.push(response.data.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de l\'éditeur'
      console.error('Erreur createPublisher:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Met à jour un éditeur existant
   * @param {number} id - Identifiant de l'éditeur
   * @param {Object} publisherData - Nouvelles données de l'éditeur
   */
  const updatePublisher = async (id, publisherData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.put(`/publishers/${id}`, publisherData)
      const index = publishers.value.findIndex(p => p.id === id)
      if (index !== -1) {
        publishers.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification de l\'éditeur'
      console.error('Erreur updatePublisher:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Supprime un éditeur
   * @param {number} id - Identifiant de l'éditeur à supprimer
   */
  const deletePublisher = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.delete(`/publishers/${id}`)
      publishers.value = publishers.value.filter(p => p.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression de l\'éditeur'
      console.error('Erreur deletePublisher:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== EXPORT DES ÉLÉMENTS UTILISABLES ==========
  return {
    publishers,
    currentPublisher,
    loading,
    error,
    fetchPublishers,
    fetchPublisher,
    createPublisher,
    updatePublisher,
    deletePublisher,
  }
})
