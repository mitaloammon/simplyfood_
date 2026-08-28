import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../shared/stores/auth'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('../modules/auth/LoginPage.vue'), meta: { public: true } },
    { path: '/dashboard', component: () => import('../modules/dashboard/DashboardPage.vue') },
    { path: '/', redirect: '/dashboard' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (!to.meta.public && !auth.token) return '/login'
  if (to.path === '/login' && auth.token) return '/dashboard'
})
