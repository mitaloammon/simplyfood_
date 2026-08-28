<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import type { Role } from '../types/api'

interface NavigationItem {
  to: string
  label: string
  roles: Role[]
}

const navigation: NavigationItem[] = [
  { to: '/dashboard', label: 'Dashboard', roles: ['ADMIN', 'MANAGER', 'CASHIER', 'WAITER'] },
  { to: '/customers', label: 'Clientes', roles: ['ADMIN', 'MANAGER', 'WAITER'] },
  { to: '/products', label: 'Produtos', roles: ['ADMIN', 'MANAGER'] },
  { to: '/tables', label: 'Mesas e comandas', roles: ['ADMIN', 'MANAGER', 'WAITER'] },
  { to: '/orders', label: 'Pedidos', roles: ['ADMIN', 'MANAGER', 'CASHIER', 'WAITER'] },
  { to: '/cash', label: 'Caixa', roles: ['ADMIN', 'MANAGER', 'CASHIER'] },
]

const auth = useAuthStore()
const router = useRouter()
const visibleNavigation = computed(() => navigation.filter((item) => auth.user && item.roles.includes(auth.user.role)))

async function logout() {
  await auth.logout()
  await router.replace('/login')
}
</script>

<template>
  <div class="min-h-screen bg-brand-floor text-brand-navy">
    <aside class="border-b border-white/10 bg-brand-navy text-white lg:fixed lg:inset-y-0 lg:left-0 lg:z-20 lg:w-64 lg:border-b-0 lg:border-r">
      <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-5 py-4 lg:px-6 lg:py-7">
          <RouterLink to="/dashboard" class="text-xl font-bold tracking-[-0.04em]">
            Simply<span class="text-blue-300">Food</span>
          </RouterLink>

          <div class="flex items-center gap-3 lg:hidden">
            <span class="max-w-28 truncate text-xs font-medium text-slate-300">{{ auth.user?.name }}</span>
            <button class="rounded-lg border border-white/20 px-3 py-2 text-xs font-semibold hover:bg-white/10" @click="logout">
              Sair
            </button>
          </div>
        </div>

        <nav class="flex gap-1 overflow-x-auto border-t border-white/10 px-3 py-2 lg:flex-1 lg:flex-col lg:overflow-visible lg:border-0 lg:px-4 lg:py-2">
          <RouterLink
            v-for="item in visibleNavigation"
            :key="item.to"
            :to="item.to"
            class="nav-link"
            active-class="nav-link-active"
          >
            {{ item.label }}
          </RouterLink>
        </nav>

        <div class="hidden border-t border-white/10 p-5 lg:block">
          <p class="truncate text-sm font-semibold text-white">{{ auth.user?.name }}</p>
          <p class="mt-1 text-xs font-medium text-slate-300">{{ auth.user?.role }}</p>
          <button class="mt-4 w-full rounded-lg border border-white/20 px-3 py-2.5 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white" @click="logout">
            Sair
          </button>
        </div>
      </div>
    </aside>

    <main class="min-h-screen lg:pl-64">
      <div class="mx-auto w-full max-w-[1600px] p-5 sm:p-7 lg:p-9 xl:p-10">
        <RouterView />
      </div>
    </main>
  </div>
</template>
