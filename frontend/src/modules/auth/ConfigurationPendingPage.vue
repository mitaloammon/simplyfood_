<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../shared/stores/auth'

const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  await router.replace('/login')
}
</script>

<template>
  <main class="mx-auto flex min-h-[60vh] max-w-2xl items-center">
    <section class="operation-card w-full" aria-labelledby="setup-title">
      <p class="page-eyebrow">Configuração necessária</p>
      <h1 id="setup-title" class="page-title">Estabelecimento indisponível</h1>
      <p class="mt-4 text-sm leading-6 text-slate-600">
        Esta conta não está vinculada a um estabelecimento ativo. Nenhuma operação de negócio foi liberada.
        Peça ao responsável pela instalação para revisar a configuração.
      </p>
      <button class="btn-secondary mt-6" @click="logout">Sair</button>
    </section>
  </main>
</template>
