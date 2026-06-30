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

const getRoleLabel = (role) => {
  const labels = {
    admin: 'Administrateur',
    responsable_demande: 'Responsable Demande',
    responsable_rh: 'Responsable RH',
    user: 'Utilisateur'
  };
  return labels[role] || role;
};

const getStatusLabel = (status) => {
  const labels = {
    active: 'Actif',
    pending: 'En attente',
    archived: 'Archivé'
  };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    archived: 'bg-gray-100 text-gray-800'
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
        <i class="pi pi-home mr-3 text-purple-700"></i>
        Tableau de bord Responsable RH
      </h1>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
      <p class="text-red-700 font-medium">{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <i class="pi pi-spin pi-spinner text-4xl text-purple-700 mb-4"></i>
      <p class="text-gray-600">Chargement des statistiques...</p>
    </div>

    <!-- Stats Cards -->
    <div v-else-if="statistics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Total utilisateurs</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.totalUsers }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-full">
            <i class="pi pi-users text-2xl text-blue-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Actifs</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.activeUsers }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-amber-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">En attente</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.pendingUsers }}</p>
          </div>
          <div class="bg-amber-100 p-3 rounded-full">
            <i class="pi pi-clock text-2xl text-amber-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-gray-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Archivés</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.archivedUsers }}</p>
          </div>
          <div class="bg-gray-100 p-3 rounded-full">
            <i class="pi pi-archive text-2xl text-gray-600"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Utilisateurs par rôle et récents -->
    <div v-else-if="statistics" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Utilisateurs par rôle -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Utilisateurs par rôle</h3>
        <div class="space-y-3">
          <div v-for="(count, role) in statistics.usersByRole" :key="role" class="flex items-center gap-4">
            <div class="flex-1">
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">{{ getRoleLabel(role) }}</span>
                <span class="text-sm font-semibold text-gray-800">{{ count }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="bg-purple-600 h-2.5 rounded-full transition-all duration-300"
                  :style="{ width: `${(count / statistics.totalUsers) * 100}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Utilisateurs récents -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Utilisateurs récents</h3>
        <div class="space-y-3">
          <div
            v-for="user in statistics.recentUsers"
            :key="user.id"
            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
          >
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-900">{{ user.first_name }} {{ user.last_name }}</h4>
                <p class="text-sm text-gray-600">{{ user.email }}</p>
                <p class="text-xs text-gray-500">
                  {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                </p>
              </div>
              <span
                :class="getStatusClass(user.status)"
                class="px-2 py-1 rounded-full text-xs font-semibold"
              >
                {{ getStatusLabel(user.status) }}
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
          to="/responsable_rh/utilisateurs"
          class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
        >
          <i class="pi pi-users text-xl text-purple-700"></i>
          <span class="font-medium text-gray-800">Gérer les utilisateurs</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>