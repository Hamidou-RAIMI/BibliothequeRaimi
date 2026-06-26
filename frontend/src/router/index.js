import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    // ADMIN---------------------------
    {
      path: '/admin',
      component: () => import('../layouts/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'admin' },
      children: [
        {
          path: 'dashboard',
          name: 'admin-dashboard',
          component: () => import('../views/admin/DashboardView.vue'),
        },
        {
          path: 'utilisateurs',
          name: 'admin-utilisateurs',
          component: () => import('../views/admin/UtilisateursView.vue'),
        },
        {
          path: 'utilisateurs/archives',
          name: 'admin-utilisateurs-archives',
          component: () => import('../views/admin/UtilisateursArchivesView.vue'),
        },
        {
          path: 'references',
          name: 'admin-references',
          component: () => import('../views/admin/ReferencesView.vue'),
        },
        {
          path: 'references/archives',
          name: 'admin-references-archives',
          component: () => import('../views/admin/ReferencesArchivesView.vue'),
        },
        {
          path: 'auteurs',
          name: 'admin-authors',
          component: () => import('../views/admin/AuthorsView.vue'),
        },
        {
          path: 'categories',
          name: 'admin-categories',
          component: () => import('../views/admin/CategoriesView.vue'),
        },
        {
          path: 'editeurs',
          name: 'admin-publishers',
        component: () => import('../views/admin/PublishersView.vue'),
        },
        {
          path: 'demandes',
          name: 'admin-deposite-requests',
        component: () => import('../views/admin/DepositeRequestsView.vue'),
        }
      ] 
    },
    // RESPONSABLE_D
    {
      path: '/responsable_demande',
      component: () => import('../layouts/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'responsable_demande' },
      children: [
        {
          path: 'dashboard',
          name: 'responsable_demande-dashboard',
          component: () => import('../views/responsable_Demande/DashboardView.vue'),
        },
        {
          path: 'demandes',
          name: 'responsable_demande-deposite-requests',
          component: () => import('../views/responsable_demande/DepositeRequestsView.vue'),
        }
      ] 
    },
    // RESPONSABLE_RH
    {
      path: '/responsable_rh',
      component: () => import('../layouts/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh' },
      children: [
        {
          path: 'dashboard',
          name: 'responsable_rh-dashboard',
          component: () => import('../views/responsable_Rh/DashboardView.vue'),
        },
        {
          path: 'utilisateurs',
          name: 'responsable_rh-utilisateurs',
          component: () => import('../views/admin/UtilisateursView.vue'),
        },
      ] 
    },
    // USER
    {
      path: '/user',
      component: () => import('../layouts/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'user' },
      children: [
        {
          path: 'dashboard',
          name: 'user-dashboard',
          component: () => import('../views/user/DashboardView.vue'),
        },
        {
          path: 'demandes',
          name: 'user-deposite-requests',
          component: () => import('../views/user/DepositeRequestsView.vue'),
        }
      ] 
    },
  ]
})

// Fonction utilitaire pour centraliser les redirections basées sur le rôle
const getDashboardRoute = (role) => {
  switch (role) {
    case 'admin':
      return '/admin/dashboard'
    case 'responsable_demande':
      return '/responsable_demande/dashboard'
    case 'responsable_rh':
      return '/responsable_rh/dashboard'
    case 'user':
      return '/user/dashboard'
    default:
      return '/'
  }
}

// Navigation guard moderne sans callback next()
router.beforeEach(async (to, from) => {
  const authStore = useAuthStore()
  
  // Récupère l'utilisateur si non authentifié en local mais session active côté API (ex: rafraîchissement)
  if (!authStore.user && !authStore.isAuthenticated) {
    try {
      await authStore.fetchUser()
    } catch (e) {
      // L'utilisateur n'est pas authentifié, échec silencieux attendu
    }
  }

  // 1. Vérification des routes nécessitant une authentification
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      return '/login'
    }
    
    // Vérification stricte des autorisations de rôle
    if (to.meta.role && authStore.user?.role !== to.meta.role) {
      return getDashboardRoute(authStore.user?.role)
    }
    
    // Si authentifié et rôle correct, la navigation continue implicitement
    return true
  }

  // 2. Vérification des routes publiques (Login / Register) : Redirection automatique si déjà connecté
  if (authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    return getDashboardRoute(authStore.user?.role)
  }

  // Autorise l'accès aux autres routes publiques
  return true
})

export default router
