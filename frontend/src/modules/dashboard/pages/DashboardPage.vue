<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useAuthStore } from '@/shared/stores/auth';
import { useToastStore } from '@/shared/stores/toast';
import { useDashboardMetrics } from '../composables/useDashboardMetrics';
import CustomerForm from '@/modules/customers/components/CustomerForm.vue';
import { CreateCustomerService } from '@/modules/customers/services/CreateCustomerService';
import { CustomerApi, type CreateCustomerDto, type CustomerDto } from '@/modules/customers/api/CustomerApi';
import type { CustomerFormInput } from '@/modules/customers/validators/customerSchema';
import { OrderApi, type OrderPayload } from '@/modules/orders/api/OrderApi';
import ProductQuickCreateModal from '@/modules/products/components/ProductQuickCreateModal.vue';
import {
  ProductApi,
  type CreateProductPayload,
  type ProductDto,
  type ProductQuickCreateDefaults,
  type ProductQuickCreateOptions,
} from '@/modules/products/api/ProductApi';
import axios from 'axios';

const authStore = useAuthStore();
const toastStore = useToastStore();
const { loading, errorMessage, metrics, user, loadMetrics } = useDashboardMetrics();
const customerApi = new CustomerApi();
const createCustomerService = new CreateCustomerService(customerApi);
const orderApi = new OrderApi();
const productApi = new ProductApi();

const customerModalVisible = ref(false);
const orderModalVisible = ref(false);
const customerModalCardRef = ref<HTMLElement | null>(null);
const orderModalCardRef = ref<HTMLElement | null>(null);

const customerSaving = ref(false);
const customerFormKey = ref(0);

const orderSaving = ref(false);
const creatingProduct = ref(false);
const productQuickCreateModalVisible = ref(false);
const productQuickCreateFieldErrors = ref<Record<string, string>>({});
const productQuickCreateError = ref('');
const customers = ref<CustomerDto[]>([]);
const products = ref<ProductDto[]>([]);
const productQuickCreateOptions = ref<ProductQuickCreateOptions | null>(null);

const orderForm = reactive({
  customer_id: 0,
  status: 'WAITING_PAYMENT',
  items: [{ product_id: null as number | null, quantity: 1, price: 0 }],
});

const productQuickCreateDefaults = computed<ProductQuickCreateDefaults>(() => {
  if (productQuickCreateOptions.value?.defaults) {
    return productQuickCreateOptions.value.defaults;
  }

  return {
    ativo: true,
    controla_estoque: false,
    produzido_cozinha: true,
    delivery: true,
    balcao: true,
    mesa: true,
    retirada: true,
    unidade: 'UN',
  };
});

const loadProductQuickCreateOptions = async () => {
  if (productQuickCreateOptions.value) {
    return;
  }

  const response = await productApi.getQuickCreateOptions();
  productQuickCreateOptions.value = response.data.data || null;
};

const welcomeName = computed(() => {
  const rawName = user.value?.name || authStore.user?.name || 'Operador';
  return rawName.charAt(0).toUpperCase() + rawName.slice(1);
});

const metricColorMap: Record<string, string> = {
  customers: '#3182ce',
  orders_today: '#38a169',
  revenue_today: '#dd6b20',
  delivery_avg: '#e53e3e',
};

const resolveMetricColor = (key: string): string => metricColorMap[key] || '#94a3b8';

const orderSubtotal = computed(() => orderForm.items.reduce((sum, item) => sum + item.quantity * item.price, 0));
const orderTotal = computed(() => Number(orderSubtotal.value.toFixed(2)));

const mapFormToCreateDto = (form: CustomerFormInput): CreateCustomerDto => ({
  name: form.name,
  phone: form.phone,
  email: form.email,
  whatsapp: form.whatsapp,
  cpf_cnpj: form.cpfCnpj,
  cep: form.cep || undefined,
  address: form.address || undefined,
  number: form.number || undefined,
  complement: form.complement || undefined,
  neighborhood: form.neighborhood || undefined,
  city: form.city || undefined,
  state: form.state || undefined,
});

const resetOrderForm = () => {
  orderForm.customer_id = 0;
  orderForm.status = 'WAITING_PAYMENT';
  orderForm.items = [{ product_id: null, quantity: 1, price: 0 }];
  productQuickCreateFieldErrors.value = {};
  productQuickCreateError.value = '';
};

const parseBrazilianCurrency = (value: string): number => {
  const digits = value.replace(/\D/g, '');
  if (!digits) {
    return 0;
  }

  return Number(digits) / 100;
};

const formatBrazilianCurrency = (value: number): string => {
  return Number(value || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const onItemPriceInput = (index: number, event: Event) => {
  const target = event.target as HTMLInputElement;
  const parsedValue = parseBrazilianCurrency(target.value);
  orderForm.items[index].price = parsedValue;
  target.value = formatBrazilianCurrency(parsedValue);
};

const toMultipartPayload = (payload: CreateProductPayload): FormData => {
  const formData = new FormData();

  Object.entries(payload).forEach(([key, value]) => {
    if (value === undefined || value === null) {
      return;
    }

    if (value instanceof File) {
      formData.append(key, value);
      return;
    }

    formData.append(key, String(value));
  });

  return formData;
};

const isAnyModalOpen = computed(() => customerModalVisible.value || orderModalVisible.value);

const lockBodyScroll = () => {
  document.body.style.overflow = 'hidden';
};

const unlockBodyScroll = () => {
  document.body.style.overflow = '';
};

const handleEscapeKey = (event: KeyboardEvent) => {
  if (event.key !== 'Escape') {
    return;
  }

  if (orderModalVisible.value) {
    closeOrderModal();
    return;
  }

  if (customerModalVisible.value) {
    closeCustomerModal();
  }
};

const openCustomerModal = async () => {
  customerModalVisible.value = true;

  await nextTick();
  customerModalCardRef.value?.focus();
};

const closeCustomerModal = () => {
  customerModalVisible.value = false;
  customerFormKey.value += 1;
};

const openProductQuickCreateModal = async () => {
  await loadProductQuickCreateOptions();
  productQuickCreateFieldErrors.value = {};
  productQuickCreateError.value = '';
  productQuickCreateModalVisible.value = true;
};

const openOrderModal = async () => {
  resetOrderForm();
  orderModalVisible.value = true;

  try {
    const [customersResponse, productsResponse] = await Promise.all([
      customerApi.getAll(),
      productApi.getActive(),
      loadProductQuickCreateOptions(),
    ]);
    customers.value = customersResponse.data.data || [];
    products.value = productsResponse.data.data || [];
    orderForm.customer_id = customers.value[0]?.id || 0;

    if (!orderForm.customer_id) {
      toastStore.error('Cadastre um cliente antes de criar um pedido.');
    }

    await nextTick();
    orderModalCardRef.value?.focus();
  } catch (error: unknown) {
    orderModalVisible.value = false;
    if (axios.isAxiosError(error)) {
      toastStore.error(error.response?.data?.message || 'Erro ao carregar dados para novo pedido.');
    } else {
      toastStore.error('Erro ao carregar dados para novo pedido.');
    }
  }
};

const closeOrderModal = () => {
  orderModalVisible.value = false;
  productQuickCreateModalVisible.value = false;
  resetOrderForm();
};

const createCustomerFromDashboard = async (payload: CustomerFormInput) => {
  customerSaving.value = true;

  try {
    await createCustomerService.execute(mapFormToCreateDto(payload));
    closeCustomerModal();
    toastStore.success('Cliente cadastrado com sucesso.');
    await loadMetrics();
  } catch (error: unknown) {
    if (error instanceof Error) {
      toastStore.error(error.message);
    } else {
      toastStore.error('Erro ao cadastrar cliente.');
    }
  } finally {
    customerSaving.value = false;
  }
};

const addOrderItem = () => {
  orderForm.items.push({ product_id: null, quantity: 1, price: 0 });
};

const removeOrderItem = (index: number) => {
  orderForm.items.splice(index, 1);
  if (orderForm.items.length === 0) {
    addOrderItem();
  }
};

const onOrderProductChange = (index: number) => {
  const item = orderForm.items[index];
  if (!item.product_id) {
    item.price = 0;
    return;
  }

  const product = products.value.find((candidate) => candidate.id === item.product_id);
  if (product) {
    item.price = Number((product.preco_venda ?? product.preco) || 0);
  }
};

const openProductQuickCreateModalFromOrder = async () => {
  await loadProductQuickCreateOptions();
  productQuickCreateFieldErrors.value = {};
  productQuickCreateError.value = '';
  productQuickCreateModalVisible.value = true;
};

const createProductFromQuickCreate = async (payload: CreateProductPayload) => {
  productQuickCreateFieldErrors.value = {};
  productQuickCreateError.value = '';

  creatingProduct.value = true;

  try {
    const response = await productApi.create(toMultipartPayload(payload));
    const createdProduct = response.data.data;
    const productsResponse = await productApi.getActive();
    products.value = productsResponse.data.data || [];

    const targetItem = orderForm.items.find((item) => item.product_id === null) || orderForm.items[0];
    if (targetItem) {
      targetItem.product_id = Number(createdProduct.id);
      targetItem.price = Number((createdProduct.preco_venda ?? createdProduct.preco) || 0);
    }

    productQuickCreateModalVisible.value = false;
    toastStore.success(orderModalVisible.value
      ? 'Produto cadastrado e selecionado no pedido.'
      : 'Produto cadastrado com sucesso.');
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      productQuickCreateError.value = error.response?.data?.message || 'Erro ao cadastrar produto.';
      const validationErrors = error.response?.data?.errors as Record<string, string[]> | undefined;
      if (validationErrors) {
        productQuickCreateFieldErrors.value = Object.fromEntries(
          Object.entries(validationErrors).map(([key, value]) => [key, value[0]])
        );
      }
    } else {
      productQuickCreateError.value = 'Erro ao cadastrar produto.';
    }
  } finally {
    creatingProduct.value = false;
  }
};

const saveOrderFromDashboard = async () => {
  if (!orderForm.customer_id) {
    toastStore.error('Cadastre um cliente antes de criar um pedido.');
    return;
  }

  const payloadItems = orderForm.items.filter((item) => item.product_id !== null);
  if (payloadItems.length === 0) {
    toastStore.error('Adicione pelo menos um item ao pedido.');
    return;
  }

  const hasInvalidItem = payloadItems.some((item) => item.quantity <= 0 || item.price < 0);
  if (hasInvalidItem) {
    toastStore.error('Itens do pedido possuem quantidade ou preço inválidos.');
    return;
  }

  orderSaving.value = true;

  try {
    const payload: OrderPayload = {
      customer_id: orderForm.customer_id,
      status: orderForm.status,
      items: payloadItems.map((item) => ({
        product_id: Number(item.product_id),
        quantity: Number(item.quantity),
        price: Number(item.price),
      })),
      total: orderTotal.value,
    };

    await orderApi.create(payload);
    closeOrderModal();
    toastStore.success('Pedido criado com sucesso.');
    await loadMetrics();
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      toastStore.error(error.response?.data?.message || 'Erro ao salvar pedido.');
    } else {
      toastStore.error('Erro ao salvar pedido.');
    }
  } finally {
    orderSaving.value = false;
  }
};

watch(isAnyModalOpen, (opened) => {
  if (opened) {
    lockBodyScroll();
    return;
  }

  unlockBodyScroll();
});

onMounted(() => {
  window.addEventListener('keydown', handleEscapeKey);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEscapeKey);
  unlockBodyScroll();
});
</script>

<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="welcome-title">Bem-vindo de volta, <span>{{ welcomeName }}</span>!</h1>
      <p class="welcome-subtitle">Aqui está um resumo do SimplyFood para o dia de hoje.</p>
    </div>

    <div v-if="errorMessage" class="alert alert-error">
      <p>{{ errorMessage }}</p>
      <button class="retry-button" @click="loadMetrics">Tentar novamente</button>
    </div>

    <div class="metrics-grid" :aria-busy="loading">
      <div v-if="loading" v-for="idx in 4" :key="`skeleton-${idx}`" class="metric-card metric-card-skeleton">
        <div class="skeleton skeleton-dot"></div>
        <div class="metric-content">
          <div class="skeleton skeleton-line"></div>
          <div class="skeleton skeleton-value"></div>
        </div>
      </div>

      <div v-else v-for="metric in metrics" :key="metric.key" class="metric-card">
        <div class="metric-bullet" :style="{ backgroundColor: resolveMetricColor(metric.key) }"></div>
        <div class="metric-content">
          <p class="metric-title">{{ metric.title }}</p>
          <p class="metric-value">{{ metric.value }}</p>
          <p class="metric-description">{{ metric.description }}</p>
        </div>
      </div>
    </div>

    <div class="quick-actions-section">
      <h2 class="section-title">Ações Rápidas</h2>
      <div class="actions-grid">
        <button type="button" class="action-card" @click="openCustomerModal">
          <div class="action-details">
            <h3>Novo Cliente</h3>
            <p>Inicia o fluxo de cadastro de um novo cliente.</p>
          </div>
        </button>

        <button type="button" class="action-card" @click="openProductQuickCreateModal">
          <div class="action-details">
            <h3>Novo Produto</h3>
            <p>Inicia o cadastro rápido de um novo produto.</p>
          </div>
        </button>

        <button type="button" class="action-card" @click="openOrderModal">
          <div class="action-details">
            <h3>Novo Pedido</h3>
            <p>Inicia o fluxo de criacao de um novo pedido.</p>
          </div>
        </button>
      </div>
    </div>

    <div v-if="customerModalVisible" class="modal-overlay" @click.self="closeCustomerModal">
      <div ref="customerModalCardRef" class="modal-card" role="dialog" aria-modal="true" aria-label="Novo cliente" tabindex="-1">
        <div class="modal-head">
          <h3>Novo Cliente</h3>
        </div>

        <CustomerForm :key="customerFormKey" :loading="customerSaving" submit-label="Salvar" @submit="createCustomerFromDashboard">
          <template #footer-actions>
            <div class="customer-modal-actions-row">
              <button type="button" class="btn-muted" @click="closeCustomerModal" :disabled="customerSaving">Cancelar</button>
              <button type="submit" class="btn-primary" :disabled="customerSaving">
                {{ customerSaving ? 'Salvando...' : 'Salvar' }}
              </button>
            </div>
          </template>
        </CustomerForm>
      </div>
    </div>

    <div v-if="orderModalVisible" class="modal-overlay" @click.self="closeOrderModal">
      <div ref="orderModalCardRef" class="modal-card" role="dialog" aria-modal="true" aria-label="Novo pedido" tabindex="-1">
        <div class="modal-head">
          <h3>Novo Pedido</h3>
        </div>

        <div class="items-header">
          <h3>Itens do Pedido</h3>
          <div class="inline-actions">
            <button type="button" class="btn-inline" @click="openProductQuickCreateModalFromOrder">Cadastrar Produto Rapidamente</button>
            <button type="button" class="btn-inline" @click="addOrderItem">Adicionar Item</button>
          </div>
        </div>

        <div v-if="products.length === 0" class="empty-products">Nenhum produto ativo encontrado. Cadastre um produto para continuar.</div>

        <div class="items-list">
          <div v-for="(item, index) in orderForm.items" :key="index" class="item-row">
            <select v-model.number="item.product_id" class="modal-input" @change="onOrderProductChange(index)">
              <option :value="null">Produto...</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.nome }}
              </option>
            </select>

            <input v-model.number="item.quantity" class="modal-input" type="number" min="1" placeholder="Quantidade" />
            <input
              class="modal-input"
              type="text"
              inputmode="decimal"
              placeholder="Preco"
              :value="formatBrazilianCurrency(item.price)"
              @input="onItemPriceInput(index, $event)"
            />
            <button type="button" class="btn-danger" @click="removeOrderItem(index)">Remover</button>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-muted" @click="closeOrderModal" :disabled="orderSaving">Cancelar</button>
          <button type="button" class="btn-primary" @click="saveOrderFromDashboard" :disabled="orderSaving">
            {{ orderSaving ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>

    <ProductQuickCreateModal
      v-model="productQuickCreateModalVisible"
      :saving="creatingProduct"
      :categories="productQuickCreateOptions?.categories || []"
      :units="productQuickCreateOptions?.units || []"
      :defaults="productQuickCreateDefaults"
      :field-errors="productQuickCreateFieldErrors"
      :error-message="productQuickCreateError"
      @save="createProductFromQuickCreate"
    />
  </div>
</template>

<style scoped>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.dashboard-header {
  margin-bottom: 2.5rem;
}

.welcome-title {
  font-family: inherit;
  font-size: 2.2rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
}

.welcome-title span {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.welcome-subtitle {
  font-family: inherit;
  font-size: 0.95rem;
  color: #a0aec0;
  margin: 0;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  margin-bottom: 3rem;
}

@media (max-width: 1024px) {
  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .metrics-grid {
    grid-template-columns: 1fr;
  }
}

.metric-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  backdrop-filter: blur(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.metric-card-skeleton {
  pointer-events: none;
}

.metric-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 255, 255, 0.15);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.metric-bullet {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  margin-top: 0.3rem;
  flex-shrink: 0;
}

.skeleton {
  background: linear-gradient(90deg, rgba(148, 163, 184, 0.15) 25%, rgba(148, 163, 184, 0.35) 50%, rgba(148, 163, 184, 0.15) 75%);
  background-size: 200% 100%;
  animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.skeleton-dot {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  margin-top: 0.3rem;
}

.skeleton-line {
  width: 145px;
  height: 14px;
  border-radius: 999px;
  margin-bottom: 0.55rem;
}

.skeleton-value {
  width: 100px;
  height: 24px;
  border-radius: 999px;
}

.alert {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
}

.alert-error {
  background: rgba(229, 62, 62, 0.15);
  border: 1px solid rgba(229, 62, 62, 0.25);
  color: #fc8181;
}

.alert-success {
  background: rgba(72, 187, 120, 0.15);
  border: 1px solid rgba(72, 187, 120, 0.25);
  color: #68d391;
}

.single-line {
  justify-content: flex-start;
}

.retry-button {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  border-radius: 10px;
  padding: 0.45rem 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.retry-button:hover {
  border-color: rgba(255, 255, 255, 0.24);
  background: rgba(30, 41, 59, 0.7);
}

.metric-content {
  font-family: inherit;
}

.metric-title {
  font-size: 0.85rem;
  font-weight: 500;
  color: #a0aec0;
  margin: 0 0 0.5rem 0;
}

.metric-value {
  font-family: inherit;
  font-size: 1.6rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 0.35rem 0;
}

.metric-description {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0;
}

.quick-actions-section {
  font-family: inherit;
}

.section-title {
  font-family: inherit;
  font-size: 1.5rem;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 1.5rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

@media (max-width: 1024px) {
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .actions-grid {
    grid-template-columns: 1fr;
  }
}

.action-card {
  background: rgba(255, 75, 43, 0.03);
  border: 1px solid rgba(255, 75, 43, 0.1);
  border-radius: 20px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  text-decoration: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-family: inherit;
  appearance: none;
  color: inherit;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-card:hover {
  transform: translateY(-4px);
  background: rgba(255, 75, 43, 0.08);
  border-color: #ff4b2b;
  box-shadow: 0 10px 20px rgba(255, 75, 43, 0.15);
}

.action-details h3 {
  font-family: inherit;
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffffff;
  margin: 0 0 0.25rem 0;
}

.action-details p {
  font-size: 0.875rem;
  color: #a0aec0;
  margin: 0;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(4px);
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-card {
  width: min(980px, 100%);
  max-height: 92vh;
  overflow-y: auto;
  background: #111827;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 18px;
  padding: 1rem;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.85rem;
}

.modal-head h3 {
  margin: 0;
  color: #f8fafc;
}

.modal-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.modal-form-grid label {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  color: #cbd5e1;
  font-size: 0.85rem;
}

.modal-input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  padding: 0.6rem 0.7rem;
  box-sizing: border-box;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 0.9rem;
}

.customer-modal-actions-row {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  width: 100%;
}

.modal-actions.compact {
  margin-top: 0.6rem;
}

.btn-primary,
.btn-muted,
.btn-inline,
.btn-danger {
  border-radius: 10px;
  border: 1px solid transparent;
  padding: 0.5rem 0.8rem;
  cursor: pointer;
  font-size: 0.85rem;
}

.btn-primary {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: #fff;
}

.btn-muted,
.btn-inline {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
}

.btn-danger {
  background: rgba(229, 62, 62, 0.2);
  border-color: rgba(229, 62, 62, 0.35);
  color: #fecaca;
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

.items-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  margin-top: 0.75rem;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 0.5rem;
}

.empty-products {
  margin-top: 0.7rem;
  color: #f6ad55;
  font-size: 0.9rem;
}

@media (max-width: 900px) {
  .modal-form-grid,
  .item-row {
    grid-template-columns: 1fr;
  }

  .items-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
}
</style>
