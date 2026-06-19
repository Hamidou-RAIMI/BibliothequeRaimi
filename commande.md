# 🚀 Guide des Commandes de Création Laravel 12

Ce fichier regroupe toutes les commandes `php artisan` essentielles pour générer les composants de votre architecture backend (Modèles, Contrôleurs, Migrations, Sécurité).

---

## ⚡ 1. Les Commandes "Tout-en-un" (Recommandé)

Ces commandes permettent de générer plusieurs fichiers liés en une seule saisie pour gagner du temps.

### Modèle + Migration + Contrôleur API
*Idéal pour un backend lié à un front-end Vue.js / Sanctum.*
```powershell
php artisan make:model NomDuModele -m --api
```

### La totale (Modèle + Migration + Contrôleur + Seeder + Factory)
*Génère absolument tout l'écosystème autour d'une table.*
```powershell
php artisan make:model NomDuModele -a
```

---

## 🗄️ 2. Base de Données & Structure

### Migration (Création de table)
```powershell
php artisan make:migration create_nom_de_la_table_table
```

### Seeder (Peuplement de données)
```powershell
php artisan make:seeder NomDuSeeder
```

### Factory (Générateur de fausses données)
```powershell
php artisan make:factory NomDuModeleFactory
```

---

## 🧠 3. Logique, API & Sécurité

### Contrôleur API seul
*Génère un contrôleur sans les méthodes HTML `create` et `edit` (inutiles avec Vue.js).*
```powershell
php artisan make:controller API/NomDuControleur --api
```

### Middleware (Filtre de requêtes / Vérification des rôles)
```powershell
php artisan make:middleware NomDuMiddleware
```

### Form Request (Validation stricte des données entrantes)
```powershell
php artisan make:request StoreNomDuModeleRequest
```

---

## 🔔 4. Communications & Alertes

### Notification (Mail, Base de données, SMS)
```powershell
php artisan make:notification NomDeLaNotification
```

### Mail (Classe d'envoi d'email dédiée)
```powershell
php artisan make:mail NomDuMail
```

















<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Header from '@/components/layout/Header.vue'
import Footer from '@/components/layout/Footer.vue'

const router = useRouter()

const books = ref([
  {
    id: 1,
    title: 'How Innovation Works',
    author: 'Matt Ridley',
    rating: 4.9,
    reviews: 128,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20how%20innovation%20works%20yellow%20design&image_size=square_hd'
  },
  {
    id: 2,
    title: "L'avenir de l'intelligence",
    author: 'Pierre Lévy',
    rating: 4.7,
    reviews: 89,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20artificial%20intelligence%20blue%20design&image_size=square_hd'
  },
  {
    id: 3,
    title: "Histoire de l'éducation",
    author: 'Louise Després',
    rating: 4.8,
    reviews: 76,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20education%20history%20sunset%20landscape&image_size=square_hd'
  },
  {
    id: 4,
    title: 'Philosophie de l’histoire',
    author: 'Michel Serres',
    rating: 4.6,
    reviews: 54,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20philosophy%20portrait%20man%20smiling&image_size=square_hd'
  },
  {
    id: 5,
    title: 'Biologie et cognitive',
    author: 'Françoise Gilot',
    rating: 4.9,
    reviews: 112,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20biology%20brain%20anatomy%20art&image_size=square_hd'
  },
  {
    id: 6,
    title: 'Design thinking',
    author: 'Tim Brown',
    rating: 4.5,
    reviews: 203,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20design%20thinking%20red%20notebook&image_size=square_hd'
  },
  {
    id: 7,
    title: 'Architecture et futures',
    author: 'Yona Friedman',
    rating: 4.8,
    reviews: 67,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20architecture%20city%20night%20lights&image_size=square_hd'
  },
  {
    id: 8,
    title: 'La santé de demain',
    author: 'Didier Raoult',
    rating: 4.7,
    reviews: 145,
    image: 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=book%20cover%20healthcare%20doctor%20phone&image_size=square_hd'
  }
])

const handleClick = () => {
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-stone-100">
    <Header />

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-stone-800 to-stone-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <span class="text-amber-400 font-semibold">À LA UNE</span>
            <h1 class="text-5xl font-bold mt-4 mb-6">Les Misérables</h1>
            <p class="text-stone-300 text-lg mb-8">
              Découvrez ce chef-d'œuvre de Victor Hugo, une histoire emblématique de justice, de rédemption et d'amour.
            </p>
            <div class="flex gap-4">
              <button @click="handleClick" class="bg-amber-700 text-white px-6 py-3 rounded-lg hover:bg-amber-800 transition">
                Lire en ligne
              </button>
              <button @click="handleClick" class="border border-white text-white px-6 py-3 rounded-lg hover:bg-white hover:text-stone-900 transition">
                Télécharger
              </button>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">1862</div>
              <div class="text-stone-300 text-sm">Année de publication</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">48 300</div>
              <div class="text-stone-300 text-sm">Pages</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">124 000</div>
              <div class="text-stone-300 text-sm">Lectures</div>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center">
              <div class="text-3xl font-bold text-amber-400">98%</div>
              <div class="text-stone-300 text-sm">Satisfaction</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Catalog Section -->
    <section class="py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-10">
          <h2 class="text-3xl font-bold text-stone-800">Parcourir le catalogue</h2>
          <button @click="handleClick" class="text-amber-700 font-semibold hover:underline">Voir tout →</button>
        </div>
        
        <!-- Categories -->
        <div class="flex gap-3 mb-10 flex-wrap">
          <span class="bg-amber-700 text-white px-4 py-2 rounded-full text-sm">Toute la bibliothèque</span>
          <span class="bg-white text-stone-700 px-4 py-2 rounded-full text-sm border">Littérature</span>
          <span class="bg-white text-stone-700 px-4 py-2 rounded-full text-sm border">Sciences</span>
          <span class="bg-white text-stone-700 px-4 py-2 rounded-full text-sm border">Histoire</span>
          <span class="bg-white text-stone-700 px-4 py-2 rounded-full text-sm border">Art & Architecture</span>
          <span class="bg-white text-stone-700 px-4 py-2 rounded-full text-sm border">Philosophie</span>
        </div>

        <!-- Books Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <div v-for="book in books" :key="book.id" @click="handleClick" class="bg-white rounded-xl overflow-hidden shadow hover:shadow-lg transition cursor-pointer">
            <img :src="book.image" :alt="book.title" class="w-full h-64 object-cover" />
            <div class="p-4">
              <h3 class="font-semibold text-stone-800 mb-1">{{ book.title }}</h3>
              <p class="text-stone-500 text-sm mb-2">{{ book.author }}</p>
              <div class="flex items-center gap-1 text-amber-500 text-sm">
                <i class="pi pi-star-fill"></i>
                <span>{{ book.rating }}</span>
                <span class="text-stone-400">({{ book.reviews }})</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-teal-800 text-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Enrichissez la bibliothèque collective</h2>
        <p class="text-teal-200 mb-8 max-w-2xl mx-auto">
          Partagez vos livres avec la communauté. Ensemble, construisons une bibliothèque accessible à tous.
        </p>
        <div class="flex justify-center gap-4">
          <button @click="handleClick" class="bg-white text-teal-900 px-6 py-3 rounded-lg hover:bg-teal-50 transition font-semibold">
            Déposer une œuvre
          </button>
          <button @click="handleClick" class="border border-white text-white px-6 py-3 rounded-lg hover:bg-white hover:text-teal-900 transition">
            En savoir plus
          </button>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>
