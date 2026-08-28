<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { apiErrorMessage } from '../../shared/api/errors'
import type { Command, CommandStatus, DiningTable, TableStatus } from '../../shared/types/api'
import {
  createTable,
  listCommands,
  listTables,
  openCommand,
  updateCommandStatus,
  updateTableStatus,
} from './tables.service'

const tables = ref<DiningTable[]>([])
const commands = ref<Command[]>([])
const tableNumber = ref<number>(1)
const capacity = ref<number>(4)
const commandCode = ref('')
const commandTableId = ref('')
const error = ref('')
const loading = ref(false)
const tableStatuses: TableStatus[] = ['FREE', 'OCCUPIED', 'RESERVED', 'BILLING']
const commandStatuses: Exclude<CommandStatus, 'FREE'>[] = ['OPEN', 'CLOSED', 'BLOCKED']

async function load() {
  loading.value = true
  error.value = ''
  try {
    const [tablePage, commandPage] = await Promise.all([listTables(), listCommands()])
    tables.value = tablePage.data
    commands.value = commandPage.data
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

async function addTable() {
  try {
    await createTable(tableNumber.value, capacity.value)
    tableNumber.value += 1
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function changeTableStatus(table: DiningTable, status: TableStatus) {
  try {
    await updateTableStatus(table.id, status)
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function addCommand() {
  try {
    await openCommand(commandCode.value, commandTableId.value)
    commandCode.value = ''
    commandTableId.value = ''
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function changeCommandStatus(command: Command, status: Exclude<CommandStatus, 'FREE'>) {
  try {
    await updateCommandStatus(command.id, status)
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="mb-7">
      <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-blue">Salão</p>
      <h1 class="page-title">Mesas e comandas</h1>
      <p class="mt-2 text-sm text-slate-500">Veja a ocupação e mova o atendimento sem perder tempo.</p>
    </div>
    <p v-if="error" class="mb-5 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</p>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(420px,0.9fr)]">
      <div class="space-y-5">
        <form class="operation-card grid grid-cols-2 gap-4" @submit.prevent="addTable">
          <div class="col-span-2 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Nova mesa</h2>
            <span class="text-xs font-medium text-[#44403c]">Configuração do salão</span>
          </div>
          <label><span class="label">Número</span><input v-model.number="tableNumber" class="field" type="number" min="1" required /></label>
          <label><span class="label">Lugares</span><input v-model.number="capacity" class="field" type="number" min="1" required /></label>
          <button class="btn-primary col-span-2">Adicionar mesa</button>
        </form>

        <div class="grid gap-3 sm:grid-cols-2">
          <article v-for="table in tables" :key="table.id" class="operation-card">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-2xl font-bold tracking-[-0.04em] text-brand-navy">Mesa {{ table.number }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ table.capacity }} lugares</p>
              </div>
              <span class="badge" :class="'status-' + table.status.toLowerCase()">{{ table.status }}</span>
            </div>
            <label class="mt-5 block">
              <span class="label">Mudar status</span>
              <select class="field" :value="table.status" @change="changeTableStatus(table, ($event.target as HTMLSelectElement).value as TableStatus)">
                <option v-for="status in tableStatuses" :key="status" :value="status">{{ status }}</option>
              </select>
            </label>
          </article>
        </div>
      </div>

      <div class="space-y-5">
        <form class="operation-card grid gap-4 sm:grid-cols-2" @submit.prevent="addCommand">
          <h2 class="text-lg font-semibold sm:col-span-2">Abrir comanda</h2>
          <label><span class="label">Código</span><input v-model="commandCode" class="field" required /></label>
          <label><span class="label">Mesa</span><select v-model="commandTableId" class="field" required><option value="" disabled>Selecione</option><option v-for="table in tables" :key="table.id" :value="table.id">Mesa {{ table.number }} · {{ table.status }}</option></select></label>
          <button class="btn-primary sm:col-span-2">Abrir comanda</button>
        </form>

        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Comanda</th><th>Mesa</th><th>Status</th></tr></thead>
            <tbody>
              <tr v-for="command in commands" :key="command.id">
                <td class="font-semibold text-brand-navy">{{ command.code }}</td>
                <td>{{ command.table ? 'Mesa ' + command.table.number : '—' }}</td>
                <td><select class="field min-w-32" :value="command.status" @change="changeCommandStatus(command, ($event.target as HTMLSelectElement).value as Exclude<CommandStatus, 'FREE'>)"><option v-for="status in commandStatuses" :key="status" :value="status">{{ status }}</option></select></td>
              </tr>
              <tr v-if="!commands.length"><td colspan="3" class="text-center !py-10 text-[#44403c]">Nenhuma comanda aberta.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>
