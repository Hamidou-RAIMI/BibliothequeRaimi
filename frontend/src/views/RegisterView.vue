<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const firstName = ref('')
const lastName = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const errorMsg = ref('')

const handleRegister = async () => {
    errorMsg.value = ''
    try {
        await authStore.register(
            firstName.value,
            lastName.value,
            email.value,
            phone.value,
            password.value,
            passwordConfirmation.value
        )
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Inscription réussie', life: 3000 })
        router.push('/login')
    } catch (err) {
        errorMsg.value = err.response?.data?.errors || err.response?.data?.message || 'Erreur d\'inscription'
        toast.add({ severity: 'error', summary: 'Erreur', detail: Array.isArray(errorMsg.value) ? errorMsg.value[0] : errorMsg.value, life: 3000 })
        throw err
    }
}
</script>

<template>
  <div class="min-h-screen bg-[#f4f4f4] lg:bg-[#dedede] flex items-center justify-center lg:p-6 font-sans">
    <div class="w-full h-screen lg:h-auto lg:max-w-6xl lg:aspect-[4/3] bg-[#1c1a19] lg:rounded-[40px] shadow-2xl flex flex-col lg:flex-row overflow-hidden">
      
      <!-- Colonne Gauche : Identité visuelle (Masquée sur mobile) -->
      <div class="hidden lg:flex flex-col justify-between w-1/2 p-12 text-white relative overflow-hidden select-none">
        <div class="my-auto flex flex-col items-center text-center z-10">
          <!-- Zone libre pour illustration ou slogan -->
        </div>

        <div class="text-[10px] text-gray-500">
          © {{ new Date().getFullYear() }} highfive bibliothèque Inc. All rights reserved.
        </div>
      </div>

      <!-- Colonne Droite : Formulaire -->
      <div class="w-full lg:w-1/2 bg-white h-full flex flex-col justify-between p-8 sm:p-12 lg:p-16 lg:rounded-l-[40px] overflow-y-auto">
        
        <!-- En-tête du formulaire -->
        <div class="flex justify-between items-center w-full mb-8 lg:mb-0">
          <div class="flex items-center space-x-2 font-bold text-xl text-[#1c1a19]">
            <span class="w-5 h-5 rounded-full bg-amber-700 inline-block"></span>
            <span>Bibliothèque</span>
          </div>
          
          <router-link to="/login" class="text-sm font-medium text-gray-700 hover:text-orange-600 flex items-center space-x-1 transition">
            <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V7.5A2.25 2.25 0 0 1 10.5 5.25h3a2.25 2.25 0 0 1 2.25 2.25V9M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V14.25A2.25 2.25 0 0 0 19.5 12h-15a2.25 2.25 0 0 0-2.25 2.25v3a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <span>Se connecter</span>
          </router-link>
        </div>

        <!-- Corps du formulaire -->
        <div class="w-full max-w-md mx-auto my-auto py-4">
          <h1 class="text-3xl sm:text-4xl font-semibold text-gray-900 mb-6 tracking-tight">
            Créer un compte
          </h1>

          <form @submit.prevent="handleRegister" class="space-y-4">
            <!-- Grille Prénom / Nom -->
            <div class="grid gap-4 grid-cols-2">
                <div>
                  <input 
                    v-model="firstName" 
                    type="text" 
                    required
                    class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                    placeholder="Prénom" 
                  />
                </div>

                <div>
                  <input 
                    v-model="lastName" 
                    type="text" 
                    required
                    class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                    placeholder="Nom" 
                  />
                </div>
            </div>

            <!-- Champ Email -->
            <div>
              <input 
                v-model="email" 
                type="email" 
                required
                class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                placeholder="Adresse email" 
              />
            </div>

            <!-- Champ Téléphone -->
            <div>
              <input 
                v-model="phone" 
                type="tel" 
                required
                class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                placeholder="Téléphone" 
              />
            </div>

            <!-- Champ Mot de passe -->
            <div>
              <input 
                v-model="password" 
                type="password" 
                required 
                minlength="6"
                class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                placeholder="Mot de passe" 
              />
            </div>

            <!-- Champ Confirmer le mot de passe -->
            <div>
              <input 
                v-model="passwordConfirmation" 
                type="password" 
                required 
                minlength="6"
                class="w-full px-5 py-3.5 bg-white border border-gray-300 rounded-full text-gray-900 placeholder-gray-400 outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition text-sm sm:text-base"
                placeholder="Confirmer le mot de passe" 
              />
            </div>

            <!-- Bouton Soumettre -->
            <button 
              :disabled="authStore.loading" 
              type="submit"
              class="w-full h-12 bg-amber-700 hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-full transition flex items-center justify-center space-x-2 mt-6 shadow-lg shadow-orange-600/20"
            >
              <span>{{ authStore.loading ? 'Inscription...' : "S’inscrire" }}</span>
              <svg v-if="!authStore.loading" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
              </svg>
            </button>
          </form>

          <!-- Zone des Messages d'Erreur (Gère chaînes de caractères et Objets de validation API) -->
          <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-xs sm:text-sm text-center">
              <template v-if="typeof errorMsg === 'object'">
                  <p v-for="(msgs, field) in errorMsg" :key="field">
                       {{ msgs[0] }}
                  </p>
              </template>
              <p v-else>{{ errorMsg }}</p>
          </div>
        </div>

        <div class="flex justify-between items-center text-xs text-gray-500 pt-8 border-t border-gray-100 lg:border-none w-full max-w-md mx-auto">
          <!-- Zone basse vide pour conserver la symétrie -->
        </div>

      </div>

    </div>
    <Toast position="top-right" />
  </div>
</template>