<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { CustomerApi, type CustomerDto, type CreateCustomerDto } from '../api/CustomerApi';
import CustomerForm from '../components/CustomerForm.vue';
import { CreateCustomerService } from '../services/CreateCustomerService';
import type { CustomerFormInput } from '../validators/customerSchema';
import axios from 'axios';
import { useToastStore } from '@/shared/stores/toast';

type EditableCustomer = {
  id: number;
  name: string;
  phone: string;
  whatsapp: string;
  email: string;
  cpf_cnpj: string;
  cep: string;
  address: string;
  number: string;
  neighborhood: string;
  city: string;
  state: string;
};

const customerApi = new CustomerApi();
const createCustomerService = new CreateCustomerService(customerApi);
const route = useRoute();
const router = useRouter();
const toastStore = useToastStore();

const loading = ref(false);
const saving = ref(false);
const deletingId = ref<number | null>(null);
const successMessage = ref('');
const errorMessage = ref('');
const customers = ref<CustomerDto[]>([]);
const editingCustomer = ref<EditableCustomer | null>(null);
const selectedCustomer = ref<CustomerDto | null>(null);
const createPanelVisible = ref(false);
const createPanelRef = ref<HTMLElement | null>(null);
const customerFormKey = ref(0);
const quickCreateMode = computed(() => route.query.action === 'create');
const highlightedCustomerId = computed(() => {
  const value = route.query.highlight;
  if (!value) {
    return null;
  }

  const parsed = Number(value);
  return Number.isNaN(parsed) ? null : parsed;
});

const filters = reactive({
  name: '',
  whatsapp: '',
  city: '',
});

const normalize = (value: string | null | undefined): string => (value || '').trim().toLowerCase();

const filteredCustomers = computed(() => {
  const nameFilter = normalize(filters.name);
  const whatsappFilter = normalize(filters.whatsapp);
  const cityFilter = normalize(filters.city);

  return customers.value.filter((customer) => {
    const matchesName = !nameFilter || normalize(customer.name).includes(nameFilter);
    const matchesWhatsapp = !whatsappFilter || normalize(customer.whatsapp).includes(whatsappFilter);
    const matchesCity = !cityFilter || normalize(customer.city).includes(cityFilter);

    return matchesName && matchesWhatsapp && matchesCity;
  });
});

const loadCustomers = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await customerApi.getAll();
    customers.value = response.data.data || [];
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao carregar clientes.';
    } else {
      errorMessage.value = 'Erro ao carregar clientes.';
    }
  } finally {
    loading.value = false;
  }
};

const mapFormToCreateDto = (form: CustomerFormInput): CreateCustomerDto => {
  return {
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
  };
};

const openCreatePanel = async () => {
  createPanelVisible.value = true;
  editingCustomer.value = null;
  errorMessage.value = '';
  successMessage.value = '';

  await nextTick();
  createPanelRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const closeCreatePanel = () => {
  if (quickCreateMode.value) {
    router.replace('/dashboard');
    return;
  }

  createPanelVisible.value = false;
  customerFormKey.value += 1;
};

const createCustomer = async (payload: CustomerFormInput) => {
  saving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const createdCustomer = await createCustomerService.execute(mapFormToCreateDto(payload));
    await loadCustomers();
    customerFormKey.value += 1;

    if (quickCreateMode.value) {
      router.replace({
        path: '/customers',
        query: { highlight: String(createdCustomer.id) },
      });
    } else {
      createPanelVisible.value = false;
    }

    selectedCustomer.value = customers.value.find((item) => item.id === createdCustomer.id) || null;
    successMessage.value = 'Cliente cadastrado com sucesso.';
    toastStore.success('Cliente cadastrado com sucesso.');
  } catch (error: unknown) {
    if (error instanceof Error) {
      errorMessage.value = error.message;
      toastStore.error(error.message);
    } else {
      errorMessage.value = 'Erro ao cadastrar cliente.';
      toastStore.error('Erro ao cadastrar cliente.');
    }
  } finally {
    saving.value = false;
  }
};

const startEdit = (customer: CustomerDto) => {
  editingCustomer.value = {
    id: customer.id,
    name: customer.name || '',
    phone: customer.phone || '',
    whatsapp: customer.whatsapp || '',
    email: customer.email || '',
    cpf_cnpj: customer.cpf_cnpj || '',
    cep: customer.cep || '',
    address: customer.address || '',
    number: customer.number || '',
    neighborhood: customer.neighborhood || '',
    city: customer.city || '',
    state: customer.state || '',
  };
  successMessage.value = '';
  errorMessage.value = '';
  selectedCustomer.value = customer;
};

const cancelEdit = () => {
  editingCustomer.value = null;
};

const saveEdit = async () => {
  if (!editingCustomer.value) {
    return;
  }

  if (!editingCustomer.value.name.trim() || !editingCustomer.value.phone.trim()) {
    errorMessage.value = 'Nome e telefone sao obrigatorios para atualizar o cliente.';
    return;
  }

  saving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const payload = {
      name: editingCustomer.value.name,
      phone: editingCustomer.value.phone,
      whatsapp: editingCustomer.value.whatsapp || undefined,
      email: editingCustomer.value.email || undefined,
      cpf_cnpj: editingCustomer.value.cpf_cnpj || undefined,
      cep: editingCustomer.value.cep || undefined,
      address: editingCustomer.value.address || undefined,
      number: editingCustomer.value.number || undefined,
      neighborhood: editingCustomer.value.neighborhood || undefined,
      city: editingCustomer.value.city || undefined,
      state: editingCustomer.value.state || undefined,
    };

    await customerApi.update(editingCustomer.value.id, payload);
    await loadCustomers();
    successMessage.value = 'Cliente atualizado com sucesso.';
    toastStore.success('Cliente atualizado com sucesso.');
    selectedCustomer.value = customers.value.find((item) => item.id === editingCustomer.value?.id) || selectedCustomer.value;
    editingCustomer.value = null;
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao atualizar cliente.';
      toastStore.error(error.response?.data?.message || 'Erro ao atualizar cliente.');
    } else {
      errorMessage.value = 'Erro ao atualizar cliente.';
      toastStore.error('Erro ao atualizar cliente.');
    }
  } finally {
    saving.value = false;
  }
};

const viewCustomer = (customer: CustomerDto) => {
  selectedCustomer.value = customer;
};

const removeCustomer = async (customer: CustomerDto) => {
  const confirmed = window.confirm(`Deseja excluir o cliente "${customer.name}"?`);
  if (!confirmed) {
    return;
  }

  deletingId.value = customer.id;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await customerApi.delete(customer.id);
    customers.value = customers.value.filter((item) => item.id !== customer.id);
    if (editingCustomer.value?.id === customer.id) {
      editingCustomer.value = null;
    }
    successMessage.value = 'Cliente excluido com sucesso.';
    toastStore.success('Cliente excluido com sucesso.');
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao excluir cliente.';
      toastStore.error(error.response?.data?.message || 'Erro ao excluir cliente.');
    } else {
      errorMessage.value = 'Erro ao excluir cliente.';
      toastStore.error('Erro ao excluir cliente.');
    }
  } finally {
    deletingId.value = null;
  }
};

onMounted(async () => {
  await loadCustomers();

  if (route.query.action === 'create') {
    await openCreatePanel();
  }

  if (highlightedCustomerId.value) {
    selectedCustomer.value = customers.value.find((item) => item.id === highlightedCustomerId.value) || null;
  }
});

watch(
  () => route.query.highlight,
  () => {
    if (!highlightedCustomerId.value) {
      return;
    }

    selectedCustomer.value = customers.value.find((item) => item.id === highlightedCustomerId.value) || null;
  }
);
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-titles">
        <h1 class="page-title">Clientes</h1>
        <p class="page-description">Gerencie clientes do seu ambiente com cadastro, filtros, visualizacao, edicao e exclusao.</p>
      </div>

      <div class="header-actions">
        <button v-if="quickCreateMode" class="btn btn-secondary" @click="closeCreatePanel">Voltar para gerenciamento</button>
        <button v-else class="btn btn-primary" @click="openCreatePanel">Novo Cliente</button>
      </div>
    </div>

    <div v-if="successMessage" class="alert alert-success">
      <span class="icon">✓</span>
      <p>{{ successMessage }}</p>
    </div>

    <div v-if="errorMessage" class="alert alert-error">
      <span class="icon">✗</span>
      <p>{{ errorMessage }}</p>
    </div>

    <section v-if="!quickCreateMode" class="filter-panel">
      <div class="filter-grid">
        <input v-model="filters.name" class="filter-input" placeholder="Filtrar por nome" />
        <input v-model="filters.whatsapp" class="filter-input" placeholder="Filtrar por WhatsApp" />
        <input v-model="filters.city" class="filter-input" placeholder="Filtrar por cidade" />
      </div>
    </section>

    <section v-if="quickCreateMode || createPanelVisible" ref="createPanelRef" class="edit-panel">
      <h2 class="section-title">Novo Cliente</h2>
      <CustomerForm :key="customerFormKey" :loading="saving" @submit="createCustomer" />

      <div class="action-row">
        <button class="btn btn-secondary" @click="closeCreatePanel" :disabled="saving">{{ quickCreateMode ? 'Cancelar e voltar' : 'Fechar' }}</button>
      </div>
    </section>

    <section v-if="!quickCreateMode && editingCustomer" class="edit-panel">
      <h2 class="section-title">Editar Cliente</h2>

      <div class="edit-grid">
        <input v-model="editingCustomer.name" class="filter-input" placeholder="Nome" />
        <input v-model="editingCustomer.phone" class="filter-input" placeholder="Telefone" />
        <input v-model="editingCustomer.whatsapp" class="filter-input" placeholder="WhatsApp" />
        <input v-model="editingCustomer.email" class="filter-input" placeholder="E-mail" />
        <input v-model="editingCustomer.cpf_cnpj" class="filter-input" placeholder="CPF/CNPJ" />
        <input v-model="editingCustomer.cep" class="filter-input" placeholder="CEP" />
        <input v-model="editingCustomer.address" class="filter-input" placeholder="Endereco" />
        <input v-model="editingCustomer.number" class="filter-input" placeholder="Numero" />
        <input v-model="editingCustomer.neighborhood" class="filter-input" placeholder="Bairro" />
        <input v-model="editingCustomer.city" class="filter-input" placeholder="Cidade" />
        <input v-model="editingCustomer.state" class="filter-input" placeholder="UF" />
      </div>

      <div class="action-row">
        <button class="btn btn-secondary" @click="cancelEdit" :disabled="saving">Cancelar</button>
        <button class="btn btn-primary" @click="saveEdit" :disabled="saving">{{ saving ? 'Salvando...' : 'Salvar alteracoes' }}</button>
      </div>
    </section>

    <section v-if="!quickCreateMode" class="list-panel">
      <div v-if="loading" class="empty-state">Carregando clientes...</div>

      <div v-else-if="filteredCustomers.length === 0" class="empty-state">
        Nenhum cliente encontrado para os filtros aplicados.
      </div>

      <div v-else class="list-grid">
        <article
          v-for="customer in filteredCustomers"
          :key="customer.id"
          class="customer-card"
          :class="{ highlighted: highlightedCustomerId === customer.id }"
        >
          <div class="card-head">
            <h3>{{ customer.name }}</h3>
            <span class="customer-id">#{{ customer.id }}</span>
          </div>

          <p><strong>Telefone:</strong> {{ customer.formatted_phone || customer.phone || '-' }}</p>
          <p><strong>WhatsApp:</strong> {{ customer.whatsapp || '-' }}</p>
          <p><strong>E-mail:</strong> {{ customer.email || '-' }}</p>
          <p><strong>Cidade:</strong> {{ customer.city || '-' }}</p>

          <div class="action-row">
            <button class="btn btn-secondary" @click="viewCustomer(customer)">Visualizar</button>
            <button class="btn btn-secondary" @click="startEdit(customer)">Editar</button>
            <button class="btn btn-danger" @click="removeCustomer(customer)" :disabled="deletingId === customer.id">
              {{ deletingId === customer.id ? 'Excluindo...' : 'Excluir' }}
            </button>
          </div>
        </article>
      </div>
    </section>

    <section v-if="!quickCreateMode && selectedCustomer" class="edit-panel">
      <h2 class="section-title">Visualizacao do Cliente</h2>

      <div class="detail-grid">
        <p><strong>ID:</strong> {{ selectedCustomer.id }}</p>
        <p><strong>Nome:</strong> {{ selectedCustomer.name || '-' }}</p>
        <p><strong>Telefone:</strong> {{ selectedCustomer.formatted_phone || selectedCustomer.phone || '-' }}</p>
        <p><strong>WhatsApp:</strong> {{ selectedCustomer.whatsapp || '-' }}</p>
        <p><strong>E-mail:</strong> {{ selectedCustomer.email || '-' }}</p>
        <p><strong>CPF/CNPJ:</strong> {{ selectedCustomer.cpf_cnpj || '-' }}</p>
        <p><strong>CEP:</strong> {{ selectedCustomer.cep || '-' }}</p>
        <p><strong>Endereco:</strong> {{ selectedCustomer.address || '-' }}</p>
        <p><strong>Numero:</strong> {{ selectedCustomer.number || '-' }}</p>
        <p><strong>Bairro:</strong> {{ selectedCustomer.neighborhood || '-' }}</p>
        <p><strong>Cidade:</strong> {{ selectedCustomer.city || '-' }}</p>
        <p><strong>UF:</strong> {{ selectedCustomer.state || '-' }}</p>
      </div>
    </section>
  </div>
</template>

<style scoped>
.page-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 1.5rem 0;
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

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 2rem;
}

.header-actions {
  display: flex;
  align-items: center;
}

.page-title {
  font-family: inherit;
  font-size: 2rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.page-description {
  font-family: inherit;
  font-size: 0.95rem;
  color: #a0aec0;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-family: inherit;
  font-size: 0.95rem;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.alert-success {
  background: rgba(72, 187, 120, 0.15);
  border: 1px solid rgba(72, 187, 120, 0.25);
  color: #68d391;
}

.alert-error {
  background: rgba(229, 62, 62, 0.15);
  border: 1px solid rgba(229, 62, 62, 0.25);
  color: #fc8181;
}

.icon {
  font-size: 1.1rem;
}

.filter-panel,
.edit-panel,
.list-panel {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.filter-grid,
.edit-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.35rem 1rem;
}

.detail-grid p {
  margin: 0;
  color: #cbd5e1;
  font-size: 0.9rem;
}

.edit-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.filter-input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  border-radius: 10px;
  padding: 0.65rem 0.75rem;
  box-sizing: border-box;
}

.filter-input::placeholder {
  color: #94a3b8;
}

.section-title {
  margin: 0 0 0.9rem 0;
  font-size: 1rem;
  color: #e2e8f0;
}

.list-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.customer-card {
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 0.9rem;
  background: rgba(15, 23, 42, 0.55);
}

.customer-card.highlighted {
  border-color: rgba(56, 161, 105, 0.65);
  box-shadow: 0 0 0 1px rgba(56, 161, 105, 0.35), 0 10px 22px rgba(0, 0, 0, 0.2);
}

.customer-card p {
  margin: 0.35rem 0;
  color: #cbd5e1;
  font-size: 0.9rem;
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.6rem;
}

.card-head h3 {
  margin: 0;
  color: #f8fafc;
  font-size: 1rem;
}

.customer-id {
  font-size: 0.75rem;
  color: #94a3b8;
}

.action-row {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 0.8rem;
}

.btn {
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 0.5rem 0.8rem;
  cursor: pointer;
  font-size: 0.85rem;
}

.btn-primary {
  background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
  color: #fff;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
}

.btn-danger {
  background: rgba(229, 62, 62, 0.2);
  border-color: rgba(229, 62, 62, 0.35);
  color: #fecaca;
}

.empty-state {
  color: #94a3b8;
  font-size: 0.9rem;
}

@media (max-width: 900px) {
  .page-header {
    flex-direction: column;
  }

  .filter-grid,
  .edit-grid,
  .list-grid,
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
