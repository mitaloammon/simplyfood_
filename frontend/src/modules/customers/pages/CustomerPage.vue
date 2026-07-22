<script setup lang="ts">
import { ref } from 'vue';
import { CustomerApi } from '../api/CustomerApi';
import { CreateCustomerService } from '../services/CreateCustomerService';
import CustomerForm from '../components/CustomerForm.vue';

const customerApi = new CustomerApi();
const createCustomerService = new CreateCustomerService(customerApi);

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const handleCreateCustomer = async (formData: any) => {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  try {
    const customer = await createCustomerService.execute({
      name: formData.name,
      email: formData.email,
      whatsapp: formData.whatsapp,
      cpf_cnpj: formData.cpfCnpj,
      cep: formData.cep || undefined,
      address: formData.address || undefined,
      number: formData.number || undefined,
      complement: formData.complement || undefined,
      neighborhood: formData.neighborhood || undefined,
      city: formData.city || undefined,
      state: formData.state || undefined,
    });

    successMessage.value = `Cliente "${customer.name}" cadastrado com sucesso!`;
  } catch (err: any) {
    errorMessage.value = err.message || 'Erro ao cadastrar cliente. Verifique os dados.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-titles">
        <h1 class="page-title">Cadastrar Novo Cliente</h1>
        <p class="page-description">Insira os dados do cliente e as informações de entrega.</p>
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

    <CustomerForm :loading="loading" @submit="handleCreateCustomer" />
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
  margin-bottom: 2rem;
}

.page-title {
  font-family: 'Outfit', sans-serif;
  font-size: 2rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.page-description {
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  color: #a0aec0;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  font-family: 'Inter', sans-serif;
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
</style>
