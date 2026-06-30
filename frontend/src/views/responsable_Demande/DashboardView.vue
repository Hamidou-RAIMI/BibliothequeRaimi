<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import client from '@/api/client';

const router = useRouter();
const authStore = useAuthStore();

const statistics = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchStatistics = async () => {
  try {
    const response = await client.get('/statistics');
    statistics.value = response.data.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des statistiques';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

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
  };
  return labels[status] || status;
};

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
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(async () => {
  await fetchStatistics();
});
</script>

<template>
  <div class="p-6 space-y-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="pi pi-home mr-3 text-blue-700"></i>
        Tableau de bord Responsable Demande
      </h1>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
      <p class="text-red-700 font-medium">{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <i class="pi pi-spin pi-spinner text-4xl text-blue-700 mb-4"></i>
      <p class="text-gray-600">Chargement des statistiques...</p>
    </div>

    <!-- Stats Cards -->
    <div v-else-if="statistics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Demandes affectées</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.assignedRequests }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <i class="pi pi-folder text-2xl text-blue-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-amber-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">À examiner</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.pendingReviewRequests }}</p>
          </div>
          <div class="bg-amber-100 p-3 rounded-full">
            <i class="pi pi-clock text-2xl text-amber-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Approuvées</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.approvedRequests }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-red-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Rejetées</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.rejectedRequests }}</p>
          </div>
          <div class="bg-red-100 p-3 rounded-full">
            <i class="pi pi-times-circle text-2xl text-red-600"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Demandes par statut -->
    <div v-else-if="statistics" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Demandes par statut</h3>
        <div class="space-y-3">
          <div v-for="(count, status) in statistics.requestsByStatus" :key="status" class="flex items-center gap-4">
            <div class="flex-1">
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">{{ getStatusLabel(status) }}</span>
                <span class="text-sm font-semibold text-gray-800">{{ count }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="h-2.5 rounded-full transition-all duration-300"
                  :class="
                    status === 'pending' ? 'bg-amber-600' :
                    status === 'assigned' ? 'bg-blue-600' :
                    status === 'reassigned' ? 'bg-purple-600' :
                    status === 'approved_by_manager' ? 'bg-green-600' :
                    status === 'rejected_by_manager' ? 'bg-red-600' :
                    status === 'published' ? 'bg-green-500' :
                    status === 'rejected' ? 'bg-red-500' :
                    'bg-gray-600'
                  "
                  :style="{ width: `${(count / statistics.assignedRequests) * 100}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Requests -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Demandes récentes</h3>
        <div class="space-y-3">
          <div
            v-for="request in statistics.recentRequests"
            :key="request.id"
            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
          >
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-900">{{ request.title }}</h4>
                <p class="text-sm text-gray-600">
                  Par {{ request.applicant?.first_name }} {{ request.applicant?.last_name }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ new Date(request.created_at).toLocaleDateString('fr-FR') }}
                </p>
              </div>
              <span
                :class="getStatusClass(request.status)"
                class="px-2 py-1 rounded-full text-xs font-semibold"
              >
                {{ getStatusLabel(request.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div v-else-if="statistics" class="bg-white rounded-xl shadow-md p-6 mt-6">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Actions rapides</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <RouterLink
          to="/responsable_demande/demandes"
          class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
        >
          <i class="pi pi-file-edit text-xl text-blue-700"></i>
          <span class="font-medium text-gray-800">Voir toutes les demandes</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>