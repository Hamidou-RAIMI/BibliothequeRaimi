import './main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

// Importations PrimeVue
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'primeicons/primeicons.css';


const app = createApp(App)

// Pinia AVANT router pourr que les storess soient disponibles
const pinia = createPinia()
app.use(pinia)
app.use(router)
// Activation de PrimeVue avec le thème Aura
app.use(PrimeVue, {
    theme: {
        preset: Aura
    }
});
    

app.mount('#app')
