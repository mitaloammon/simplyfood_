<script setup lang="ts">
import type { OrderManagementMeta, OrderManagementRow } from '../api/OrderApi';

const props = defineProps<{
  loading: boolean;
  rows: OrderManagementRow[];
  meta: OrderManagementMeta;
}>();

const emit = defineEmits<{
  (event: 'select', orderId: number): void;
  (event: 'pageChange', page: number): void;
}>();

const toCurrency = (value: number): string => {
  return Number(value || 0).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
};
</script>

<template>
  <section class="rounded-2xl border border-slate-700 bg-slate-900 p-4">
    <div v-if="loading" class="empty">Carregando pedidos de gerenciamento...</div>

    <div v-else-if="rows.length === 0" class="empty">Nenhum pedido encontrado. Ajuste os filtros e tente novamente.</div>

    <div v-else class="overflow-auto">
      <div class="mb-2 text-sm text-slate-300">
        {{ meta.total }} pedido(s) encontrado(s) nesta consulta.
      </div>
      <table class="orders-table">
        <thead>
          <tr>
            <th>Pedido</th>
            <th>Cliente</th>
            <th>Operador</th>
            <th>Tipo</th>
            <th>Itens</th>
            <th>Total</th>
            <th>Status</th>
            <th>Criado em</th>
            <th>Atualizado em</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td>#{{ row.order_number }}</td>
            <td>{{ row.customer?.name || 'Cliente nao associado' }}</td>
            <td>{{ row.operator?.name || '-' }}</td>
            <td>{{ row.order_type }}</td>
            <td>{{ row.items_count }}</td>
            <td>{{ toCurrency(row.total) }}</td>
            <td>{{ row.status }}</td>
            <td>{{ new Date(row.created_at).toLocaleString('pt-BR') }}</td>
            <td>{{ new Date(row.updated_at).toLocaleString('pt-BR') }}</td>
            <td>
              <button class="btn secondary" type="button" @click="emit('select', row.id)">Detalhar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-3 flex items-center justify-end gap-2" v-if="meta.last_page > 1">
      <button class="btn secondary" type="button" :disabled="meta.current_page <= 1" @click="emit('pageChange', meta.current_page - 1)">
        Anterior
      </button>
      <span class="text-sm text-slate-300">Pagina {{ meta.current_page }} de {{ meta.last_page }}</span>
      <button class="btn secondary" type="button" :disabled="meta.current_page >= meta.last_page" @click="emit('pageChange', meta.current_page + 1)">
        Proxima
      </button>
    </div>
  </section>
</template>

<style scoped>
.orders-table {
  width: 100%;
  border-collapse: collapse;
  color: #e2e8f0;
  font-size: 0.85rem;
}

.orders-table th,
.orders-table td {
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  padding: 0.6rem;
  text-align: left;
  white-space: nowrap;
}

.empty {
  color: #94a3b8;
}

.btn {
  border-radius: 10px;
  border: 1px solid transparent;
  padding: 0.4rem 0.75rem;
  cursor: pointer;
  font-size: 0.82rem;
}

.btn.secondary {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
}
</style>
