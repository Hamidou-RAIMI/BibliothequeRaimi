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
    // ADMIN
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
      ] 
    },
    // RESPONSABLE_D
    {
      path: '/responsable_d',
      component: () => import('../layouts/AdminLayout.vue'),
      meta: { requiresAuth: true, role: 'responsable_d' },
      children: [
        {
          path: 'dashboard',
          name: 'responsable_d-dashboard',
          component: () => import('../views/responsable_D/DashboardView.vue'),
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
        }
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
        }
      ] 
    },
  ]
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Fetch utilisateur si non authentifié et que l'utilisateur est connecté (par exemple, après un rafraîchissement de la page)
  if (!authStore.user && !authStore.isAuthenticated) {
    try {
      await authStore.fetchUser()
    } catch (e) {
      // Utilisateurr n'est pas authentifier
    }
  }

  // verifie si la route a besoin d'authentification et si l'utilisateur est authentifié
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      next('/login')
    } else if (to.meta.role && authStore.user?.role !== to.meta.role) {
      // Redirige l'utilisateur vers sa page de tableau de bord en fonction de son rôle
      const role = authStore.user.role
      switch(role) {
        case 'admin':
          next('/admin/dashboard')
          break
        case 'responsable_d':
          next('/responsable_d/dashboard')
          break
        case 'responsable_rh':
          next('/responsable_rh/dashboard')
          break
        case 'user':
          next('/user/dashboard')
          break
        default:
          next('/')
      }
    } else {
      next()
    }
  } else {
    // Public routes - si l'utilisateur est déjà connecté, redirige vers le tableau de bord approprié
    if (authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
      const role = authStore.user.role
      switch(role) {
        case 'admin':
          next('/admin/dashboard')
          break
        case 'responsable_d':
          next('/responsable_d/dashboard')
          break
        case 'responsable_rh':
          next('/responsable_rh/dashboard')
          break
        case 'user':
          next('/user/dashboard')
          break
        default:
          next('/')
      }
    } else {
      next()
    }
  }
})

export default router
