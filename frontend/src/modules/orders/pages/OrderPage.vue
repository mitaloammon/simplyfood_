<script setup lang="ts">
import { onMounted } from 'vue';
import { useOrderManagement } from '../composables/useOrderManagement';
import OrdersFilterBar from '../components/OrdersFilterBar.vue';
import OrdersTable from '../components/OrdersTable.vue';
import OrderManagementDrawer from '../components/OrderManagementDrawer.vue';

const {
  state,
  statusOptions,
  orderTypeOptions,
  loadCustomers,
  loadOrders,
  loadOrderDetails,
  resetFilters,
  closeDrawer,
  associateCustomer,
  changeStatus,
} = useOrderManagement();

const applyFilters = async () => {
  await loadOrders(1);
};

onMounted(async () => {
  await Promise.all([loadCustomers(), loadOrders(1)]);
});
</script>

<template>
  <div class="orders-management-page">
    <header class="page-header">
      <h1>Pedidos</h1>
      <p>Módulo exclusivo de gerenciamento operacional dos pedidos cadastrados.</p>
    </header>

    <div v-if="state.successMessage" class="alert success">{{ state.successMessage }}</div>
    <div v-if="state.errorMessage" class="alert error">{{ state.errorMessage }}</div>

    <OrdersFilterBar
      v-model="state.filters"
      :status-options="statusOptions"
      :order-type-options="orderTypeOptions"
      @apply="applyFilters"
      @reset="resetFilters"
    />

    <OrdersTable
      :loading="state.loading"
      :rows="state.rows"
      :meta="state.meta"
      @select="loadOrderDetails"
      @page-change="loadOrders"
    />

    <OrderManagementDrawer
      :open="state.drawer.open"
      :loading="state.drawer.loading"
      :order="state.drawer.order"
      :timeline="state.drawer.timeline"
      :customers="state.customers"
      :action-loading="state.drawer.associateLoading"
      :status-options="statusOptions"
      @close="closeDrawer"
      @associate-customer="associateCustomer"
      @change-status="changeStatus"
    />
  </div>
</template>

<style scoped>
.orders-management-page {
  max-width: 1280px;
  margin: 0 auto;
}

.page-header h1 {
  margin: 0;
  color: #f8fafc;
  font-size: 2rem;
}

.page-header p {
  color: #94a3b8;
  margin-top: 0.35rem;
}

.alert {
  margin-top: 1rem;
  margin-bottom: 1rem;
  padding: 0.7rem 0.9rem;
  border-radius: 10px;
  font-size: 0.9rem;
}

.alert.success {
  background: rgba(72, 187, 120, 0.15);
  border: 1px solid rgba(72, 187, 120, 0.25);
  color: #68d391;
}

.alert.error {
  background: rgba(229, 62, 62, 0.15);
  border: 1px solid rgba(229, 62, 62, 0.25);
  color: #fc8181;
}
</style>
