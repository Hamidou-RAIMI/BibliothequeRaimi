import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

export const useReferenceStore = defineStore('reference', () => {
  // STATE : état des données
  const references = ref([]) // Liste des références
  const archivedReferences = ref([]) // Liste des références archivées
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
      // Création d'un objet FormData pour envoyer les données et le fichier
      const formData = new FormData()
      
      // Ajout des champs de référence
      Object.keys(referenceData).forEach(key => {
        if (referenceData[key] !== null && referenceData[key] !== undefined) {
          if (key === 'authors') {
            // Si c'est un tableau d'auteurs, on le convertit en JSON string
            formData.append('authors', JSON.stringify(referenceData[key]))
          } else {
            formData.append(key, referenceData[key])
          }
        }
      })

      const response = await client.post('/references', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
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
      // Création d'un objet FormData pour envoyer les données et le fichier
      const formData = new FormData()
      
      // Ajout des champs de référence
      Object.keys(referenceData).forEach(key => {
        if (referenceData[key] !== null && referenceData[key] !== undefined) {
          if (key === 'authors') {
            // Si c'est un tableau d'auteurs, on le convertit en JSON string
            formData.append('authors', JSON.stringify(referenceData[key]))
          } else {
            formData.append(key, referenceData[key])
          }
        }
      })

      // Utilisation de POST avec _method PUT pour Laravel (car Laravel ne supporte pas PUT avec multipart/form-data nativment)
      formData.append('_method', 'PUT')
      
      const response = await client.post(`/references/${id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
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

  // ========================================================================
  // ACTION 7 : RÉCUPÉRER LES RÉFÉRENCES ARCHIVÉES
  // ========================================================================
  const fetchArchivedReferences = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/references/archived')
      archivedReferences.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des références archivées'
      console.error('Erreur fetchArchivedReferences:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 8 : ARCHIVER UNE RÉFÉRENCE
  // ========================================================================
  const archiveReference = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.post(`/references/${id}/archive`)
      references.value = references.value.filter(r => r.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de l\'archivage de la référence'
      console.error('Erreur archiveReference:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // ========================================================================
  // ACTION 9 : RÉSTAURER UNE RÉFÉRENCE ARCHIVÉE
  // ========================================================================
  const restoreReference = async (id) => {
    loading.value = true
    error.value = null
    try {
      await client.post(`/references/${id}/restore`)
      archivedReferences.value = archivedReferences.value.filter(r => r.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la restauration de la référence'
      console.error('Erreur restoreReference:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // On retourne toutes les variables et fonctions pour les utiliser dans les composants
  return {
    references,
    archivedReferences,
    currentReference,
    loading,
    error,
    fetchReferences,
    fetchReference,
    createReference,
    updateReference,
    deleteReference,
    toggleReferenceStatus,
    fetchArchivedReferences,
    archiveReference,
    restoreReference,
  }
})
