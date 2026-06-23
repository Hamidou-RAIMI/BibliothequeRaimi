import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

// ==============================================
// STORE PINIA POUR LA GESTION DES AUTEURS
// ==============================================
// Ce store centralise l'état et les actions liées aux auteurs
export const useAuthorStore = defineStore('author', () => {
  // ========== ÉTAT (STATE) ==========
  const authors = ref([])       // Liste de tous les auteurs
  const currentAuthor = ref(null) // Auteur actuellement sélectionné (pour modification)
  const loading = ref(false)     // Indique si une requête est en cours
  const error = ref(null)        // Stocke les erreurs éventuelles

  // ========== ACTIONS ==========

  /**
   * Récupère tous les auteurs depuis l'API
   */
  const fetchAuthors = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/authors')
      authors.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des auteurs'
      console.error('Erreur fetchAuthors:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Récupère un auteur spécifique par son ID
   * @param {number} id - Identifiant de l'auteur
   */
  const fetchAuthor = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get(`/authors/${id}`)
      currentAuthor.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de l\'auteur'
      console.error('Erreur fetchAuthor:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Crée un nouvel auteur
   * @param {Object} authorData - Données de l'auteur à créer
   */
  const createAuthor = async (authorData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post('/authors', authorData)
      authors.value.push(response.data.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de l\'auteur'
      console.error('Erreur createAuthor:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Met à jour un auteur existant
   * @param {number} id - Identifiant de l'auteur
   * @param {Object} authorData - Nouvelles données de l'auteur
   */
  const updateAuthor = async (id, authorData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.put(`/authors/${id}`, authorData)
      const index = authors.value.findIndex(a => a.id === id)
      if (index !== -1) {
        authors.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification de l\'auteur'
      console.error('Erreur updateAuthor:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Supprime un auteur
   * @param {number} id - Identifiant de l'auteur à supprimer
   */
  const deleteAuthor = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.delete(`/authors/${id}`)
      authors.value = authors.value.filter(a => a.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression de l\'auteur'
      console.error('Erreur deleteAuthor:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========== EXPORT DES ÉLÉMENTS UTILISABLES ==========
  return {
    authors,
    currentAuthor,
    loading,
    error,
    fetchAuthors,
    fetchAuthor,
    createAuthor,
    updateAuthor,
    deleteAuthor,
  }
})
