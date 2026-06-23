import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

export const useReferenceStore = defineStore('reference', () => {
  // STATE : état des données
  const references = ref([]) // Liste des références
  const currentReference = ref(null) // Référence sélectionnée (pour modification)
  const loading = ref(false) // Indique chargement en cours
  const error = ref(null) // Stocke les erreurs

  // ========================================================================
  // ACTION 1 : RÉCUPÉRER TOute LES references
  // ========================================================================
  const fetchReferences = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/references')
      references.value = response.data.data // On stocke la liste des références
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des références'
      console.error('Erreur fetchReferences:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 2 : RÉCUPÉRER UNe SEULe reference
  // ========================================================================
  const fetchReference = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get(`/references/${id}`)
      currentReference.value = response.data.data // On stocke la référence sélectionnée
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de la référence'
      console.error('Erreur fetchReference:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 3 : CRÉER UNe NOUVELle reference
  // ========================================================================
  const createReference = async (referenceData) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post('/references', referenceData)
      references.value.push(response.data.data) // On ajoute la nouvelle référence à la liste
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de la référence'
      console.error('Erreur createReference:', err)
      throw err // On re-lance l'erreur pour que le composant puisse la gérer
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 4 : MODIFIER UNe reference EXISTANTe
  // ========================================================================
  const updateReference = async (id, referenceData) => {
    loading.value = true
    error.value = null
    try {
      const data = { ...referenceData }
      const response = await client.put(`/references/${id}`, data)
      const index = references.value.findIndex(r => r.id === id)
      if (index !== -1) {
        references.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification de la référence'
      console.error('Erreur updateReference:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 5 : SUPPRIMER UNE RÉFÉRENCE
  // ========================================================================
  const deleteReference = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.delete(`/references/${id}`)
      references.value = references.value.filter(r => r.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression de la référence'
      console.error('Erreur deleteReference:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 6 : ACTIVER / DÉSACTIVER UNE RÉFÉRENCE
  // ========================================================================
  const toggleReferenceStatus = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/references/${id}/toggle-status`)
      // On met à jour la référence dans la liste locale
      const index = references.value.findIndex(r => r.id === id)
      if (index !== -1) {
        references.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du changement de statut'
      console.error('Erreur toggleReferenceStatus:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // On retourne toutes les variables et fonctions pour les utiliser dans les composants
  return {
    references,
    currentReference,
    loading,
    error,
    fetchReferences,
    fetchReference,
    createReference,
    updateReference,
    deleteReference,
    toggleReferenceStatus,
  }
})
