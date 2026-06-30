<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ref, onMounted, onUnmounted } from 'vue'
import { Search, Bell, Menu, ChevronDown, LogOut, User } from 'lucide-vue-next'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()


const dropdownOpen = ref(false)
const dropdownRef = ref(null)

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const handleLoginClick = () => {
  router.push('/login')
}

const handleLogout = async () => {
  dropdownOpen.value = false
  await authStore.logout()
  toast.add({ severity: 'success', summary: 'Succès', detail: 'Déconnexion réussie', life: 3000 })
  router.push('/')
}
defineProps({
  isSidebarCollapsed: Boolean
})

defineEmits(['toggleSidebar']);

// const handleNavigateHome = () => {
//   if (authStore.isAuthenticated) {
//     const role = authStore.user.role
//     switch(role) {
//       case 'admin':
//         router.push('/admin/dashboard')
//         break
//       case 'responsable_demande':
//         router.push('/responsable_demande/dashboard')
//         break
//       case 'responsable_rh':
//         router.push('/responsable_rh/dashboard')
//         break
//       case 'user':
//         router.push('/user/dashboard')
//         break
//       default:
//         router.push('/')
//     }
//   } else {
//     router.push('/')
//   }
// }


</script>

<template>
  <header class="h-20 bg-white border-b border-slate-200 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-2 sm:gap-6 flex-1 max-w-2xl">
      <button 
        @click="$emit('toggleSidebar')"
        class="p-2 sm:p-2.5 rounded-xl hover:bg-slate-100 text-slate-500 transition-all active:scale-95"
      >
        <Menu class="w-6 h-6" />
      </button>
    </div>

    <div class="flex items-center gap-2 sm:gap-4">
      

      <div class="h-8 sm:h-10 w-[1px] bg-slate-200 mx-1"></div>

      <!-- Profile Dropdown -->
      <div class="relative" ref="dropdownRef">
        <button
          @click="toggleDropdown"
          class="flex items-center gap-2 sm:gap-3 pl-1 pr-1 sm:pr-2 py-1.5 rounded-2xl hover:bg-slate-50 transition-all"
        >
          
          <!-- Nom & Rôle -->
          <div class="text-left hidden md:block">
            <p class="text-sm font-bold text-slate-900 leading-tight">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
            <p class="text-[11px] font-medium text-slate-500 capitalize">{{ authStore.user?.role || 'Rôle' }}</p>
          </div>
          <!-- Chevron -->
          <ChevronDown 
            class="w-4 h-4 text-slate-400 transition-transform duration-200 flex-shrink-0"
            :class="{ 'rotate-180': dropdownOpen }"
          />
        </button>

        <!-- Dropdown Menu -->
        <Transition
          enter-active-class="transition ease-out duration-150"
          enter-from-class="opacity-0 translate-y-1 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100 translate-y-0 scale-100"
          leave-to-class="opacity-0 translate-y-1 scale-95"
        >
          <div
            v-if="dropdownOpen"
            class="absolute right-0 top-full mt-2 w-52 bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/80 overflow-hidden z-50"
          >
            <!-- User info recap -->
            <div class="px-4 py-3 border-b border-slate-100">
              <p class="text-sm font-bold text-slate-900">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
              <p class="text-xs text-slate-500 truncate">{{ authStore.user?.email || '' }}</p>
            </div>
            <!-- Actions -->
            <div class="py-1.5">
              <button
                @click="closeDropdown(); router.push(`/${authStore.user?.role}/profile`)"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left"
              >
                <User class="w-4 h-4 text-slate-400" />
                Mon profil
              </button>
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors text-left"
              >
                <LogOut class="w-4 h-4" />
                Se déconnecter
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>







    <!-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
      <div @click="handleNavigateHome" class="flex items-center gap-2 cursor-pointer">
        <i class="pi pi-book text-amber-700 text-2xl"></i>
        <span class="text-xl font-bold text-stone-800">Bibliothèque</span>
      </div>
      <nav v-if="!authStore.isAuthenticated" class="hidden md:flex gap-8">
        
      </nav>
      <div v-if="authStore.isAuthenticated" class="flex items-center gap-4">
        <span class="text-stone-700">
          {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}
        </span>
        <button @click="handleLogout" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
          Se déconnecter
        </button>
      </div>
      <button v-else @click="handleLoginClick" class="bg-amber-700 text-white px-4 py-2 rounded-lg hover:bg-amber-800 transition">
        Se connecter
      </button>
    </div> -->
  </header>
</template>

<style scoped></style>
