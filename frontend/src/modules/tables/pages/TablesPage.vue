<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { TableApi, type RestaurantTableDto } from '../api/TableApi';

const api = new TableApi();
const loading = ref(false);
const tables = ref<RestaurantTableDto[]>([]);

const loadTables = async () => {
  loading.value = true;
  try {
    const response = await api.getAll();
    tables.value = response.data.data || [];
  } finally {
    loading.value = false;
  }
};

onMounted(loadTables);
</script>

<template>
  <section class="space-y-4">
    <header>
      <h1 class="text-2xl font-semibold text-slate-100">Mesas</h1>
      <p class="text-sm text-slate-300">Gestao de atendimento presencial por status operacional.</p>
    </header>

    <div class="rounded-xl border border-slate-700 bg-slate-900 p-4" :aria-busy="loading">
      <p class="text-sm text-slate-300">Total de mesas:</p>
      <p class="text-lg font-semibold text-slate-100">{{ tables.length }}</p>
    </div>
  </section>
</template>
