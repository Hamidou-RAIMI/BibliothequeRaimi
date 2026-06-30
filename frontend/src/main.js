import './main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/auth'

// Importations PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'primeicons/primeicons.css';
import ToastService from 'primevue/toastservice';


const app = createApp(App)

// Pinia AVANT router pourr que les storess soient disponibles
const pinia = createPinia()
app.use(pinia)

// Initialiser l'utilisateur avant de monter l'app
const authStore = useAuthStore()
await authStore.fetchUser()

app.use(router)
// Activation de PrimeVue avec le thème Aura
app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
});
app.use(ToastService);
    

app.mount('#app')
