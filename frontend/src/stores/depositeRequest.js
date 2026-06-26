import { defineStore } from 'pinia'
import { ref } from 'vue'
import client from '@/api/client'

export const useDepositeRequestStore = defineStore('depositeRequest', () => {
  const depositeRequests = ref([])
  const managers = ref([])
  const currentDepositeRequest = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const fetchDepositeRequests = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/deposite-requests')
      depositeRequests.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des demandes'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchManagers = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get('/users/managers')
      managers.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des responsables'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchDepositeRequest = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.get(`/deposite-requests/${id}`)
      currentDepositeRequest.value = response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de la demande'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const createDepositeRequest = async (data) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post('/deposite-requests', data)
      depositeRequests.value.unshift(response.data.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateDepositeRequest = async (id, data) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.put(`/deposite-requests/${id}`, data)
      const index = depositeRequests.value.findIndex(d => d.id === id)
      if (index !== -1) {
        depositeRequests.value[index] = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const assignRequest = async (id, managerId) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/deposite-requests/${id}/assign`, { manager_id: managerId })
      const index = depositeRequests.value.findIndex(d => d.id === id)
      if (index !== -1) {
        depositeRequests.value[index] = response.data.data
      }
      if (currentDepositeRequest.value?.id === id) {
        currentDepositeRequest.value = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de l\'affectation'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const reassignRequest = async (id, managerId) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/deposite-requests/${id}/reassign`, { manager_id: managerId })
      const index = depositeRequests.value.findIndex(d => d.id === id)
      if (index !== -1) {
        depositeRequests.value[index] = response.data.data
      }
      if (currentDepositeRequest.value?.id === id) {
        currentDepositeRequest.value = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la réaffectation'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const submitReview = async (id, decision, justification) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/deposite-requests/${id}/review`, { decision, justification })
      const index = depositeRequests.value.findIndex(d => d.id === id)
      if (index !== -1) {
        depositeRequests.value[index] = response.data.data
      }
      if (currentDepositeRequest.value?.id === id) {
        currentDepositeRequest.value = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la soumission de l\'avis'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const publishRequest = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/deposite-requests/${id}/publish`)
      const index = depositeRequests.value.findIndex(d => d.id === id)
      if (index !== -1) {
        depositeRequests.value[index] = response.data.data
      }
      if (currentDepositeRequest.value?.id === id) {
        currentDepositeRequest.value = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la publication'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const rejectRequest = async (id, justification) => {
    loading.value = true
    error.value = null
    try {
      const response = await client.post(`/deposite-requests/${id}/reject`, { justification })
      const index = depositeRequests.value.findIndex(d => d.id === id)
      if (index !== -1) {
        depositeRequests.value[index] = response.data.data
      }
      if (currentDepositeRequest.value?.id === id) {
        currentDepositeRequest.value = response.data.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du rejet'
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    depositeRequests,
    managers,
    currentDepositeRequest,
    loading,
    error,
    fetchDepositeRequests,
    fetchManagers,
    fetchDepositeRequest,
    createDepositeRequest,
    updateDepositeRequest,
    assignRequest,
    reassignRequest,
    submitReview,
    publishRequest,
    rejectRequest
  }
})
