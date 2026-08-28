<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { apiErrorMessage } from '../../shared/api/errors'
import { money } from '../../shared/formatters'
import type { DashboardMetrics } from '../../shared/types/api'
import { getDashboardMetrics } from './dashboard.service'

const metrics = ref<DashboardMetrics | null>(null)
const loading = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    metrics.value = await getDashboardMetrics()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-blue">Agora</p>
        <h1 class="page-title">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-500">Acompanhe o movimento do turno.</p>
      </div>
      <button class="btn-secondary" :disabled="loading" @click="load">Atualizar</button>
    </div>

    <p v-if="error" class="mb-5 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</p>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article class="operation-card">
        <p class="text-sm font-medium text-slate-500">Pedidos abertos</p>
        <p class="mt-5 text-4xl font-bold tracking-[-0.04em] text-brand-navy">{{ metrics?.open_orders ?? '—' }}</p>
      </article>
      <article class="operation-card">
        <p class="text-sm font-medium text-slate-500">Mesas ocupadas</p>
        <p class="mt-5 text-4xl font-bold tracking-[-0.04em] text-brand-navy">{{ metrics?.occupied_tables ?? '—' }}</p>
      </article>
      <article class="operation-card">
        <p class="text-sm font-medium text-slate-500">Receita de hoje</p>
        <p class="mt-5 text-3xl font-bold tracking-[-0.04em] text-brand-blue">
          {{ metrics ? money(metrics.today_revenue) : '—' }}
        </p>
      </article>
      <article class="operation-card">
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-medium text-slate-500">Turno de caixa</p>
          <span class="h-2.5 w-2.5 rounded-full" :class="metrics?.open_shift ? 'bg-brand-blue' : 'bg-slate-300'" />
        </div>
        <p class="mt-5 text-2xl font-bold tracking-[-0.03em]" :class="metrics?.open_shift ? 'text-brand-navy' : 'text-[#44403c]'">
          {{ metrics?.open_shift ? 'Aberto' : 'Fechado' }}
        </p>
      </article>
    </div>
  </section>
</template>
