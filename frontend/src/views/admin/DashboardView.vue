<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import client from '@/api/client';

const router = useRouter();

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

onMounted(async () => {
  await fetchStatistics();
});
</script>

<template>
  <div class="p-6 space-y-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="pi pi-home mr-3 text-amber-700"></i>
        Tableau de bord administrateur
      </h1>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
      <p class="text-red-700 font-medium">{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <i class="pi pi-spin pi-spinner text-4xl text-amber-700 mb-4"></i>
      <p class="text-gray-600">Chargement des statistiques...</p>
    </div>

    <!-- Stats Cards -->
    <div v-else-if="statistics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Utilisateurs total</p>
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
            <p class="text-sm text-gray-500 font-medium">Références publiées</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.publishedReferences }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-full">
            <i class="pi pi-book text-2xl text-green-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-amber-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Demandes en attente</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.pendingRequests }}</p>
          </div>
          <div class="bg-amber-100 p-3 rounded-full">
            <i class="pi pi-clock text-2xl text-amber-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-purple-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Demandes total</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.totalDepositeRequests }}</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-full">
            <i class="pi pi-file-edit text-2xl text-purple-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-indigo-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Nouvelles références</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.newReferences }}</p>
          </div>
          <div class="bg-indigo-100 p-3 rounded-full">
            <i class="pi pi-star text-2xl text-indigo-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-pink-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Catégories</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.totalCategories }}</p>
          </div>
          <div class="bg-pink-100 p-3 rounded-full">
            <i class="pi pi-tags text-2xl text-pink-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-teal-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Éditeurs</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.totalPublishers }}</p>
          </div>
          <div class="bg-teal-100 p-3 rounded-full">
            <i class="pi pi-building text-2xl text-teal-600"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-orange-500">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-sm text-gray-500 font-medium">Auteurs</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ statistics.totalAuthors }}</p>
          </div>
          <div class="bg-orange-100 p-3 rounded-full">
            <i class="pi pi-user-edit text-2xl text-orange-600"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Second Section: More stats -->
    <div v-else-if="statistics" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
      <!-- Références par catégorie -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Références par catégorie</h3>
        <div class="space-y-3">
          <div v-for="item in statistics.referencesByCategory" :key="item.name" class="flex items-center gap-4">
            <div class="flex-1">
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">{{ item.name }}</span>
                <span class="text-sm font-semibold text-gray-800">{{ item.count }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="bg-amber-600 h-2.5 rounded-full transition-all duration-300"
                  :style="{ width: `${(item.count / statistics.totalReferences) * 100}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Demandes par statut -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Demandes par statut</h3>
        <div class="space-y-3">
          <div v-for="(count, status) in statistics.requestsByStatus" :key="status" class="flex items-center gap-4">
            <div class="flex-1">
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">
                  {{
                    status === 'pending' ? 'En attente' :
                    status === 'assigned' ? 'Affectée' :
                    status === 'reassigned' ? 'Réaffectée' :
                    status === 'approved_by_manager' ? 'Approuvée par responsable' :
                    status === 'rejected_by_manager' ? 'Rejetée par responsable' :
                    status === 'published' ? 'Publiée' :
                    status === 'rejected' ? 'Rejetée' :
                    status
                  }}
                </span>
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
                  :style="{ width: `${(count / statistics.totalDepositeRequests) * 100}%` }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div v-else-if="statistics" class="bg-white rounded-xl shadow-md p-6 mt-6">
      <h3 class="text-xl font-bold text-gray-800 mb-4">Actions rapides</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link
          to="/admin/references"
          class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
        >
          <i class="pi pi-book text-xl text-amber-700"></i>
          <span class="font-medium text-gray-800">Gérer les références</span>
        </router-link>
        <router-link
          to="/admin/demandes"
          class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
        >
          <i class="pi pi-file-edit text-xl text-amber-700"></i>
          <span class="font-medium text-gray-800">Traiter les demandes</span>
        </router-link>
        <router-link
          to="/admin/utilisateurs"
          class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
        >
          <i class="pi pi-users text-xl text-amber-700"></i>
          <span class="font-medium text-gray-800">Gérer les utilisateurs</span>
        </router-link>
        <router-link
          to="/admin/categories"
          class="flex items-center gap-3 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition"
        >
          <i class="pi pi-tags text-xl text-amber-700"></i>
          <span class="font-medium text-gray-800">Gérer les catégories</span>
        </router-link>
      </div>
    </div>
  </div>
</template>
