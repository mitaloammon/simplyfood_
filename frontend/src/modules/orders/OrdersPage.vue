<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { apiErrorMessage } from '../../shared/api/errors'
import { dateTime, money } from '../../shared/formatters'
import { useAuthStore } from '../../shared/stores/auth'
import type { Command, Customer, DiningTable, Order, OrderStatus, OrderType, Product } from '../../shared/types/api'
import { listProducts } from '../catalog/catalog.service'
import { listCustomers } from '../customers/customers.service'
import { listCommands, listTables } from '../tables/tables.service'
import {
  addPayment,
  createOrder,
  listOrders,
  updateOrderStatus,
} from './orders.service'

const orders = ref<Order[]>([])
const products = ref<Product[]>([])
const tables = ref<DiningTable[]>([])
const commands = ref<Command[]>([])
const customers = ref<Customer[]>([])
const filterStatus = ref('')
const error = ref('')
const loading = ref(false)
const auth = useAuthStore()

const orderType = ref<OrderType>('COUNTER')
const tableId = ref('')
const commandId = ref('')
const customerId = ref('')
const productId = ref('')
const quantity = ref(1)
const notes = ref('')

const canPay = computed(() => auth.user && ['ADMIN', 'MANAGER', 'CASHIER'].includes(auth.user.role))

const transitions: Record<OrderStatus, Exclude<OrderStatus, 'OPEN'>[]> = {
  OPEN: ['IN_PREPARATION', 'CANCELLED'],
  IN_PREPARATION: ['READY', 'CANCELLED'],
  READY: ['DELIVERED', 'CANCELLED'],
  DELIVERED: ['CLOSED'],
  CLOSED: [],
  CANCELLED: [],
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const [orderPage, productPage, tablePage, commandPage, customerPage] = await Promise.all([
      listOrders(filterStatus.value),
      listProducts(),
      listTables(),
      listCommands(),
      listCustomers(),
    ])
    orders.value = orderPage.data
    products.value = productPage.data.filter((product) => product.is_available)
    tables.value = tablePage.data
    commands.value = commandPage.data.filter((command) => command.status === 'OPEN')
    customers.value = customerPage.data
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  } finally {
    loading.value = false
  }
}

async function saveOrder() {
  error.value = ''
  const selectedCommand = commands.value.find((command) => command.id === commandId.value)
  try {
    await createOrder({
      order_type: orderType.value,
      table_id: orderType.value === 'COUNTER'
        ? null
        : orderType.value === 'COMMAND'
          ? selectedCommand?.table?.id || null
          : tableId.value,
      command_id: orderType.value === 'COMMAND' ? commandId.value : null,
      customer_id: customerId.value || null,
      items: [{ product_id: productId.value, quantity: quantity.value, notes: notes.value || null }],
    })
    productId.value = ''
    quantity.value = 1
    notes.value = ''
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function changeStatus(order: Order, status: Exclude<OrderStatus, 'OPEN'>) {
  try {
    await updateOrderStatus(order.id, status)
    await load()
  } catch (exception) {
    error.value = apiErrorMessage(exception)
  }
}

async function registerPayment(order: Order) {
  const method = window.prompt('Método: CASH, CREDIT_CARD, DEBIT_CARD, PIX ou VOUCHER', 'CASH')
  if (!method) return
  const rawAmount = window.prompt('Valor do pagamento', String(order.total_amount))
  if (!rawAmount) return
  try {
    await addPayment(order.id, method, Number(rawAmount.replace(',', '.')))
    window.alert('Pagamento registrado.')
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
      <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-brand-blue">Atendimento</p>
      <h1 class="page-title">Pedidos</h1>
      <p class="mt-2 text-sm text-slate-500">Abra pedidos e acompanhe cada etapa.</p>
    </div>
    <p v-if="error" class="mb-5 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">{{ error }}</p>

    <form class="operation-card mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="saveOrder">
      <div class="flex items-center justify-between md:col-span-2 xl:col-span-4">
        <h2 class="text-lg font-semibold">Novo pedido</h2>
        <span class="text-xs font-medium text-[#44403c]">Primeiro item</span>
      </div>
      <label><span class="label">Tipo</span><select v-model="orderType" class="field"><option value="COUNTER">Balcão</option><option value="TABLE">Mesa</option><option value="COMMAND">Comanda</option></select></label>
      <label v-if="orderType === 'TABLE'"><span class="label">Mesa</span><select v-model="tableId" class="field" required><option value="" disabled>Selecione</option><option v-for="table in tables" :key="table.id" :value="table.id">Mesa {{ table.number }} · {{ table.status }}</option></select></label>
      <label v-if="orderType === 'COMMAND'"><span class="label">Comanda</span><select v-model="commandId" class="field" required><option value="" disabled>Selecione</option><option v-for="command in commands" :key="command.id" :value="command.id">{{ command.code }} · Mesa {{ command.table?.number }}</option></select></label>
      <label><span class="label">Cliente</span><select v-model="customerId" class="field"><option value="">Sem cliente</option><option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option></select></label>
      <label><span class="label">Produto</span><select v-model="productId" class="field" required><option value="" disabled>Selecione</option><option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} · {{ money(product.price) }}</option></select></label>
      <label><span class="label">Quantidade</span><input v-model.number="quantity" class="field" type="number" min="1" required /></label>
      <label class="md:col-span-2"><span class="label">Observação</span><input v-model="notes" class="field" placeholder="Ex.: sem cebola" /></label>
      <button class="btn-primary md:col-span-2 xl:col-span-4">Abrir pedido</button>
    </form>

    <form class="mb-5 flex max-w-lg gap-2" @submit.prevent="load">
      <select v-model="filterStatus" class="field">
        <option value="">Todos os status</option>
        <option v-for="status in Object.keys(transitions)" :key="status" :value="status">{{ status }}</option>
      </select>
      <button class="btn-secondary" :disabled="loading">Filtrar</button>
    </form>

    <div class="grid gap-4 lg:grid-cols-2">
      <article v-for="order in orders" :key="order.id" class="operation-card">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <p class="text-xs font-medium text-[#44403c]">#{{ order.id.slice(0, 8) }}</p>
            <h2 class="mt-2 text-xl font-bold tracking-[-0.03em] text-brand-navy">{{ order.order_type }} · {{ money(order.total_amount) }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ dateTime(order.created_at) }}</p>
          </div>
          <span class="badge" :class="'status-' + order.status.toLowerCase()">{{ order.status }}</span>
        </div>

        <div class="my-5 space-y-2 border-y border-slate-100 py-4">
          <div v-for="item in order.items || []" :key="item.id" class="flex justify-between gap-3 text-sm">
            <span class="text-slate-600">{{ item.quantity }}× {{ item.product.name }}</span>
            <span class="font-semibold text-brand-navy">{{ money(item.total_price) }}</span>
          </div>
          <p v-if="!order.items?.length" class="text-sm text-[#44403c]">Sem itens carregados.</p>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            v-for="status in transitions[order.status]"
            :key="status"
            class="btn-secondary"
            @click="changeStatus(order, status)"
          >
            {{ status === 'IN_PREPARATION' ? 'Preparar' : status === 'READY' ? 'Marcar pronto' : status === 'DELIVERED' ? 'Entregar' : status === 'CLOSED' ? 'Fechar conta' : 'Cancelar' }}
          </button>
          <button v-if="canPay && !['CLOSED', 'CANCELLED'].includes(order.status)" class="btn-primary" @click="registerPayment(order)">
            Receber
          </button>
        </div>
      </article>
      <p v-if="!orders.length" class="operation-card text-center text-[#44403c] lg:col-span-2">Nenhum pedido encontrado.</p>
    </div>
  </section>
</template>
