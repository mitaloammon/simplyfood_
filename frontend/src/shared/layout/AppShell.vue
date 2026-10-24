<script setup lang="ts">
import { computed, ref, watch } from 'vue'
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
const sidebarOpen = ref(false)
const visibleNavigation = computed(() => navigation.filter((item) => auth.user && item.roles.includes(auth.user.role)))

watch(() => router.currentRoute.value.path, () => { sidebarOpen.value = false })

async function logout() {
  await auth.logout()
  await router.replace('/login')
}
</script>

<template>
  <main v-if="auth.user && !auth.user.installation_ready" class="min-h-screen bg-brand-floor p-4 text-brand-navy sm:p-6">
    <RouterView />
  </main>
  <div v-else class="min-h-screen bg-brand-floor text-brand-navy" @keydown.esc="sidebarOpen = false">
    <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false" />
    <aside
      id="app-sidebar"
      class="invisible fixed inset-y-0 left-0 z-40 w-[min(18rem,85vw)] -translate-x-full bg-brand-navy text-white transition-transform duration-200 lg:visible lg:translate-x-0"
      :class="sidebarOpen ? 'visible translate-x-0' : ''"
    >
      <div class="flex h-full flex-col">
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-5 lg:px-6 lg:py-7">
          <RouterLink to="/dashboard" class="text-xl font-bold tracking-[-0.035em]" aria-label="SimplyCore, início">
            Simply<span class="text-blue-300">Core</span>
          </RouterLink>

          <div class="flex items-center gap-2 lg:hidden">
            <span class="max-w-28 truncate text-xs font-medium text-slate-300">{{ auth.user?.name }}</span>
            <button class="rounded-lg p-2 text-slate-300 hover:bg-white/10 hover:text-white" aria-label="Fechar navegação" @click="sidebarOpen = false">✕</button>
          </div>
        </div>

        <nav aria-label="Navegação principal" class="flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-5 lg:px-4">
          <RouterLink
            v-for="item in visibleNavigation"
            :key="item.to"
            :to="item.to"
            class="nav-link"
            active-class="nav-link-active"
            :aria-current="router.currentRoute.value.path === item.to ? 'page' : undefined"
          >
            {{ item.label }}
          </RouterLink>
        </nav>

        <div class="border-t border-white/10 p-5">
          <p class="truncate text-sm font-semibold text-white">{{ auth.user?.name }}</p>
          <button class="mt-4 w-full rounded-lg border border-white/20 px-3 py-2.5 text-sm font-semibold text-slate-200 transition hover:bg-white/10 hover:text-white" @click="logout">
            Sair
          </button>
        </div>
      </div>
    </aside>

    <main class="min-h-screen lg:pl-64">
      <div class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
        <button class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-brand-navy hover:bg-slate-50" aria-label="Abrir navegação" aria-controls="app-sidebar" :aria-expanded="sidebarOpen" @click="sidebarOpen = true">☰</button>
        <span class="text-base font-bold text-brand-navy">Simply<span class="text-brand-blue">Core</span></span>
        <span class="ml-auto max-w-36 truncate text-xs text-slate-500">{{ auth.user?.name }}</span>
      </div>
      <div class="mx-auto w-full max-w-[1600px] p-4 sm:p-6 lg:p-8 xl:p-9">
        <RouterView />
      </div>
    </main>
  </div>
</template>
