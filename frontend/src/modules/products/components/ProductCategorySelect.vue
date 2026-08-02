<script setup lang="ts">
import type { ProductCategoryOption } from '../api/ProductApi';

const props = defineProps<{
  modelValue: number;
  options: ProductCategoryOption[];
  errorMessage?: string;
}>();

const emit = defineEmits<{
  (event: 'update:modelValue', value: number): void;
}>();
</script>

<template>
  <label class="field">
    <span class="label-text">Categoria *</span>
    <select
      class="control"
      :value="modelValue"
      @change="emit('update:modelValue', Number(($event.target as HTMLSelectElement).value))"
    >
      <option :value="0">Selecione...</option>
      <option v-for="category in options" :key="category.id" :value="category.id">{{ category.name }}</option>
    </select>
    <span v-if="errorMessage" class="error-text">{{ errorMessage }}</span>
  </label>
</template>

<style scoped>
.field {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  color: #cbd5e1;
  font-size: 0.85rem;
}

.label-text {
  font-weight: 500;
}

.control {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  padding: 0.65rem 0.75rem;
  box-sizing: border-box;
}

.error-text {
  color: #fda4af;
  font-size: 0.75rem;
}
</style>
