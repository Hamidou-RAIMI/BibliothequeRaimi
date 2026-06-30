import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

// ==============================================
// STORE PINIA POUR LA GESTION DES CATÉGORIES
// ==============================================
// Ce store centralise l'état et les actions liées aux catégories
export const useCategoryStore = defineStore('category', () => {
    // ========== ÉTAT (STATE) ==========
    const categories = ref([])       // Liste de toutes les catégories
    const currentCategory = ref(null) // Catégorie actuellement sélectionnée (pour modification)
    const loading = ref(false)        // Indique si une requête est en cours
    const error = ref(null)           // Stocke les erreurs éventuelles

    // ========== ACTIONS ==========

    /**
     * Récupère toutes les catégories depuis l'API
     */
    const fetchCategories = async () => {
        loading.value = true
        error.value = null
        try {
            const response = await client.get('/categories')
            categories.value = response.data.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors du chargement des catégories'
            console.error('Erreur fetchCategories:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Récupère une catégorie spécifique par son ID
     * @param {number} id - Identifiant de la catégorie
     */
    const fetchCategory = async (id) => {
        loading.value = true
        error.value = null
        try {
            const response = await client.get(`/categories/${id}`)
            currentCategory.value = response.data.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors du chargement de la catégorie'
            console.error('Erreur fetchCategory:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Crée une nouvelle catégorie
     * @param {Object} categoryData - Données de la catégorie à créer
     */
    const createCategory = async (categoryData) => {
        loading.value = true
        error.value = null
        try {
            const response = await client.post('/categories', categoryData)
            categories.value.push(response.data.data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la création de la catégorie'
            console.error('Erreur createCategory:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Met à jour une catégorie existante
     * @param {number} id - Identifiant de la catégorie
     * @param {Object} categoryData - Nouvelles données de la catégorie
     */
    const updateCategory = async (id, categoryData) => {
        loading.value = true
        error.value = null
        try {
            const response = await client.put(`/categories/${id}`, categoryData)
            const index = categories.value.findIndex(c => c.id === id)
            if (index !== -1) {
                categories.value[index] = response.data.data
            }
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la modification de la catégorie'
            console.error('Erreur updateCategory:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    /**
     * Supprime une catégorie
     * @param {number} id - Identifiant de la catégorie à supprimer
     */
    const deleteCategory = async (id) => {
        loading.value = true
        error.value = null
        try {
            await client.delete(`/categories/${id}`)
            categories.value = categories.value.filter(c => c.id !== id)
        } catch (err) {
            error.value = err.response?.data?.message || 'Erreur lors de la suppression de la catégorie'
            console.error('Erreur deleteCategory:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    // ========== EXPORT DES ÉLÉMENTS UTILISABLES ==========
    return {
        categories,
        currentCategory,
        loading,
        error,
        fetchCategories,
        fetchCategory,
        createCategory,
        updateCategory,
        deleteCategory,
    }
})
