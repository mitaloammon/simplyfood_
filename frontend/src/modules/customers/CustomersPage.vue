<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { apiErrorMessage } from '../../shared/api/errors'
import type { Customer } from '../../shared/types/api'
import {
  createCustomer,
  deleteCustomer,
  listCustomers,
  updateCustomer,
  type CustomerPayload,
} from './customers.service'

const customers = ref<Customer[]>([])
const query = ref('')
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const editingId = ref<string | null>(null)
const form = ref<CustomerPayload>(emptyForm())

function emptyForm(): CustomerPayload {
  return { name: '', phone: null, email: null, document: null, address: null }
}

function edit(customer: Customer) {
  editingId.value = customer.id
  form.value = {
    name: customer.name,
    phone: customer.phone,
    email: customer.email,
    document: customer.document,
    address: customer.address,
  }
}

function reset() {
  editingId.value = null
  form.value = emptyForm()
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    customers.value = (await listCustomers(query.value)).data
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) await updateCustomer(editingId.value, form.value)
    else await createCustomer(form.value)
    reset()
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    saving.value = false
  }
}

async function remove(customer: Customer) {
  if (!window.confirm('Remover o cliente ' + customer.name + '?')) return
  try {
    await deleteCustomer(customer.id)
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="mb-6">
      <h1 class="page-title">Clientes</h1>
      <p class="mt-1 text-sm text-[#44403c]">Cadastro isolado por estabelecimento.</p>
    </div>

    <p v-if="error" class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ error }}</p>

    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
      <form class="card h-fit space-y-4" @submit.prevent="save">
        <div class="flex items-center justify-between">
          <h2 class="font-bold">{{ editingId ? 'Editar cliente' : 'Novo cliente' }}</h2>
          <button v-if="editingId" type="button" class="text-sm text-[#44403c] underline" @click="reset">Cancelar</button>
        </div>
        <label><span class="label">Nome</span><input v-model="form.name" class="field" required minlength="2" /></label>
        <label><span class="label">Telefone</span><input v-model="form.phone" class="field" /></label>
        <label><span class="label">E-mail</span><input v-model="form.email" class="field" type="email" /></label>
        <label><span class="label">Documento</span><input v-model="form.document" class="field" /></label>
        <label><span class="label">Endereço</span><textarea v-model="form.address" class="field" rows="2" /></label>
        <button class="btn-primary w-full" :disabled="saving">{{ saving ? 'Salvando…' : 'Salvar cliente' }}</button>
      </form>

      <div>
        <form class="mb-4 flex gap-2" @submit.prevent="load">
          <input v-model="query" class="field" placeholder="Buscar por nome, telefone ou e-mail" />
          <button class="btn-secondary" :disabled="loading">Buscar</button>
        </form>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Cliente</th><th>Contato</th><th>Documento</th><th></th></tr></thead>
            <tbody>
              <tr v-for="customer in customers" :key="customer.id">
                <td class="font-semibold">{{ customer.name }}</td>
                <td><div>{{ customer.phone || '—' }}</div><div class="text-xs text-[#44403c]">{{ customer.email }}</div></td>
                <td>{{ customer.document || '—' }}</td>
                <td class="whitespace-nowrap text-right">
                  <button class="btn-secondary mr-2" @click="edit(customer)">Editar</button>
                  <button class="btn-danger" @click="remove(customer)">Remover</button>
                </td>
              </tr>
              <tr v-if="!customers.length"><td colspan="4" class="text-center !py-10 text-[#44403c]">Nenhum cliente encontrado.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>
