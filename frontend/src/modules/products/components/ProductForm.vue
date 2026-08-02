<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import type {
  CreateProductPayload,
  ProductCategoryOption,
  ProductQuickCreateDefaults,
  ProductUnitOption,
} from '../api/ProductApi';
import ProductCategorySelect from './ProductCategorySelect.vue';
import ProductUnitSelect from './ProductUnitSelect.vue';
import ProductSwitchGroup from './ProductSwitchGroup.vue';
import ProductImageUpload from './ProductImageUpload.vue';

type ProductSwitchGroupState = {
  ativo: boolean;
  controla_estoque: boolean;
  produzido_cozinha: boolean;
  delivery: boolean;
  balcao: boolean;
  mesa: boolean;
  retirada: boolean;
};

type ProductFormState = {
  category_id: number;
  nome: string;
  preco_venda: number;
  unidade: string;
  descricao: string;
  codigo_barras: string;
  custo: number | null;
  imagem_file: File | null;
};

const props = defineProps<{
  categories: ProductCategoryOption[];
  units: ProductUnitOption[];
  defaults: ProductQuickCreateDefaults;
  fieldErrors?: Record<string, string>;
  errorMessage?: string;
  saving?: boolean;
}>();

const emit = defineEmits<{
  (event: 'save', payload: CreateProductPayload): void;
}>();

const form = reactive<ProductFormState>({
  category_id: 0,
  nome: '',
  preco_venda: 0,
  unidade: '',
  descricao: '',
  codigo_barras: '',
  custo: null,
  imagem_file: null,
});

const switches = ref<ProductSwitchGroupState>({
  ativo: true,
  controla_estoque: false,
  produzido_cozinha: true,
  delivery: true,
  balcao: true,
  mesa: true,
  retirada: true,
});

const categoryOptions = computed(() => props.categories || []);
const unitOptions = computed(() => props.units || []);

const formatCurrencyInput = (value: number): string => {
  return Number(value || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const parseCurrencyInput = (rawValue: string): number => {
  const digits = rawValue.replace(/\D/g, '');
  if (!digits) {
    return 0;
  }

  return Number(digits) / 100;
};

const priceInputValue = computed(() => formatCurrencyInput(form.preco_venda));
const costInputValue = computed(() => (form.custo === null ? '' : formatCurrencyInput(form.custo)));

const resetForm = () => {
  const defaults = props.defaults;

  form.category_id = categoryOptions.value[0]?.id || 0;
  form.nome = '';
  form.preco_venda = 0;
  form.unidade = defaults?.unidade || unitOptions.value[0]?.value || '';
  form.descricao = '';
  form.codigo_barras = '';
  form.custo = null;
  form.imagem_file = null;

  switches.value = {
    ativo: defaults?.ativo ?? true,
    controla_estoque: defaults?.controla_estoque ?? false,
    produzido_cozinha: defaults?.produzido_cozinha ?? true,
    delivery: defaults?.delivery ?? true,
    balcao: defaults?.balcao ?? true,
    mesa: defaults?.mesa ?? true,
    retirada: defaults?.retirada ?? true,
  };
};

const onPriceInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const parsed = parseCurrencyInput(target.value);
  form.preco_venda = parsed;
  target.value = formatCurrencyInput(parsed);
};

const onCostInput = (event: Event) => {
  const target = event.target as HTMLInputElement;

  if (!target.value.trim()) {
    form.custo = null;
    return;
  }

  const parsed = parseCurrencyInput(target.value);
  form.custo = parsed;
  target.value = formatCurrencyInput(parsed);
};

const saveProduct = () => {
  emit('save', {
    category_id: Number(form.category_id),
    nome: form.nome.trim(),
    descricao: form.descricao.trim() || undefined,
    preco_venda: Number(form.preco_venda),
    unidade: form.unidade,
    codigo_barras: form.codigo_barras.trim() || undefined,
    custo: form.custo === null ? undefined : Number(form.custo),
    ativo: switches.value.ativo,
    controla_estoque: switches.value.controla_estoque,
    produzido_cozinha: switches.value.produzido_cozinha,
    delivery: switches.value.delivery,
    balcao: switches.value.balcao,
    mesa: switches.value.mesa,
    retirada: switches.value.retirada,
    imagem_file: form.imagem_file || undefined,
  });
};

resetForm();
</script>

<template>
  <form class="space-y-5" @submit.prevent="saveProduct">
    <div v-if="errorMessage" class="rounded-lg border border-rose-500/40 bg-rose-950/40 px-3 py-2 text-sm text-rose-200">
      {{ errorMessage }}
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <label class="field">
        <span class="label-text">Nome do Produto *</span>
        <input v-model="form.nome" class="control" type="text" placeholder="Ex: X-Burger" />
        <span v-if="fieldErrors?.nome" class="error-text">{{ fieldErrors.nome }}</span>
      </label>

      <ProductCategorySelect v-model="form.category_id" :options="categoryOptions" :error-message="fieldErrors?.category_id" />

      <label class="field">
        <span class="label-text">Preço de Venda *</span>
        <input
          class="control"
          type="text"
          inputmode="decimal"
          placeholder="0,00"
          :value="priceInputValue"
          @input="onPriceInput"
        />
        <span v-if="fieldErrors?.preco_venda" class="error-text">{{ fieldErrors.preco_venda }}</span>
      </label>

      <ProductUnitSelect v-model="form.unidade" :options="unitOptions" :error-message="fieldErrors?.unidade" />

      <label class="field md:col-span-2">
        <span class="label-text">Descrição</span>
        <textarea v-model="form.descricao" class="control min-h-20" placeholder="Descrição opcional"></textarea>
        <span v-if="fieldErrors?.descricao" class="error-text">{{ fieldErrors.descricao }}</span>
      </label>

      <label class="field">
        <span class="label-text">Código de Barras</span>
        <input v-model="form.codigo_barras" class="control" type="text" placeholder="Opcional" />
        <span v-if="fieldErrors?.codigo_barras" class="error-text">{{ fieldErrors.codigo_barras }}</span>
      </label>

      <label class="field">
        <span class="label-text">Custo</span>
        <input
          class="control"
          type="text"
          inputmode="decimal"
          placeholder="0,00"
          :value="costInputValue"
          @input="onCostInput"
        />
        <span v-if="fieldErrors?.custo" class="error-text">{{ fieldErrors.custo }}</span>
      </label>

      <div class="md:col-span-2">
        <ProductSwitchGroup v-model="switches" />
      </div>

      <div class="md:col-span-2">
        <ProductImageUpload v-model="form.imagem_file" :error-message="fieldErrors?.imagem_file" />
      </div>
    </div>

    <footer class="flex justify-end gap-3">
      <button type="button" class="btn-secondary" :disabled="saving" @click="resetForm">Limpar</button>
      <button type="submit" class="btn-primary" :disabled="saving">
        {{ saving ? 'Salvando...' : 'Salvar Produto' }}
      </button>
    </footer>
  </form>
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

.btn-primary,
.btn-secondary {
  border-radius: 10px;
  border: 1px solid transparent;
  padding: 0.5rem 0.85rem;
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
</style>
