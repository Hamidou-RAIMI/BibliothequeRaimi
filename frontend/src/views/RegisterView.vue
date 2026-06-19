<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

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
        router.push('/login')
    } catch (err) {
        errorMsg.value = err.response?.data?.errors || err.response?.data?.message || 'Erreur d\'inscription'
        throw err
    }
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 text-center">Inscription</h1>
            <p class="text-gray-600 text-center mb-8">Créez votre compte</p>

            <form @submit.prevent="handleRegister" class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                        <input v-model="firstName" type="text" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            placeholder="Prénom" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                        <input v-model="lastName" type="text" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                            placeholder="Nom" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input v-model="email" type="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                        placeholder="test@example.com" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                    <input v-model="phone" type="tel" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                        placeholder="0612345678" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                    <input v-model="password" type="password" required minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                        placeholder="password123" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <input v-model="passwordConfirmation" type="password" required minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                        placeholder="password123" />
                </div>

                <button :disabled="authStore.loading" type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2 px-4 rounded-lg transition">
                    {{ authStore.loading ? 'Inscription...' : "S’inscrire" }}
                </button>
            </form>

            
            <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                
                <template v-if="typeof errorMsg === 'object'">
                    <p v-for="(msgs, field) in errorMsg" :key="field">
                         {{ msgs[0] }}
                    </p>
                </template>
            
                <p v-else>{{ errorMsg }}</p>
            </div>

            <p class="mt-6 text-center text-gray-600">
                Déjà inscrit ?
                <router-link to="/login" class="text-blue-600 hover:underline font-semibold">
                    Connectez-vous
                </router-link>
            </p>
        </div>
    </div>
</template>
