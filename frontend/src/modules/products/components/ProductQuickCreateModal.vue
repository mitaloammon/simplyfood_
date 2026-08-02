<script setup lang="ts">
import type {
  CreateProductPayload,
  ProductCategoryOption,
  ProductQuickCreateDefaults,
  ProductUnitOption,
} from '../api/ProductApi';
import ProductForm from './ProductForm.vue';

const props = defineProps<{
  modelValue: boolean;
  saving?: boolean;
  categories: ProductCategoryOption[];
  units: ProductUnitOption[];
  defaults: ProductQuickCreateDefaults;
  fieldErrors?: Record<string, string>;
  errorMessage?: string;
}>();

const emit = defineEmits<{
  (event: 'update:modelValue', value: boolean): void;
  (event: 'save', payload: CreateProductPayload): void;
}>();

const closeModal = () => {
  emit('update:modelValue', false);
};
</script>

<template>
  <div v-if="modelValue" class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" @click.self="closeModal">
    <section
      tabindex="-1"
      role="dialog"
      aria-modal="true"
      aria-label="Novo Produto"
      class="w-full max-w-5xl rounded-2xl border border-slate-700 bg-slate-900 p-5 shadow-2xl"
    >
      <header class="mb-5 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-slate-100">Novo Produto</h3>
      </header>

      <ProductForm
        :categories="categories"
        :units="units"
        :defaults="defaults"
        :field-errors="fieldErrors"
        :error-message="errorMessage"
        :saving="saving"
        @save="emit('save', $event)"
      />
    </section>
  </div>
</template>
