<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { apiErrorMessage } from '../../shared/api/errors'
import { dateTime, money } from '../../shared/formatters'
import type { CashShift } from '../../shared/types/api'
import {
  addCashMovement,
  closeCash,
  getCashHistory,
  getCurrentShift,
  openCash,
} from './cash.service'

const current = ref<CashShift | null>(null)
const history = ref<CashShift[]>([])
const error = ref('')
const loading = ref(false)
const registerId = ref('')
const openingBalance = ref(0)
const movementType = ref<'BLEED' | 'SUPPLEMENT'>('SUPPLEMENT')
const movementAmount = ref(0)
const movementDescription = ref('')
const closingBalance = ref(0)
const closingNotes = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    const [shift, page] = await Promise.all([getCurrentShift(), getCashHistory()])
    current.value = shift
    history.value = page.data
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

async function open() {
  try {
    await openCash(registerId.value, openingBalance.value)
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function movement() {
  try {
    await addCashMovement(movementType.value, movementAmount.value, movementDescription.value)
    movementAmount.value = 0
    movementDescription.value = ''
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function close() {
  if (!window.confirm('Confirma o fechamento do turno?')) return
  try {
    await closeCash(closingBalance.value, closingNotes.value)
    closingBalance.value = 0
    closingNotes.value = ''
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-blue">Turno</p>
        <h1 class="page-title">Caixa</h1>
        <p class="mt-2 text-sm text-slate-500">Controle a abertura, os movimentos e o fechamento.</p>
      </div>
      <button class="btn-secondary" :disabled="loading" @click="load">Atualizar</button>
    </div>
    <p v-if="error" class="mb-5 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</p>

    <div v-if="current" class="mb-7 grid gap-5 lg:grid-cols-[minmax(0,1fr)_380px]">
      <article class="operation-card border-0 bg-brand-navy text-white shadow-lift">
        <div class="flex items-center gap-2">
          <span class="h-2.5 w-2.5 rounded-full bg-blue-300" />
          <p class="text-sm font-medium text-slate-300">Caixa aberto</p>
        </div>
        <h2 class="mt-4 text-3xl font-bold tracking-[-0.04em]">{{ current.cash_register.name }}</h2>
        <p class="mt-1 text-sm text-slate-300">{{ current.cash_register.location || 'Sem localização' }}</p>
        <div class="mt-8 grid grid-cols-2 gap-5 border-t border-white/10 pt-5">
          <div><p class="text-xs font-medium text-slate-300">Saldo inicial</p><p class="mt-2 text-xl font-bold">{{ money(current.opening_balance) }}</p></div>
          <div><p class="text-xs font-medium text-slate-300">Aberto em</p><p class="mt-2 font-semibold">{{ dateTime(current.opened_at) }}</p></div>
        </div>
      </article>

      <form class="operation-card space-y-4" @submit.prevent="movement">
        <h2 class="text-lg font-semibold">Novo movimento</h2>
        <label><span class="label">Tipo</span><select v-model="movementType" class="field"><option value="SUPPLEMENT">Suprimento</option><option value="BLEED">Sangria</option></select></label>
        <label><span class="label">Valor</span><input v-model.number="movementAmount" class="field" type="number" min="0.01" step="0.01" required /></label>
        <label><span class="label">Descrição</span><input v-model="movementDescription" class="field" /></label>
        <button class="btn-primary w-full">Lançar movimento</button>
      </form>

      <form class="operation-card space-y-4 lg:col-span-2" @submit.prevent="close">
        <h2 class="text-lg font-semibold">Fechar caixa</h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <label><span class="label">Saldo final</span><input v-model.number="closingBalance" class="field" type="number" min="0" step="0.01" required /></label>
          <label><span class="label">Observações</span><input v-model="closingNotes" class="field" /></label>
        </div>
        <button class="btn-danger">Fechar caixa</button>
      </form>
    </div>

    <form v-else class="operation-card mb-7 max-w-xl space-y-4" @submit.prevent="open">
      <div><h2 class="text-lg font-semibold">Abrir caixa</h2><p class="mt-1 text-xs text-slate-500">Use o identificador do caixa cadastrado.</p></div>
      <label><span class="label">Identificador do caixa</span><input v-model="registerId" class="field" required /></label>
      <label><span class="label">Saldo inicial</span><input v-model.number="openingBalance" class="field" type="number" min="0" step="0.01" required /></label>
      <button class="btn-primary w-full">Abrir caixa</button>
    </form>

    <div>
      <h2 class="mb-3 text-lg font-semibold text-brand-navy">Histórico</h2>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>Caixa</th><th>Abertura</th><th>Fechamento</th><th>Status</th></tr></thead>
          <tbody>
            <tr v-for="shift in history" :key="shift.id">
              <td><p class="font-semibold text-brand-navy">{{ shift.cash_register.name }}</p><p class="text-xs text-[#44403c]">#{{ shift.id.slice(0, 8) }}</p></td>
              <td>{{ dateTime(shift.opened_at) }} · {{ money(shift.opening_balance) }}</td>
              <td>{{ dateTime(shift.closed_at) }}<span v-if="shift.closing_balance"> · {{ money(shift.closing_balance) }}</span></td>
              <td><span class="badge" :class="'status-' + shift.status.toLowerCase()">{{ shift.status }}</span></td>
            </tr>
            <tr v-if="!history.length"><td colspan="4" class="text-center !py-10 text-[#44403c]">Nenhum turno registrado.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>
