<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { CashRegisterApi, type CashRegisterDto } from '../api/CashRegisterApi';

const api = new CashRegisterApi();
const loading = ref(false);
const errorMessage = ref('');
const current = ref<CashRegisterDto | null>(null);

const loadCurrent = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const response = await api.current();
    current.value = response.data.data;
  } catch {
    errorMessage.value = 'Nao foi possivel carregar o caixa atual.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadCurrent);
</script>

<template>
  <section class="space-y-4">
    <header>
      <h1 class="text-2xl font-semibold text-slate-100">Caixa</h1>
      <p class="text-sm text-slate-300">Modulo financeiro desacoplado para operacoes de abertura, movimentacao e fechamento.</p>
    </header>

    <div v-if="errorMessage" class="rounded-lg border border-rose-500/40 bg-rose-950/40 px-4 py-3 text-sm text-rose-200">
      {{ errorMessage }}
    </div>

    <article class="rounded-xl border border-slate-700 bg-slate-900 p-4" :aria-busy="loading">
      <p class="text-sm text-slate-300">Status atual:</p>
      <p class="text-lg font-semibold text-slate-100">{{ current?.status || 'SEM CAIXA ABERTO' }}</p>
      <p class="text-sm text-slate-300">Saldo: R$ {{ Number(current?.current_balance || 0).toFixed(2) }}</p>
    </article>
  </section>
</template>
