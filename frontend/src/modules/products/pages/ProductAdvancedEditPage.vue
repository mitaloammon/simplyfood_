<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ProductApi, type ProductDto } from '../api/ProductApi';
import ProductAdvancedTabs, { type ProductAdvancedTab, type ProductAdvancedTabKey } from '../components/ProductAdvancedTabs.vue';
import axios from 'axios';
import { useToastStore } from '@/shared/stores/toast';

const route = useRoute();
const productApi = new ProductApi();
const toastStore = useToastStore();

const loading = ref(false);
const errorMessage = ref('');
const product = ref<ProductDto | null>(null);
const activeTab = ref<ProductAdvancedTabKey>('informacoes');

const tabs: ProductAdvancedTab[] = [
  { key: 'informacoes', label: 'Informacoes' },
  { key: 'estoque', label: 'Estoque' },
  { key: 'fiscal', label: 'Fiscal' },
  { key: 'fornecedores', label: 'Fornecedores' },
  { key: 'producao', label: 'Producao' },
  { key: 'ficha_tecnica', label: 'Ficha Tecnica' },
  { key: 'combos', label: 'Combos' },
  { key: 'adicionais', label: 'Adicionais' },
  { key: 'delivery', label: 'Delivery' },
  { key: 'historico', label: 'Historico' },
  { key: 'movimentacoes', label: 'Movimentacoes' },
];

const productId = computed(() => Number(route.params.id));

const activeTabTitle = computed(() => {
  return tabs.find((tab) => tab.key === activeTab.value)?.label || 'Informacoes';
});

const loadProduct = async () => {
  if (!productId.value) {
    errorMessage.value = 'Produto invalido para edicao.';
    toastStore.error('Produto invalido para edicao.');
    return;
  }

  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await productApi.getById(productId.value);
    product.value = response.data.data;
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      errorMessage.value = error.response?.data?.message || 'Erro ao carregar produto para edicao.';
      toastStore.error(error.response?.data?.message || 'Erro ao carregar produto para edicao.');
    } else {
      errorMessage.value = 'Erro ao carregar produto para edicao.';
      toastStore.error('Erro ao carregar produto para edicao.');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await loadProduct();
});
</script>

<template>
  <section class="mx-auto w-full max-w-6xl space-y-4">
    <header class="rounded-2xl border border-slate-700 bg-slate-900 p-4">
      <h1 class="text-2xl font-semibold text-slate-100">Cadastro Avancado de Produto</h1>
      <p class="mt-1 text-sm text-slate-300">Arquitetura inicial de abas preparada para evolucao incremental sem alterar regras existentes.</p>

      <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-slate-300 md:grid-cols-3">
        <p><strong>Produto:</strong> {{ product?.nome || '-' }}</p>
        <p><strong>ID:</strong> {{ product?.id || '-' }}</p>
        <p><strong>Categoria:</strong> {{ product?.category?.name || '-' }}</p>
      </div>
    </header>

    <div v-if="errorMessage" class="rounded-lg border border-rose-500/40 bg-rose-950/40 px-4 py-3 text-sm text-rose-200">
      {{ errorMessage }}
    </div>

    <ProductAdvancedTabs v-model:active-tab="activeTab" :tabs="tabs" :disabled="loading" />

    <section class="rounded-2xl border border-slate-700 bg-slate-900 p-5">
      <h2 class="text-lg font-semibold text-slate-100">{{ activeTabTitle }}</h2>
      <p class="mt-2 text-sm text-slate-300">
        Estrutura arquitetural pronta para implementacao futura desta aba.
      </p>

      <div class="mt-4 rounded-xl border border-dashed border-slate-600 p-4 text-sm text-slate-400">
        Sem regras de negocio novas nesta etapa.
      </div>
    </section>
  </section>
</template>
