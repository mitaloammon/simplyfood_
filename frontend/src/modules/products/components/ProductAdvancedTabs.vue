<script setup lang="ts">
import { computed } from 'vue';

export type ProductAdvancedTabKey =
  | 'informacoes'
  | 'estoque'
  | 'fiscal'
  | 'fornecedores'
  | 'producao'
  | 'ficha_tecnica'
  | 'combos'
  | 'adicionais'
  | 'delivery'
  | 'historico'
  | 'movimentacoes';

export type ProductAdvancedTab = {
  key: ProductAdvancedTabKey;
  label: string;
};

const props = defineProps<{
  tabs: ProductAdvancedTab[];
  activeTab: ProductAdvancedTabKey;
  disabled?: boolean;
}>();

const emit = defineEmits<{
  (event: 'update:activeTab', value: ProductAdvancedTabKey): void;
}>();

const canNavigate = computed(() => !props.disabled);

const selectTab = (tabKey: ProductAdvancedTabKey) => {
  if (!canNavigate.value) {
    return;
  }

  emit('update:activeTab', tabKey);
};
</script>

<template>
  <nav class="rounded-xl border border-slate-700 bg-slate-900 p-2" aria-label="Abas do cadastro avancado de produto">
    <ul class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
      <li v-for="tab in tabs" :key="tab.key">
        <button
          type="button"
          class="w-full rounded-lg px-3 py-2 text-left text-sm transition"
          :class="
            tab.key === activeTab
              ? 'bg-rose-500 text-white shadow'
              : 'bg-slate-800 text-slate-200 hover:bg-slate-700'
          "
          :disabled="disabled"
          @click="selectTab(tab.key)"
        >
          {{ tab.label }}
        </button>
      </li>
    </ul>
  </nav>
</template>
