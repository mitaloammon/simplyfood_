<script setup lang="ts">
import { useAuthStore } from '../../shared/stores/auth'
import { useRouter } from 'vue-router'
const auth = useAuthStore()
const router = useRouter()
function out() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <main class="p-8">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-semibold">Dashboard</h1>
      <button class="text-sm underline" @click="out">Sair</button>
    </div>
    <p class="mt-4 text-slate-600">Esqueleto operacional. Proximo: mesas, pedidos e caixa.</p>
  </main>
</template>
