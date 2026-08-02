<script setup lang="ts">
import { reactive, watch } from 'vue';
import type { OrderFiltersState } from '../types/OrderManagement';

const props = defineProps<{
  modelValue: OrderFiltersState;
  statusOptions: string[];
  orderTypeOptions: string[];
}>();

const emit = defineEmits<{
  (event: 'update:modelValue', value: OrderFiltersState): void;
  (event: 'apply'): void;
  (event: 'reset'): void;
}>();

const localFilters = reactive<OrderFiltersState>({ ...props.modelValue });

watch(
  () => props.modelValue,
  (value) => {
    Object.assign(localFilters, value);
  }
);

watch(
  () => ({ ...localFilters }),
  (value) => {
    emit('update:modelValue', { ...value });
  },
  { deep: true }
);
</script>

<template>
  <section class="rounded-2xl border border-slate-700 bg-slate-900 p-4">
    <div class="mb-3 text-sm text-slate-300">
      Use os filtros para localizar pedidos por cliente, operador, status, tipo e faixa de valor.
    </div>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
      <input v-model="localFilters.order_number" class="input" placeholder="Numero do pedido" />
      <input v-model="localFilters.customer" class="input" placeholder="Nome do cliente" />
      <input v-model="localFilters.operator" class="input" placeholder="Nome do operador" />
      <select v-model="localFilters.status" class="input">
        <option value="">Todos os status</option>
        <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
      </select>
      <select v-model="localFilters.order_type" class="input">
        <option value="">Todos os tipos</option>
        <option v-for="orderType in orderTypeOptions" :key="orderType" :value="orderType">{{ orderType }}</option>
      </select>
      <input v-model="localFilters.date" class="input" type="date" />
      <input v-model="localFilters.value_min" class="input" type="number" min="0" step="0.01" placeholder="Valor minimo (R$)" />
      <input v-model="localFilters.value_max" class="input" type="number" min="0" step="0.01" placeholder="Valor maximo (R$)" />
    </div>

    <div class="mt-3 flex justify-end gap-2">
      <button class="btn secondary" type="button" @click="emit('reset')">Limpar filtros</button>
      <button class="btn primary" type="button" @click="emit('apply')">Buscar pedidos</button>
    </div>
  </section>
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
</style>
