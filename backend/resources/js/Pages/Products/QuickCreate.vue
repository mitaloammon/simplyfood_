<script setup>
defineProps({
  quickCreateOptions: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <section class="p-6">
    <h1 class="text-2xl font-semibold">Cadastro Rápido de Produtos</h1>
    <p class="mt-2 text-sm text-slate-600">Estrutura Inertia preparada para evolução do fluxo corporativo.</p>

    <pre class="mt-4 overflow-auto rounded bg-slate-100 p-4 text-xs">{{ quickCreateOptions }}</pre>
  </section>
</template>
