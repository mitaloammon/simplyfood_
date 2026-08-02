<script setup lang="ts">
import { computed } from 'vue';
import type { ProductQuickCreateDefaults } from '../api/ProductApi';

type ProductSwitchGroupState = Pick<
  ProductQuickCreateDefaults,
  'ativo' | 'controla_estoque' | 'produzido_cozinha' | 'delivery' | 'balcao' | 'mesa' | 'retirada'
>;

const props = defineProps<{
  modelValue: ProductSwitchGroupState;
}>();

const emit = defineEmits<{
  (event: 'update:modelValue', value: ProductSwitchGroupState): void;
}>();

const switchItems = computed(() => [
  { key: 'ativo', label: 'Produto ativo' },
  { key: 'controla_estoque', label: 'Controla estoque' },
  { key: 'produzido_cozinha', label: 'Produzido na cozinha' },
  { key: 'delivery', label: 'Disponível para Delivery' },
  { key: 'balcao', label: 'Disponível para Balcão' },
  { key: 'mesa', label: 'Disponível para Mesa' },
  { key: 'retirada', label: 'Disponível para Retirada' },
] as const);

const updateSwitch = (key: keyof ProductSwitchGroupState, checked: boolean) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: checked,
  });
};
</script>

<template>
  <div class="switch-grid">
    <label v-for="item in switchItems" :key="item.key" class="switch-item">
      <input
        :checked="modelValue[item.key]"
        type="checkbox"
        @change="updateSwitch(item.key, ($event.target as HTMLInputElement).checked)"
      />
      <span>{{ item.label }}</span>
    </label>
  </div>
</template>

<style scoped>
.switch-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 0.75rem;
}

@media (min-width: 640px) {
  .switch-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1280px) {
  .switch-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.switch-item {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  color: #e2e8f0;
  font-size: 0.85rem;
}
</style>
