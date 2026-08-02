<script setup lang="ts">
import type { CustomerDto } from '@/modules/customers/api/CustomerApi';
import type { OrderDto, OrderTimelineEntry } from '../api/OrderApi';
import CustomerAssociateAutocomplete from './CustomerAssociateAutocomplete.vue';

const props = defineProps<{
  open: boolean;
  loading: boolean;
  order: OrderDto | null;
  timeline: OrderTimelineEntry[];
  customers: CustomerDto[];
  actionLoading?: boolean;
  statusOptions: string[];
}>();

const emit = defineEmits<{
  (event: 'close'): void;
  (event: 'associateCustomer', customerId: number): void;
  (event: 'changeStatus', status: string): void;
}>();

const toCurrency = (value: number): string => {
  return Number(value || 0).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
};
</script>

<template>
  <div v-if="open" class="drawer-overlay" @click.self="emit('close')">
    <aside class="drawer-panel">
      <header class="drawer-header">
        <h2>Pedido #{{ order?.id }}</h2>
        <button class="btn secondary" type="button" @click="emit('close')">Fechar</button>
      </header>

      <div v-if="loading" class="empty">Carregando detalhes...</div>

      <template v-else-if="order">
        <section class="section">
          <h3>Dados Gerais</h3>
          <p><strong>Operador:</strong> {{ order.operator?.name || '-' }}</p>
          <p><strong>Tipo:</strong> {{ order.order_type || '-' }}</p>
          <p><strong>Status:</strong> {{ order.status }}</p>
          <div class="mt-2">
            <label class="label">Atualizar status do pedido</label>
            <select class="input" :disabled="actionLoading" @change="emit('changeStatus', ($event.target as HTMLSelectElement).value)">
              <option value="">Selecione um novo status...</option>
              <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
            </select>
          </div>
        </section>

        <section class="section">
          <h3>Cliente Associado</h3>
          <p v-if="order.customer"><strong>{{ order.customer.name }}</strong></p>
          <p v-else>Cliente nao associado</p>
          <p v-if="!order.customer" class="hint">Use o campo abaixo para associar cliente.</p>
          <CustomerAssociateAutocomplete :customers="customers" :loading="actionLoading" @associate="emit('associateCustomer', $event)" />
        </section>

        <section class="section">
          <h3>Produtos</h3>
          <ul v-if="(order.items || []).length > 0" class="list">
            <li v-for="item in order.items || []" :key="item.id">
              {{ item.product?.nome || `Produto #${item.product_id}` }} - {{ item.quantity }} x {{ toCurrency(Number(item.price || 0)) }}
            </li>
          </ul>
          <p v-else class="empty">Nenhum item registrado neste pedido.</p>
        </section>

        <section class="section">
          <h3>Resumo Financeiro</h3>
          <p><strong>Quantidade de itens:</strong> {{ order.financial_summary?.items_count || 0 }}</p>
          <p><strong>Subtotal:</strong> {{ toCurrency(Number(order.financial_summary?.subtotal || 0)) }}</p>
          <p><strong>Descontos:</strong> {{ toCurrency(Number(order.financial_summary?.discount || 0)) }}</p>
          <p><strong>Acrescimos:</strong> {{ toCurrency(Number(order.financial_summary?.surcharge || 0)) }}</p>
          <p><strong>Valor Total:</strong> {{ toCurrency(Number(order.financial_summary?.total || order.total || 0)) }}</p>
        </section>

        <section class="section">
          <h3>Observacoes</h3>
          <p>{{ order.notes || 'Sem observacoes.' }}</p>
        </section>

        <section class="section">
          <h3>Timeline Operacional</h3>
          <ul v-if="timeline.length > 0" class="timeline">
            <li v-for="event in timeline" :key="event.id">
              <p><strong>{{ event.title }}</strong> ({{ event.event_type }})</p>
              <p>{{ event.description || '-' }}</p>
              <small>{{ event.created_at ? new Date(event.created_at).toLocaleString('pt-BR') : '-' }}</small>
            </li>
          </ul>
          <p v-else class="empty">Sem eventos operacionais para este pedido ate o momento.</p>
        </section>
      </template>

      <div v-else class="empty">Selecione um pedido para visualizar os detalhes.</div>
    </aside>
  </div>
</template>

<style scoped>
.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  z-index: 80;
  display: flex;
  justify-content: flex-end;
}

.drawer-panel {
  width: min(520px, 100%);
  height: 100%;
  overflow: auto;
  background: #0f172a;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem;
  color: #e2e8f0;
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section {
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 12px;
  padding: 0.75rem;
  margin-bottom: 0.75rem;
}

.section h3 {
  margin: 0 0 0.5rem 0;
}

.input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  padding: 0.55rem 0.65rem;
}

.label {
  font-size: 0.84rem;
}

.btn {
  border-radius: 10px;
  border: 1px solid transparent;
  padding: 0.4rem 0.75rem;
  cursor: pointer;
}

.btn.secondary {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
}

.list,
.timeline {
  margin: 0;
  padding-left: 1rem;
}

.timeline li {
  margin-bottom: 0.6rem;
}

.hint,
.empty {
  color: #94a3b8;
}
</style>
