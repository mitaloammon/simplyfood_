<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiErrorMessage } from '../../shared/api/errors'
import { useAuthStore } from '../../shared/stores/auth'

const email = ref('admin@simplyfood.test')
const password = ref('password')
const error = ref('')
const notice = ref('')
const loading = ref(false)
const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

async function submit() {
  error.value = ''
  notice.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await router.replace(redirect)
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

function showComingSoon() {
  notice.value = 'Essa opção ainda não está disponível.'
}
</script>

<template>
  <main class="min-h-screen bg-brand-navy lg:grid lg:grid-cols-[minmax(0,1.35fr)_minmax(420px,1fr)]">
    <section class="relative min-h-[240px] overflow-hidden bg-brand-navy text-white sm:min-h-[300px] lg:min-h-screen">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_20%,_rgba(37,99,235,0.48),_transparent_34%),radial-gradient(circle_at_86%_78%,_rgba(220,90,75,0.2),_transparent_30%)]" />
      <div class="absolute -left-24 bottom-[-30%] h-[70%] w-[70%] rounded-full border border-blue-300/20 bg-blue-400/10 blur-sm" />
      <div class="absolute -right-20 top-[-20%] h-[55%] w-[55%] rounded-full border border-white/10 bg-white/5" />

      <div class="relative flex h-full min-h-[240px] flex-col justify-end p-7 sm:min-h-[300px] sm:p-10 lg:min-h-screen lg:p-14 xl:p-20">
        <div class="max-w-2xl border-l-4 border-brand-coral pl-5 sm:pl-7">
          <h2 class="max-w-xl text-3xl font-bold leading-tight tracking-[-0.045em] sm:text-4xl xl:text-6xl">
            Tudo pronto para o próximo pedido.
          </h2>
          <p class="mt-4 hidden max-w-lg text-base leading-relaxed text-slate-300 sm:block">
            Entre para cuidar das mesas, dos pedidos e do caixa sem perder o ritmo.
          </p>
        </div>
      </div>
    </section>

    <section class="flex min-h-[calc(100vh-240px)] flex-col bg-white px-6 py-8 sm:min-h-[calc(100vh-300px)] sm:px-12 lg:min-h-screen lg:px-14 lg:py-12 xl:px-20">
      <header class="mx-auto w-full max-w-md">
        <p class="text-xs font-bold uppercase tracking-[0.24em] text-brand-blue">SimplyFood</p>
      </header>

      <div class="mx-auto flex w-full max-w-md flex-1 items-center py-10">
        <div class="w-full">
          <h1 class="text-3xl font-bold tracking-[-0.04em] text-brand-navy sm:text-4xl">Entre no SimplyFood</h1>
          <p class="mt-3 text-sm leading-relaxed text-slate-500">Use seu acesso para continuar o atendimento.</p>

          <form class="mt-8 space-y-5" @submit.prevent="submit">
            <label class="block">
              <span class="label">E-mail</span>
              <input
                v-model="email"
                class="field py-3"
                type="email"
                autocomplete="username"
                placeholder="voce@restaurante.com"
                required
              />
            </label>

            <label class="block">
              <span class="label">Senha</span>
              <input
                v-model="password"
                class="field py-3"
                type="password"
                autocomplete="current-password"
                placeholder="Sua senha"
                required
              />
            </label>

            <p v-if="error" class="rounded-lg border border-red-100 bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>

            <button class="btn-primary w-full py-3.5 text-base" :disabled="loading">
              {{ loading ? 'Entrando…' : 'Entrar' }}
            </button>
          </form>

          <p class="mt-4 text-center text-xs text-[#44403c]">Seu acesso fica nesta aba e é encerrado ao sair.</p>
        </div>
      </div>

      <footer class="mx-auto w-full max-w-md border-t border-slate-200 pt-5">
        <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
          <a href="#" class="font-medium text-slate-500 hover:text-brand-blue" @click.prevent="showComingSoon">
            Esqueci a senha
          </a>
          <a href="#" class="font-medium text-slate-500 hover:text-brand-blue" @click.prevent="showComingSoon">
            Novo usuário
          </a>
        </div>
        <p v-if="notice" class="mt-3 rounded-lg bg-brand-mist p-2 text-center text-xs text-blue-700">{{ notice }}</p>
      </footer>
    </section>
  </main>
</template>
