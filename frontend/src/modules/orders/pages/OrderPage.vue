<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { CustomerApi, type CustomerDto } from '@/modules/customers/api/CustomerApi';
import { OrderApi, type OrderDto, type OrderPayload } from '../api/OrderApi';
import { ProductApi, type CreateProductPayload, type ProductDto } from '@/modules/products/api/ProductApi';

type EditableItem = {
  product_id: number | null;
  quantity: number;
  price: number;
};

const orderApi = new OrderApi();
const customerApi = new CustomerApi();
const productApi = new ProductApi();
const route = useRoute();
const router = useRouter();

const loading = ref(false);
const saving = ref(false);
const deletingId = ref<number | null>(null);
const successMessage = ref('');
const errorMessage = ref('');
const orders = ref<OrderDto[]>([]);
const customers = ref<CustomerDto[]>([]);
const products = ref<ProductDto[]>([]);
const creatingProduct = ref(false);
const productFormVisible = ref(false);
const productFormError = ref('');
const productForm = reactive({
  nome: '',
  preco: 0,
  descricao: '',
});
const detailOrder = ref<OrderDto | null>(null);
const editingOrderId = ref<number | null>(null);
const formPanelRef = ref<HTMLElement | null>(null);
const quickCreateMode = computed(() => route.query.action === 'create');
const highlightedOrderId = computed(() => {
  const value = route.query.highlight;
  if (!value) {
    return null;
  }

  const parsed = Number(value);
  return Number.isNaN(parsed) ? null : parsed;
});

const filters = reactive({
  status: '',
  customer: '',
});

const form = reactive({
  customer_id: 0,
  status: 'WAITING_PAYMENT',
  items: [] as EditableItem[],
});

const statusOptions = ['WAITING_PAYMENT', 'PAID', 'PREPARING', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED'];

const filteredOrders = computed(() => {
  const statusFilter = filters.status.trim().toLowerCase();
  const customerFilter = filters.customer.trim().toLowerCase();

  return orders.value.filter((order) => {
    const matchStatus = !statusFilter || order.status.toLowerCase().includes(statusFilter);
    const customerName = order.customer?.name?.toLowerCase() || '';
    const matchCustomer = !customerFilter || customerName.includes(customerFilter);
    return matchStatus && matchCustomer;
  });
});

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => sum + item.quantity * item.price, 0);
});

const itemCount = computed(() => {
  return form.items.reduce((sum, item) => sum + item.quantity, 0);
});

const total = computed(() => Number(subtotal.value.toFixed(2)));

const resetForm = () => {
  form.customer_id = 0;
  form.status = 'WAITING_PAYMENT';
  form.items = [];
  addItem();
  editingOrderId.value = null;
};

const resetProductForm = () => {
  productForm.nome = '';
  productForm.preco = 0;
  productForm.descricao = '';
  productFormError.value = '';
};

const startCreateMode = async () => {
  resetForm();
  errorMessage.value = '';
  successMessage.value = '';
  detailOrder.value = null;

  await nextTick();
  formPanelRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const backToManagement = () => {
  router.replace('/orders');
};

const addItem = () => {
  form.items.push({
    product_id: null,
    quantity: 1,
    price: 0,
  });
};

const removeItem = (index: number) => {
  form.items.splice(index, 1);
  if (form.items.length === 0) {
    addItem();
  }
};

const onProductChange = (index: number) => {
  const item = form.items[index];
  if (!item.product_id) {
    item.price = 0;
    return;
  }

  const product = products.value.find((candidate) => candidate.id === item.product_id);
  if (product) {
    item.price = Number(product.preco || 0);
  }
};

const openProductForm = () => {
  productFormVisible.value = true;
  productFormError.value = '';
};

const closeProductForm = () => {
  productFormVisible.value = false;
  resetProductForm();
};

const createProductFromOrderFlow = async () => {
  productFormError.value = '';

  if (!productForm.nome.trim()) {
    productFormError.value = 'Informe o nome do produto.';
    return;
  }

  if (Number(productForm.preco) < 0) {
    productFormError.value = 'Informe um preco valido para o produto.';
    return;
  }

  creatingProduct.value = true;

  try {
    const payload: CreateProductPayload = {
      nome: productForm.nome.trim(),
      preco: Number(productForm.preco),
      descricao: productForm.descricao.trim() || undefined,
      ativo: true,
      tempo_preparo: 0,
    };

    const response = await productApi.create(payload);
    const createdProduct = response.data.data;

    await loadProducts();

    const targetItem = form.items.find((item) => item.product_id === null) || form.items[0];
    if (targetItem) {
      targetItem.product_id = Number(createdProduct.id);
      targetItem.price = Number(createdProduct.preco || 0);
    }

    successMessage.value = 'Produto cadastrado e selecionado no pedido.';
    closeProductForm();
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      productFormError.value = error.response?.data?.message || 'Erro ao cadastrar produto.';
    } else {
      productFormError.value = 'Erro ao cadastrar produto.';
    }
  } finally {
    creatingProduct.value = false;
  }
};

const toOrderPayload = (): OrderPayload => {
  return {
    customer_id: form.customer_id,
    status: form.status,
    items: form.items
      .filter((item) => item.product_id !== null)
      .map((item) => ({
        product_id: Number(item.product_id),
        quantity: Number(item.quantity),
        price: Number(item.price),
      })),
    total: total.value,
  };
};

const validateForm = (): boolean => {
  if (!form.customer_id) {
    errorMessage.value = 'Selecione um cliente para o pedido.';
    return false;
  }

  const payloadItems = form.items.filter((item) => item.product_id !== null);
  if (payloadItems.length === 0) {
    errorMessage.value = 'Adicione pelo menos um item ao pedido.';
    return false;
  }

  const hasInvalidItem = payloadItems.some((item) => item.quantity <= 0 || item.price < 0);
  if (hasInvalidItem) {
    errorMessage.value = 'Itens do pedido possuem quantidade ou preco invalidos.';
    return false;
  }

  return true;
};

const loadCustomers = async () => {
  const response = await customerApi.getAll();
  customers.value = response.data.data || [];
};

const loadProducts = async () => {
  const response = await productApi.getActive();
  products.value = response.data.data || [];
};

const loadOrders = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await orderApi.getAll();
    orders.value = response.data.data || [];
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao carregar pedidos.';
    } else {
      errorMessage.value = 'Erro ao carregar pedidos.';
    }
  } finally {
    loading.value = false;
  }
};

const loadDependencies = async () => {
  try {
    await Promise.all([loadCustomers(), loadProducts(), loadOrders()]);
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao carregar dados de pedidos.';
    } else {
      errorMessage.value = 'Erro ao carregar dados de pedidos.';
    }
  }
};

const saveOrder = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (!validateForm()) {
    return;
  }

  saving.value = true;

  try {
    const payload = toOrderPayload();
    let createdOrderId: number | null = null;

    if (editingOrderId.value) {
      await orderApi.update(editingOrderId.value, payload);
      successMessage.value = 'Pedido atualizado com sucesso.';
    } else {
      const response = await orderApi.create(payload);
      createdOrderId = response.data.data.id;
      successMessage.value = 'Pedido criado com sucesso.';
    }

    await loadOrders();

    if (quickCreateMode.value && createdOrderId) {
      router.replace({
        path: '/orders',
        query: { highlight: String(createdOrderId) },
      });

      detailOrder.value = orders.value.find((order) => order.id === createdOrderId) || null;
    }

    resetForm();
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao salvar pedido.';
    } else {
      errorMessage.value = 'Erro ao salvar pedido.';
    }
  } finally {
    saving.value = false;
  }
};

const editOrder = (order: OrderDto) => {
  editingOrderId.value = order.id;
  form.customer_id = order.customer_id;
  form.status = order.status;
  form.items = (order.items || []).map((item) => ({
    product_id: item.product_id,
    quantity: item.quantity,
    price: Number(item.price),
  }));
  if (form.items.length === 0) {
    addItem();
  }
  detailOrder.value = order;
};

const showOrderDetail = (order: OrderDto) => {
  detailOrder.value = order;
};

const deleteOrder = async (order: OrderDto) => {
  const confirmed = window.confirm(`Deseja excluir o pedido #${order.id}?`);
  if (!confirmed) {
    return;
  }

  deletingId.value = order.id;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await orderApi.remove(order.id);
    if (detailOrder.value?.id === order.id) {
      detailOrder.value = null;
    }
    orders.value = orders.value.filter((current) => current.id !== order.id);
    successMessage.value = 'Pedido excluido com sucesso.';
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao excluir pedido.';
    } else {
      errorMessage.value = 'Erro ao excluir pedido.';
    }
  } finally {
    deletingId.value = null;
  }
};

onMounted(async () => {
  resetForm();
  await loadDependencies();

  if (route.query.action === 'create') {
    await startCreateMode();
  }

  if (highlightedOrderId.value) {
    detailOrder.value = orders.value.find((order) => order.id === highlightedOrderId.value) || null;
  }
});

watch(
  () => route.query.highlight,
  () => {
    if (!highlightedOrderId.value) {
      return;
    }

    detailOrder.value = orders.value.find((order) => order.id === highlightedOrderId.value) || null;
  }
);
</script>

<template>
  <div class="orders-page">
    <header class="page-header">
      <h1>Pedidos</h1>
      <p v-if="quickCreateMode">Cadastro rapido de pedido iniciado pelo Dashboard.</p>
      <p v-else>Gerencie pedidos associados ao seu ambiente com total reativo em tempo real.</p>
      <button v-if="quickCreateMode" class="btn secondary" type="button" @click="backToManagement">Voltar para gerenciamento</button>
    </header>

    <div v-if="successMessage" class="alert success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert error">{{ errorMessage }}</div>

    <section ref="formPanelRef" class="panel order-form-panel">
      <h2>{{ editingOrderId ? 'Editar Pedido' : 'Novo Pedido' }}</h2>

      <div class="form-grid">
        <label>
          Cliente
          <select v-model.number="form.customer_id" class="input">
            <option :value="0">Selecione...</option>
            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
              {{ customer.name }}
            </option>
          </select>
        </label>

        <label>
          Status
          <select v-model="form.status" class="input">
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </label>
      </div>

      <div class="items-header">
        <h3>Itens do Pedido</h3>
        <div class="inline-actions">
          <button class="btn secondary" @click="openProductForm" type="button">Cadastrar Produto</button>
          <button class="btn secondary" @click="addItem" type="button">Adicionar Item</button>
        </div>
      </div>

      <div v-if="products.length === 0" class="empty-products">
        Nenhum produto ativo encontrado. Cadastre um produto para continuar o pedido.
      </div>

      <section v-if="productFormVisible" class="product-create-box">
        <h3>Novo Produto</h3>
        <div class="product-create-grid">
          <input v-model="productForm.nome" class="input" placeholder="Nome do produto" />
          <input v-model.number="productForm.preco" class="input" type="number" min="0" step="0.01" placeholder="Preco" />
          <input v-model="productForm.descricao" class="input full" placeholder="Descricao (opcional)" />
        </div>

        <div v-if="productFormError" class="alert error product-error">{{ productFormError }}</div>

        <div class="form-actions">
          <button class="btn secondary" type="button" @click="closeProductForm" :disabled="creatingProduct">Cancelar</button>
          <button class="btn primary" type="button" @click="createProductFromOrderFlow" :disabled="creatingProduct">
            {{ creatingProduct ? 'Cadastrando...' : 'Salvar Produto' }}
          </button>
        </div>
      </section>

      <div class="items-list">
        <div v-for="(item, index) in form.items" :key="index" class="item-row">
          <select v-model.number="item.product_id" class="input" @change="onProductChange(index)">
            <option :value="null">Produto...</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.nome }}
            </option>
          </select>

          <input v-model.number="item.quantity" class="input" type="number" min="1" placeholder="Qtd" />
          <input v-model.number="item.price" class="input" type="number" min="0" step="0.01" placeholder="Preco" />

          <button class="btn danger" type="button" @click="removeItem(index)">Remover</button>
        </div>
      </div>

      <div class="summary-box">
        <p><strong>Quantidade de itens:</strong> {{ itemCount }}</p>
        <p><strong>Subtotal:</strong> R$ {{ subtotal.toFixed(2) }}</p>
        <p class="total"><strong>Total:</strong> R$ {{ total.toFixed(2) }}</p>
      </div>

      <div class="form-actions">
        <button class="btn secondary" type="button" @click="resetForm" :disabled="saving">Limpar</button>
        <button class="btn primary" type="button" @click="saveOrder" :disabled="saving">
          {{ saving ? 'Salvando...' : editingOrderId ? 'Atualizar Pedido' : 'Criar Pedido' }}
        </button>
      </div>
    </section>

    <section v-if="!quickCreateMode" class="panel">
      <div class="filters">
        <input v-model="filters.status" class="input" placeholder="Filtrar por status" />
        <input v-model="filters.customer" class="input" placeholder="Filtrar por cliente" />
      </div>

      <div v-if="loading" class="empty">Carregando pedidos...</div>
      <div v-else-if="filteredOrders.length === 0" class="empty">Nenhum pedido encontrado.</div>

      <div v-else class="order-list">
        <article
          v-for="order in filteredOrders"
          :key="order.id"
          class="order-card"
          :class="{ highlighted: highlightedOrderId === order.id }"
        >
          <div class="order-head">
            <h3>Pedido #{{ order.id }}</h3>
            <span>{{ order.status }}</span>
          </div>
          <p><strong>Cliente:</strong> {{ order.customer?.name || 'Nao informado' }}</p>
          <p><strong>Total:</strong> R$ {{ Number(order.total || 0).toFixed(2) }}</p>

          <div class="card-actions">
            <button class="btn secondary" type="button" @click="showOrderDetail(order)">Detalhes</button>
            <button class="btn secondary" type="button" @click="editOrder(order)">Editar</button>
            <button class="btn danger" type="button" @click="deleteOrder(order)" :disabled="deletingId === order.id">
              {{ deletingId === order.id ? 'Excluindo...' : 'Excluir' }}
            </button>
          </div>
        </article>
      </div>
    </section>

    <section v-if="!quickCreateMode && detailOrder" class="panel detail-panel">
      <h2>Detalhes do Pedido #{{ detailOrder.id }}</h2>
      <p><strong>Status:</strong> {{ detailOrder.status }}</p>
      <p><strong>Cliente:</strong> {{ detailOrder.customer?.name || '-' }}</p>
      <p><strong>Total:</strong> R$ {{ Number(detailOrder.total || 0).toFixed(2) }}</p>
      <p><strong>Itens:</strong> {{ detailOrder.items?.length || 0 }}</p>

      <ul class="detail-items" v-if="detailOrder.items && detailOrder.items.length > 0">
        <li v-for="item in detailOrder.items" :key="item.id">
          {{ item.product?.nome || `Produto #${item.product_id}` }} - {{ item.quantity }} x R$ {{ Number(item.price).toFixed(2) }}
        </li>
      </ul>
    </section>
  </div>
</template>

<style scoped>
.orders-page {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header h1 {
  margin: 0;
  color: #f8fafc;
  font-size: 2rem;
}

.page-header p {
  color: #94a3b8;
  margin-top: 0.35rem;
}

.panel {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1rem;
  margin-top: 1rem;
}

.order-form-panel h2,
.detail-panel h2 {
  margin: 0 0 1rem 0;
  color: #f8fafc;
  font-size: 1.15rem;
}

.form-grid,
.filters {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  color: #cbd5e1;
  font-size: 0.85rem;
}

.input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  padding: 0.6rem 0.7rem;
  box-sizing: border-box;
}

.items-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
}

.inline-actions {
  display: flex;
  gap: 0.5rem;
}

.items-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #f8fafc;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  margin-top: 0.75rem;
}

.empty-products {
  margin-top: 0.75rem;
  color: #f6ad55;
  font-size: 0.9rem;
}

.product-create-box {
  margin-top: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 0.8rem;
}

.product-create-box h3 {
  margin: 0 0 0.65rem 0;
  color: #f8fafc;
  font-size: 0.95rem;
}

.product-create-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.6rem;
}

.product-create-grid .full {
  grid-column: 1 / -1;
}

.product-error {
  margin-top: 0.7rem;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 0.5rem;
}

.summary-box {
  margin-top: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 0.75rem;
}

.summary-box p {
  margin: 0.3rem 0;
  color: #cbd5e1;
}

.summary-box .total {
  color: #f8fafc;
  font-size: 1.05rem;
}

.form-actions,
.card-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 0.9rem;
}

.order-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.order-card {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 0.85rem;
  background: rgba(15, 23, 42, 0.5);
}

.order-card.highlighted {
  border-color: rgba(56, 161, 105, 0.65);
  box-shadow: 0 0 0 1px rgba(56, 161, 105, 0.35), 0 10px 22px rgba(0, 0, 0, 0.2);
}

.order-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-head h3 {
  margin: 0;
  color: #f8fafc;
}

.order-head span {
  color: #f59e0b;
  font-size: 0.8rem;
}

.order-card p,
.detail-panel p,
.detail-items li {
  color: #cbd5e1;
  margin: 0.4rem 0;
}

.detail-items {
  padding-left: 1.2rem;
  margin: 0.6rem 0 0 0;
}

.btn {
  border-radius: 10px;
  border: 1px solid transparent;
  padding: 0.45rem 0.8rem;
  cursor: pointer;
  font-size: 0.85rem;
}

.btn.primary {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: #fff;
}

.btn.secondary {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
}

.btn.danger {
  background: rgba(229, 62, 62, 0.2);
  border-color: rgba(229, 62, 62, 0.35);
  color: #fecaca;
}

.alert {
  margin-top: 1rem;
  padding: 0.7rem 0.9rem;
  border-radius: 10px;
  font-size: 0.9rem;
}

.alert.success {
  background: rgba(72, 187, 120, 0.15);
  border: 1px solid rgba(72, 187, 120, 0.25);
  color: #68d391;
}

.alert.error {
  background: rgba(229, 62, 62, 0.15);
  border: 1px solid rgba(229, 62, 62, 0.25);
  color: #fc8181;
}

.empty {
  margin-top: 0.75rem;
  color: #94a3b8;
}

@media (max-width: 900px) {
  .form-grid,
  .filters,
  .order-list {
    grid-template-columns: 1fr;
  }

  .item-row {
    grid-template-columns: 1fr;
  }

  .inline-actions {
    width: 100%;
    justify-content: flex-end;
    flex-wrap: wrap;
  }

  .product-create-grid {
    grid-template-columns: 1fr;
  }
}
</style>
