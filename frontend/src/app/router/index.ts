import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/shared/stores/auth';
import AuthLayout from '../layouts/AuthLayout.vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('@/modules/auth/pages/LoginPage.vue'),
      }
    ]
  },
  {
    path: '/',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: { name: 'dashboard' }
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/modules/dashboard/pages/DashboardPage.vue'),
      },
      {
        path: 'customers',
        name: 'customers',
        component: () => import('@/modules/customers/pages/CustomerPage.vue'),
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/modules/orders/pages/OrderPage.vue'),
      },
      {
        path: 'cash-register',
        name: 'cash-register',
        component: () => import('@/modules/cash-register/pages/CashRegisterPage.vue'),
      },
      {
        path: 'tables',
        name: 'tables',
        component: () => import('@/modules/tables/pages/TablesPage.vue'),
      },
      {
        path: 'commands',
        name: 'commands',
        component: () => import('@/modules/commands/pages/CommandsPage.vue'),
      },
      {
        path: 'recipes',
        name: 'recipes',
        component: () => import('@/modules/recipes/pages/RecipesPage.vue'),
      },
      {
        path: 'products/:id/edit',
        name: 'product-advanced-edit',
        component: () => import('@/modules/products/pages/ProductAdvancedEditPage.vue'),
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' });
  } else if (to.name === 'login' && authStore.isAuthenticated) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
});
