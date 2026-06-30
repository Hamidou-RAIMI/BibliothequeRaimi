<script setup>
import { ref } from 'vue';
import Header from '@/components/layout/Header.vue';
import Sidebar from '@/components/layout/Sidebar.vue';
import Toast from 'primevue/toast';

const isSidebarCollapsed = ref(false);
const isMobileMenuOpen = ref(false);

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const closeMobileSidebar = () => {
  isMobileMenuOpen.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <Sidebar 
      :is-collapsed="isSidebarCollapsed"
      :is-open-mobile="isMobileMenuOpen"
      @close-mobile="closeMobileSidebar"
    />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <Header 
        :is-sidebar-collapsed="isSidebarCollapsed"
        @toggle-sidebar="toggleSidebar"
      />
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <RouterView />
      </main>
    </div>
    <Toast position="top-right" />
  </div>
</template>

<style scoped>
</style>