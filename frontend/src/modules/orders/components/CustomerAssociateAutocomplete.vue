<script setup lang="ts">
import { computed, ref } from 'vue';
import type { CustomerDto } from '@/modules/customers/api/CustomerApi';

const props = defineProps<{
  customers: CustomerDto[];
  loading?: boolean;
}>();

const emit = defineEmits<{
  (event: 'associate', customerId: number): void;
}>();

const query = ref('');

const filteredCustomers = computed(() => {
  const normalized = query.value.trim().toLowerCase();
  if (!normalized) {
    return props.customers.slice(0, 10);
  }

  return props.customers
    .filter((customer) => (customer.name || '').toLowerCase().includes(normalized))
    .slice(0, 10);
});
</script>

<template>
  <div class="space-y-2">
    <input v-model="query" class="input" placeholder="Digite nome do cliente para associar" />

    <div class="max-h-40 overflow-auto rounded border border-slate-700">
      <p v-if="customers.length === 0" class="empty">Nenhum cliente disponivel para associacao.</p>

      <button
        v-for="customer in filteredCustomers"
        :key="customer.id"
        class="customer-option"
        type="button"
        :disabled="loading"
        @click="emit('associate', customer.id)"
      >
        <span>{{ customer.name }}</span>
        <small>#{{ customer.id }}</small>
      </button>

      <p v-if="customers.length > 0 && filteredCustomers.length === 0" class="empty">Nenhum cliente encontrado para o termo informado.</p>
    </div>
  </div>
</template>

<style scoped>
.input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  padding: 0.6rem 0.7rem;
  box-sizing: border-box;
}

.customer-option {
  width: 100%;
  display: flex;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.55rem 0.7rem;
  border: none;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  background: transparent;
  color: #e2e8f0;
  text-align: left;
  cursor: pointer;
}

.customer-option:last-child {
  border-bottom: 0;
}

.customer-option:hover {
  background: rgba(148, 163, 184, 0.12);
}

.empty {
  color: #94a3b8;
  padding: 0.55rem 0.7rem;
}
</style>
