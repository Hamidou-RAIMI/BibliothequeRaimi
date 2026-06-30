<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const isProfileOpen = ref(false)
const isMobileMenuOpen = ref(false)
</script>

<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        
        <!-- Gauche : Logo et Navigation principale -->
        <div class="flex items-center gap-8">
          <div class="flex items-center gap-2 font-bold text-xl text-indigo-600">
            <span>📚</span>
            <span>Bibliothèque</span>
          </div>
          
          <!-- Liens Desktop -->
          <nav class="hidden md:flex items-center gap-1">
            <RouterLink 
              to="/" 
              class="px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
              active-class="bg-indigo-50 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-600"
            >
              Accueil
            </RouterLink>
            <RouterLink 
              to="/dashboard" 
              class="px-3 py-2 rounded-md text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors"
              active-class="bg-indigo-50 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-600"
            >
              Dashboard
            </RouterLink>
          </nav>
        </div>

        <!-- Droite : Profil & Menu Mobile -->
        <div class="flex items-center gap-4">
          <!-- Menu Profil (Desktop) -->
          <div class="relative hidden md:block">
            <button 
              @click="isProfileOpen = !isProfileOpen"
              class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition-colors"
            >
              <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold text-sm">
                R
              </div>
            </button>

            <!-- Dropdown Profil -->
            <div 
              v-if="isProfileOpen" 
              class="absolute right-0 mt-2 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 border border-slate-100"
            >
              <div class="px-4 py-2 border-b border-slate-100">
                <p class="text-xs text-slate-500">Connecté en tant que</p>
                <p class="text-sm font-medium text-slate-800 truncate">raouf.raimi</p>
              </div>
              <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Mon profil</a>
              <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-slate-50">Déconnexion</a>
            </div>
          </div>

          <!-- Bouton Menu Mobile -->
          <button 
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="p-2 text-slate-600 hover:bg-slate-100 rounded-md md:hidden"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

      </div>
    </div>

    <!-- Menu Mobile Déroulant -->
    <div v-if="isMobileMenuOpen" class="md:hidden border-t border-slate-200 bg-white px-4 py-3 space-y-1">
      <RouterLink 
        to="/" 
        @click="isMobileMenuOpen = false"
        class="block px-3 py-2 rounded-md text-base font-medium text-slate-600 hover:bg-slate-50"
        active-class="bg-indigo-50 text-indigo-600"
      >
        Accueil
      </RouterLink>
      <RouterLink 
        to="/dashboard" 
        @click="isMobileMenuOpen = false"
        class="block px-3 py-2 rounded-md text-base font-medium text-slate-600 hover:bg-slate-50"
        active-class="bg-indigo-50 text-indigo-600"
      >
        Dashboard
      </RouterLink>
      <div class="pt-4 border-t border-slate-100">
        <div class="px-3 py-2 text-sm font-medium text-slate-800">raouf.raimi</div>
        <a href="#" class="block px-3 py-2 text-base font-medium text-red-600 hover:bg-slate-50">Déconnexion</a>
      </div>
    </div>
  </header>
</template>


