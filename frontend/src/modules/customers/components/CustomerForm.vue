<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import { customerSchema, type CustomerFormInput } from '../validators/customerSchema';
import BaseForm from '@/shared/components/BaseForm.vue';
import BaseInput from '@/shared/components/BaseInput.vue';
import BaseButton from '@/shared/components/BaseButton.vue';

const props = defineProps<{
  loading?: boolean;
}>();

const emit = defineEmits(['submit']);

const form = ref<CustomerFormInput>({
  name: '',
  email: '',
  whatsapp: '55',
  cpfCnpj: '',
  cep: '',
  address: '',
  number: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
});

const errors = ref<Record<string, string>>({});

watch(
  () => form.value.cep,
  async (newCep) => {
    if (!newCep) return;
    const cleanCep = newCep.replace(/\D/g, '');
    if (cleanCep.length === 8) {
      try {
        const response = await axios.get(`https://viacep.com.br/ws/${cleanCep}/json/`);
        if (response.data && !response.data.erro) {
          form.value.address = response.data.logradouro || '';
          form.value.neighborhood = response.data.bairro || '';
          form.value.city = response.data.localidade || '';
          form.value.state = response.data.uf || '';
          errors.value.cep = '';
        }
      } catch (err) {
        console.error('ViaCEP fetch error:', err);
      }
    }
  }
);

const onSubmit = () => {
  errors.value = {};
  const result = customerSchema.safeParse(form.value);
  
  if (!result.success) {
    for (const issue of result.error.issues) {
      const path = issue.path[0] as string;
      errors.value[path] = issue.message;
    }
    return;
  }

  emit('submit', form.value);
};
</script>

<template>
  <div class="form-container">
    <BaseForm @submit="onSubmit">
      <div class="form-grid">
        <BaseInput
          v-model="form.name"
          label="Nome Completo"
          placeholder="ex: João Silva"
          :error="errors.name"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.email"
          label="E-mail"
          type="email"
          placeholder="ex: joao@simplyfood.com"
          :error="errors.email"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.whatsapp"
          label="WhatsApp"
          placeholder="ex: 5511999999999"
          :error="errors.whatsapp"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.cpfCnpj"
          label="CPF ou CNPJ"
          placeholder="somente números"
          :error="errors.cpfCnpj"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.cep"
          label="CEP"
          placeholder="ex: 01001000"
          :error="errors.cep"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.address"
          label="Endereço (Rua/Av)"
          placeholder="Rua das Flores"
          :error="errors.address"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.number"
          label="Número"
          placeholder="123"
          :error="errors.number"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.complement"
          label="Complemento"
          placeholder="Apto 42"
          :error="errors.complement"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.neighborhood"
          label="Bairro"
          placeholder="Centro"
          :error="errors.neighborhood"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.city"
          label="Cidade"
          placeholder="São Paulo"
          :error="errors.city"
          :disabled="loading"
        />

        <BaseInput
          v-model="form.state"
          label="Estado (UF)"
          placeholder="SP"
          :error="errors.state"
          :disabled="loading"
        />
      </div>

      <div class="form-footer">
        <BaseButton type="submit" :loading="loading" variant="primary">
          Salvar Cliente
        </BaseButton>
      </div>
    </BaseForm>
  </div>
</template>

<style scoped>
.form-container {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 2rem;
  backdrop-filter: blur(10px);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
</style>
