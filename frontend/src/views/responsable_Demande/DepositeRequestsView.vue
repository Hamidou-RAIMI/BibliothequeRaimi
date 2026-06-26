<script setup>
import { computed, onMounted, ref } from 'vue'
import { useDepositeRequestStore } from '@/stores/depositeRequest'
import { useAuthStore } from '@/stores/auth'
import { Toast } from 'primevue'
import { useToast } from 'primevue/usetoast'

const depositeRequestStore = useDepositeRequestStore()
const authStore = useAuthStore()
const toast = useToast()

const showDetailModal = ref(false)
const showReviewModal = ref(false)
const searchQuery = ref('')
const reviewDecision = ref('approved')
const reviewJustification = ref('')

const filteredDepositeRequests = computed(() => {
  return depositeRequestStore.depositeRequests.filter(demande => {
    const isAssignedToMe = demande.assigned_manager_id === authStore.user.id
    const isRelevantStatus = ['assigned', 'reassigned'].includes(demande.status)
    const matchesSearch =
      searchQuery.value === '' ||
      demande.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (demande.description && demande.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    return isAssignedToMe && isRelevantStatus && matchesSearch
  })
})

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    assigned: 'Affectée',
    reassigned: 'Réaffectée',
    approved_by_manager: 'Approuvée par responsable',
    rejected_by_manager: 'Rejetée par responsable',
    second_review: 'Deuxième avis',
    approved: 'Approuvée',
    rejected: 'Rejetée',
    published: 'Publiée'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    assigned: 'bg-blue-100 text-blue-800',
    reassigned: 'bg-purple-100 text-purple-800',
    approved_by_manager: 'bg-green-100 text-green-800',
    rejected_by_manager: 'bg-red-100 text-red-800',
    second_review: 'bg-purple-100 text-purple-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    published: 'bg-green-100 text-green-800'
  }
  return classes[status] || classes.pending
}

onMounted(async () => {
  try {
    await depositeRequestStore.fetchDepositeRequests()
  } catch (err) {
    console.error('Erreur lors du chargement:', err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors du chargement des données', life: 3000 })
  }
})

const openDetailModal = (demande) => {
  depositeRequestStore.currentDepositeRequest = demande
  showDetailModal.value = true
}

const openReviewModal = () => {
  reviewDecision.value = 'approved'
  reviewJustification.value = ''
  showReviewModal.value = true
}

const handleReview = async () => {
  try {
    await depositeRequestStore.submitReview(
      depositeRequestStore.currentDepositeRequest.id,
      reviewDecision.value,
      reviewJustification.value
    )
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Avis soumis avec succès', life: 3000 })
    showReviewModal.value = false
    showDetailModal.value = false
  } catch (err) {
    console.error(err)
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la soumission de l\'avis', life: 3000 })
  }
}
</script>

<template>
  <div class="p-6 max-w-8xl mx-auto">
    <Toast />
    <h1 class="text-3xl font-bold text-gray-800 mb-8">
      <i class="pi pi-file-edit mr-3 text-amber-700"></i>
      Demandes de Dépôt à traiter
    </h1>

    <div v-if="depositeRequestStore.error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
      {{ depositeRequestStore.error }}
    </div>

    <div class="p-6 mb-6 bg-white rounded-xl shadow-lg">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
        <div class="relative">
          <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher par titre ou description..."
            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
          />
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Titre</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Demandeur</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Statut</th>
              <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-if="depositeRequestStore.loading">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                <p>Chargement des demandes...</p>
              </td>
            </tr>
            <tr v-else v-for="demande in filteredDepositeRequests" :key="demande.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4">
                <div>
                  <p class="font-semibold text-gray-800">{{ demande.title }}</p>
                  <p v-if="demande.description" class="text-sm text-gray-500 line-clamp-2">{{ demande.description }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-gray-600">
                {{ demande.applicant?.first_name }} {{ demande.applicant?.last_name }}
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(demande.status)" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ getStatusLabel(demande.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openDetailModal(demande)"
                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                    title="Voir détails"
                  >
                    <i class="pi pi-eye"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!depositeRequestStore.loading && filteredDepositeRequests.length === 0">
              <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                <i class="pi pi-inbox text-4xl mb-3"></i>
                <p>Aucune demande à traiter</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showDetailModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Détails de la demande</h2>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">
            &times;
          </button>
        </div>
        <div v-if="depositeRequestStore.currentDepositeRequest" class="space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-500">Titre</label>
            <p class="text-lg font-semibold text-gray-800">{{ depositeRequestStore.currentDepositeRequest.title }}</p>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.description">
            <label class="text-sm font-medium text-gray-500">Description</label>
            <p class="text-gray-700">{{ depositeRequestStore.currentDepositeRequest.description }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500">Statut</label>
            <p>
              <span
                :class="getStatusClass(depositeRequestStore.currentDepositeRequest.status)"
                class="px-3 py-1 rounded-full text-xs font-semibold"
              >
                {{ getStatusLabel(depositeRequestStore.currentDepositeRequest.status) }}
              </span>
            </p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-500">Demandeur</label>
              <p class="text-gray-700">
                {{ depositeRequestStore.currentDepositeRequest.applicant?.first_name }}
                {{ depositeRequestStore.currentDepositeRequest.applicant?.last_name }}
              </p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500">Date</label>
              <p class="text-gray-700">
                {{ new Date(depositeRequestStore.currentDepositeRequest.created_at).toLocaleDateString('fr-FR') }}
              </p>
            </div>
          </div>
          <div class="flex flex-wrap gap-3 mt-6">
            <button
              @click="openReviewModal"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
            >
              Donner un avis
            </button>
          </div>
          <div v-if="depositeRequestStore.currentDepositeRequest.reviews && depositeRequestStore.currentDepositeRequest.reviews.length > 0" class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Historique des avis</h3>
            <div class="space-y-4">
              <div
                v-for="review in depositeRequestStore.currentDepositeRequest.reviews"
                :key="review.id"
                class="p-4 bg-gray-50 rounded-lg"
              >
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <p class="font-semibold text-gray-800">
                      {{ review.reviewer?.first_name }} {{ review.reviewer?.last_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ review.reviewer_role === 'admin' ? 'Administrateur' : 'Responsable demande' }}
                    </p>
                  </div>
                  <span
                    :class="review.decision === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ review.decision === 'approved' ? 'Approuvé' : 'Rejeté' }}
                  </span>
                </div>
                <p v-if="review.justification" class="text-sm text-gray-600">{{ review.justification }}</p>
                <p class="text-xs text-gray-400 mt-2">
                  {{ new Date(review.created_at).toLocaleDateString('fr-FR') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showReviewModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div @click="showReviewModal = false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Donner un avis</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Décision</label>
            <select
              v-model="reviewDecision"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white"
            >
              <option value="approved">Approuver</option>
              <option value="rejected">Rejeter</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Justification</label>
            <textarea
              v-model="reviewJustification"
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition"
              placeholder="Entrez une justification..."
            ></textarea>
          </div>
        </div>
        <div class="flex gap-4 mt-6">
          <button
            @click="showReviewModal = false"
            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition"
          >
            Annuler
          </button>
          <button
            @click="handleReview"
            :disabled="!reviewJustification || depositeRequestStore.loading"
            class="flex-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition disabled:opacity-50"
          >
            Soumettre
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
