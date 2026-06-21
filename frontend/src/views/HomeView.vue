<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import 'primeicons/primeicons.css'

const router = useRouter()
const authStore = useAuthStore()
const searchQuery = ref('')
const isMenuOpen = ref(false)

// Données de démonstration pour les catégories
const categories = ref([
  { id: 1, name: 'Informatique', icon: 'pi pi-code', color: 'from-blue-500 to-blue-700' },
  { id: 2, name: 'Littérature', icon: 'pi pi-book', color: 'from-purple-500 to-purple-700' },
  { id: 3, name: 'Sciences', icon: 'pi pi-microscope', color: 'from-green-500 to-green-700' },
  { id: 4, name: 'Droit', icon: 'pi pi-balance', color: 'from-amber-500 to-amber-700' },
  { id: 5, name: 'Médecine', icon: 'pi pi-heartbeat', color: 'from-red-500 to-red-700' },
  { id: 6, name: 'Arts', icon: 'pi pi-palette', color: 'from-pink-500 to-pink-700' },
])

// Données de démonstration pour les références
const recentReferences = ref([
  { id: 1, title: 'Les Misérables', author: 'Victor Hugo', category: 'Littérature', cover: 'https://picsum.photos/seed/book1/200/300', year: 1862 },
  { id: 2, title: 'La Relativité', author: 'Albert Einstein', category: 'Sciences', cover: 'https://picsum.photos/seed/book2/200/300', year: 1916 },
  { id: 3, title: 'Le Deuxième Sexe', author: 'Simone de Beauvoir', category: 'Littérature', cover: 'https://picsum.photos/seed/book3/200/300', year: 1949 },
  { id: 4, title: 'L\'Être et le Néant', author: 'Jean-Paul Sartre', category: 'Littérature', cover: 'https://picsum.photos/seed/book4/200/300', year: 1943 },
  { id: 5, title: 'Sur l\'Origine des Espèces', author: 'Charles Darwin', category: 'Sciences', cover: 'https://picsum.photos/seed/book5/200/300', year: 1859 },
  { id: 6, title: 'Le Rouge et le Noir', author: 'Stendhal', category: 'Littérature', cover: 'https://picsum.photos/seed/book6/200/300', year: 1830 },
])

onMounted(async () => {
  if (!authStore.user) {
    await authStore.fetchUser()
  }
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const goToDashboard = () => {
  const role = authStore.user?.role
  switch(role) {
    case 'admin':
      router.push('/admin/dashboard')
      break
    case 'responsable_d':
      router.push('/responsable_d/dashboard')
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
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <i class="pi pi-book text-3xl text-blue-600 mr-2"></i>
            <router-link to="/" class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              HighFive Bibliothèque
            </router-link>
          </div>

          <!-- Desktop Menu -->
          <div class="hidden md:flex items-center space-x-6">
            <router-link to="/" class="text-gray-700 hover:text-blue-600 font-medium transition">Accueil</router-link>
            <a href="#categories" class="text-gray-700 hover:text-blue-600 font-medium transition">Catégories</a>
            <a href="#ouvrages" class="text-gray-700 hover:text-blue-600 font-medium transition">Ouvrages</a>
            <a href="#apropos" class="text-gray-700 hover:text-blue-600 font-medium transition">À propos</a>
          </div>

          <!-- Right Side -->
          <div class="flex items-center space-x-4">
            <div v-if="authStore.isAuthenticated" class="flex items-center space-x-3">
              <div class="flex items-center space-x-2 bg-gray-100 px-3 py-1.5 rounded-full">
                <i class="pi pi-user text-blue-600"></i>
                <span class="text-sm font-medium text-gray-700">
                  {{ authStore.user.first_name }} {{ authStore.user.last_name }}
                </span>
              </div>
              <button 
                @click="goToDashboard"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition"
              >
                Tableau de bord
              </button>
              <button 
                @click="handleLogout"
                class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg font-medium transition"
                title="Déconnexion"
              >
                <i class="pi pi-sign-out"></i>
              </button>
            </div>
            <div v-else class="flex items-center space-x-3">
              <router-link 
                to="/login"
                class="text-gray-700 hover:text-blue-600 font-medium transition"
              >
                Connexion
              </router-link>
              <router-link 
                to="/register"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition"
              >
                Inscription
              </router-link>
            </div>

            <!-- Mobile Menu Button -->
            <button 
              @click="isMenuOpen = !isMenuOpen"
              class="md:hidden text-gray-700 p-2"
            >
              <i :class="isMenuOpen ? 'pi pi-times text-2xl' : 'pi pi-bars text-2xl'"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="isMenuOpen" class="md:hidden bg-white border-t">
        <div class="px-4 py-4 space-y-3">
          <router-link to="/" class="block text-gray-700 hover:text-blue-600 font-medium" @click="isMenuOpen = false">Accueil</router-link>
          <a href="#categories" class="block text-gray-700 hover:text-blue-600 font-medium" @click="isMenuOpen = false">Catégories</a>
          <a href="#ouvrages" class="block text-gray-700 hover:text-blue-600 font-medium" @click="isMenuOpen = false">Ouvrages</a>
          <a href="#apropos" class="block text-gray-700 hover:text-blue-600 font-medium" @click="isMenuOpen = false">À propos</a>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-700 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
          <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
            Explorez le monde de la <span class="text-yellow-300">connaissance</span>
          </h1>
          <p class="text-xl text-blue-100 mb-8">
            Accédez à des milliers de références documentaires, livres, articles et plus encore.
            Recherchez, découvrez et partagez votre passion pour la lecture.
          </p>
          
          <!-- Search Bar -->
          <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl p-2 flex items-center">
              <i class="pi pi-search text-gray-400 ml-4 text-xl"></i>
              <input 
                v-model="searchQuery"
                type="text"
                placeholder="Recherchez un livre, un auteur, une catégorie..."
                class="flex-1 px-4 py-3 text-gray-700 rounded-xl focus:outline-none"
              >
              <button class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-3 rounded-xl font-semibold transition transform hover:scale-105">
                Rechercher
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Parcourez par catégorie</h2>
          <p class="text-gray-600 text-lg">Trouvez ce que vous cherchez grâce à nos catégories thématiques</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
          <div 
            v-for="category in categories" 
            :key="category.id"
            class="group cursor-pointer"
          >
            <div class="bg-gradient-to-br" :class="category.color + ' rounded-2xl p-6 text-center text-white shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2'">
              <i :class="category.icon" class="text-5xl mb-3 block"></i>
              <h3 class="font-semibold text-lg">{{ category.name }}</h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent References Section -->
    <section id="ouvrages" class="py-16 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
          <div>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Ouvrages récents</h2>
            <p class="text-gray-600">Découvrez les dernières références ajoutées à notre bibliothèque</p>
          </div>
          <button class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-2">
            Voir tout <i class="pi pi-arrow-right"></i>
          </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
          <div 
            v-for="book in recentReferences" 
            :key="book.id"
            class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer"
          >
            <div class="relative">
              <img :src="book.cover" :alt="book.title" class="w-full h-64 object-cover">
              <div class="absolute top-2 right-2 bg-white/90 px-2 py-1 rounded text-xs font-semibold text-gray-700">
                {{ book.year }}
              </div>
            </div>
            <div class="p-4">
              <span class="text-xs text-blue-600 font-semibold uppercase tracking-wide">{{ book.category }}</span>
              <h3 class="font-bold text-gray-800 mt-1 line-clamp-2">{{ book.title }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ book.author }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="apropos" class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Pourquoi nous choisir ?</h2>
          <p class="text-gray-600 text-lg">Une bibliothèque numérique moderne et accessible à tous</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="text-center p-6">
            <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-book-open text-3xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Vaste collection</h3>
            <p class="text-gray-600">Des milliers de références dans tous les domaines</p>
          </div>

          <div class="text-center p-6">
            <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-cloud-download text-3xl text-purple-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Téléchargement</h3>
            <p class="text-gray-600">Téléchargez et lisez vos ouvrages préférés</p>
          </div>

          <div class="text-center p-6">
            <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-users text-3xl text-green-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Collaboratif</h3>
            <p class="text-gray-600">Contribuez en proposant de nouvelles références</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <div class="flex items-center mb-4">
              <i class="pi pi-book text-3xl text-blue-400 mr-2"></i>
              <span class="text-xl font-bold">HighFive Bibliothèque</span>
            </div>
            <p class="text-gray-400">Votre bibliothèque numérique de référence pour accéder à la connaissance.</p>
          </div>

          <div>
            <h4 class="font-semibold text-lg mb-4">Liens rapides</h4>
            <ul class="space-y-2 text-gray-400">
              <li><router-link to="/" class="hover:text-white transition">Accueil</router-link></li>
              <li><a href="#categories" class="hover:text-white transition">Catégories</a></li>
              <li><a href="#ouvrages" class="hover:text-white transition">Ouvrages</a></li>
            </ul>
          </div>

          <div>
            <h4 class="font-semibold text-lg mb-4">Légal</h4>
            <ul class="space-y-2 text-gray-400">
              <li><a href="#" class="hover:text-white transition">Mentions légales</a></li>
              <li><a href="#" class="hover:text-white transition">Politique de confidentialité</a></li>
              <li><a href="#" class="hover:text-white transition">CGU</a></li>
            </ul>
          </div>

          <div>
            <h4 class="font-semibold text-lg mb-4">Contact</h4>
            <ul class="space-y-2 text-gray-400">
              <li class="flex items-center gap-2"><i class="pi pi-envelope"></i> contact@bibliotheque.fr</li>
              <li class="flex items-center gap-2"><i class="pi pi-phone"></i> +33 1 23 45 67 89</li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-800 mt-10 pt-6 text-center text-gray-500">
          <p>&copy; 2026 HighFive Bibliothèque. Tous droits réservés.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

