<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const errorMsg = ref('')

const handleLogin = async () => {
  errorMsg.value = ''
  try {
    await authStore.login(email.value, password.value)
    // Redirect to correct dashboard based on role
    const role = authStore.user.role
    switch(role) {
      case 'admin':
        router.push('/admin/dashboard')
        break
      case 'responsable_demande':
        router.push('/responsable_demande/dashboard')
        break
      case 'responsable_rh':
        router.push('/responsable_rh/dashboard')
        break
      case 'user':
        router.push('/user/dashboard')
        break
      default:
        router.push('/')
    }
  } catch (err) {
    errorMsg.value = authStore.error
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#f4f4f4] lg:bg-[#dedede] flex items-center justify-center lg:p-6 font-sans">
    <div class="w-full h-screen lg:h-auto lg:max-w-6xl lg:aspect-[4/3] bg-[#1c1a19] lg:rounded-[40px] shadow-2xl flex flex-col lg:flex-row overflow-hidden">
      
      <div class="hidden lg:flex flex-col justify-between w-1/2 p-12 text-white relative overflow-hidden select-none">
        

        <div class="my-auto flex flex-col items-center text-center z-10">
          
          
        </div>

        <div class="text-[10px] text-gray-500">
          © {{ new Date().getFullYear() }} highfive bibliothèque Inc. All rights reserved.
        </div>
      </div>

      <div class="w-full lg:w-1/2 bg-white h-full flex flex-col justify-between p-8 sm:p-12 lg:p-16 lg:rounded-l-[40px]">
        
        <div class="flex justify-between items-center w-full mb-12 lg:mb-0">
          <div class="flex items-center space-x-2 font-bold text-xl text-[#1c1a19]">
            <span class="w-5 h-5 rounded-full bg-amber-700 inline-block"></span>
            <span>Bibliothèque</span>
          </div>
          
          <router-link to="/register" class="text-sm font-medium text-gray-700 hover:text-orange-600 flex items-center space-x-1 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            <span>S'inscrire</span>
          </router-link>
        </div>

        <div class="w-full max-w-md mx-auto my-auto">
          <h1 class="text-3xl sm:text-4xl font-semibold text-gray-900 mb-8 tracking-tight">
            Se connecter
          </h1>

          <form @submit.prevent="handleLogin" class="space-y-5">
            <div>
              <input 
                v-model="email" 
                type="email" 
                required
                class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                placeholder="Email ou nom d'utilisateur"
              />
            </div>

            <div class="relative">
              <input 
                v-model="password" 
                type="password" 
                required
                class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                placeholder="Mot de passe"
              />
              <span class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 17.772 17.772m-10.34-10.34a4.5 4.5 0 0 0 6.364 6.364m-6.364-6.364 6.364 6.364" />
                </svg>
              </span>
            </div>

            <div class="text-left pl-2">
              <a href="#" class="text-xs sm:text-sm text-[#ff4e00] hover:underline font-medium">
                Mot de passe oublié ?
              </a>
            </div>

            <button 
              :disabled="authStore.loading"
              type="submit"
              class="w-full h-12 bg-amber-700 hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-full transition flex items-center justify-center space-x-2 mt-4 shadow-lg shadow-orange-600/20"
            >
              <span>{{ authStore.loading ? 'Connexion...' : 'Se connecter' }}</span>
              <svg v-if="!authStore.loading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </button>
          </form>

          <p v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-xs sm:text-sm text-center">
            {{ errorMsg }}
          </p>
        </div>

        <div class="flex justify-between items-center text-xs text-gray-500 pt-8 border-t border-gray-100 lg:border-none w-full max-w-md mx-auto">
          
        </div>

      </div>

    </div>
  </div>
</template>