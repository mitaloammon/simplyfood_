import { createRouter, createWebHistory } from 'vue-router'
import AppShell from '../shared/layout/AppShell.vue'
import { getAccessToken } from '../shared/api/token'
import { useAuthStore } from '../shared/stores/auth'
import type { Role } from '../shared/types/api'

const dashboardRoles: Role[] = ['ADMIN', 'MANAGER', 'CASHIER', 'WAITER']

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../modules/auth/LoginPage.vue'),
      meta: { public: true },
    },
    {
      path: '/',
      component: AppShell,
      children: [
        { path: '', redirect: '/dashboard' },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('../modules/dashboard/DashboardPage.vue'),
          meta: { roles: dashboardRoles },
        },
        {
          path: 'customers',
          name: 'customers',
          component: () => import('../modules/customers/CustomersPage.vue'),
          meta: { roles: ['ADMIN', 'MANAGER', 'WAITER'] satisfies Role[] },
        },
        {
          path: 'products',
          name: 'products',
          component: () => import('../modules/catalog/CatalogPage.vue'),
          meta: { roles: ['ADMIN', 'MANAGER'] satisfies Role[] },
        },
        {
          path: 'tables',
          name: 'tables',
          component: () => import('../modules/tables/TablesPage.vue'),
          meta: { roles: ['ADMIN', 'MANAGER', 'WAITER'] satisfies Role[] },
        },
        {
          path: 'orders',
          name: 'orders',
          component: () => import('../modules/orders/OrdersPage.vue'),
          meta: { roles: dashboardRoles },
        },
        {
          path: 'cash',
          name: 'cash',
          component: () => import('../modules/cash/CashPage.vue'),
          meta: { roles: ['ADMIN', 'MANAGER', 'CASHIER'] satisfies Role[] },
        },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  const authenticated = Boolean(getAccessToken() && auth.user)

  if (!to.meta.public && !authenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.public && authenticated) return { name: 'dashboard' }

  const roles = to.meta.roles as Role[] | undefined
  if (roles && auth.user && !roles.includes(auth.user.role)) return { name: 'dashboard' }
})
