<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../shared/stores/auth'

const email = ref('admin@simplyfood.test')
const password = ref('password')
const error = ref('')
const auth = useAuthStore()
const router = useRouter()

async function submit() {
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    await router.push('/dashboard')
  } catch {
    error.value = 'Falha no login'
  }
}
</script>

<template>
  <main class="min-h-screen grid place-items-center p-6">
    <form class="w-full max-w-sm bg-white p-6 rounded-xl shadow space-y-4" @submit.prevent="submit">
      <h1 class="text-xl font-semibold">SimplyFood</h1>
      <input v-model="email" class="w-full border rounded px-3 py-2" type="email" />
      <input v-model="password" class="w-full border rounded px-3 py-2" type="password" />
      <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
      <button class="w-full bg-slate-900 text-white rounded py-2">Entrar</button>
    </form>
  </main>
</template>
