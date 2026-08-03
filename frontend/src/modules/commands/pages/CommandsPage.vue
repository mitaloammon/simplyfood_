<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { CommandApi, type CommandDto } from '../api/CommandApi';

const api = new CommandApi();
const loading = ref(false);
const commands = ref<CommandDto[]>([]);

const loadCommands = async () => {
  loading.value = true;
  try {
    const response = await api.getAll();
    commands.value = response.data.data || [];
  } finally {
    loading.value = false;
  }
};

onMounted(loadCommands);
</script>

<template>
  <section class="space-y-4">
    <header>
      <h1 class="text-2xl font-semibold text-slate-100">Comandas</h1>
      <p class="text-sm text-slate-300">Separacao do atendimento de mesa em fluxo dedicado de comanda.</p>
    </header>

    <div class="rounded-xl border border-slate-700 bg-slate-900 p-4" :aria-busy="loading">
      <p class="text-sm text-slate-300">Comandas ativas no contexto:</p>
      <p class="text-lg font-semibold text-slate-100">{{ commands.length }}</p>
    </div>
  </section>
</template>
