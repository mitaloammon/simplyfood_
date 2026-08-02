import { computed, reactive } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/shared/stores/toast';
import { OrderManagementService } from '../services/OrderManagementService';
import { DEFAULT_ORDER_FILTERS, DEFAULT_ORDER_META, type OrderManagementState } from '../types/OrderManagement';
import { dispatchOrdersChanged } from '../hooks/useOrdersRealtimeSync';

const STATUS_OPTIONS = ['WAITING_PAYMENT', 'PAID', 'PREPARING', 'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED'];
const ORDER_TYPE_OPTIONS = ['MESA', 'DELIVERY', 'BALCAO'];

export const useOrderManagement = () => {
  const service = new OrderManagementService();
  const toastStore = useToastStore();

  const state = reactive<OrderManagementState>({
    loading: false,
    errorMessage: '',
    successMessage: '',
    rows: [],
    meta: { ...DEFAULT_ORDER_META },
    filters: { ...DEFAULT_ORDER_FILTERS },
    drawer: {
      open: false,
      loading: false,
      selectedOrderId: null,
      order: null,
      timeline: [],
      associateLoading: false,
    },
    customers: [],
  });

  const formattedSummary = computed(() => {
    const summary = state.drawer.order?.financial_summary;
    if (!summary) {
      return {
        items_count: 0,
        subtotal: 0,
        discount: 0,
        surcharge: 0,
        total: 0,
      };
    }

    return summary;
  });

  const loadCustomers = async () => {
    state.customers = await service.loadCustomers();
  };

  const loadOrders = async (page = state.meta.current_page) => {
    state.loading = true;
    state.errorMessage = '';

    try {
      const { rows, meta } = await service.loadOrders({
        ...state.filters,
        page,
        per_page: state.meta.per_page,
      });

      state.rows = rows;
      state.meta = meta;
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        state.errorMessage = error.response?.data?.message || 'Erro ao carregar pedidos.';
        toastStore.error(error.response?.data?.message || 'Erro ao carregar pedidos.');
      } else {
        state.errorMessage = 'Erro ao carregar pedidos.';
        toastStore.error('Erro ao carregar pedidos.');
      }
    } finally {
      state.loading = false;
    }
  };

  const loadOrderDetails = async (orderId: number) => {
    state.drawer.open = true;
    state.drawer.loading = true;
    state.drawer.selectedOrderId = orderId;
    state.errorMessage = '';

    try {
      const [order, timeline] = await Promise.all([service.loadOrderDetails(orderId), service.loadTimeline(orderId)]);
      state.drawer.order = order;
      state.drawer.timeline = timeline;
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        state.errorMessage = error.response?.data?.message || 'Erro ao carregar detalhes do pedido.';
        toastStore.error(error.response?.data?.message || 'Erro ao carregar detalhes do pedido.');
      } else {
        state.errorMessage = 'Erro ao carregar detalhes do pedido.';
        toastStore.error('Erro ao carregar detalhes do pedido.');
      }
    } finally {
      state.drawer.loading = false;
    }
  };

  const resetFilters = async () => {
    state.filters = { ...DEFAULT_ORDER_FILTERS };
    await loadOrders(1);
  };

  const closeDrawer = () => {
    state.drawer.open = false;
    state.drawer.selectedOrderId = null;
    state.drawer.order = null;
    state.drawer.timeline = [];
  };

  const associateCustomer = async (customerId: number) => {
    if (!state.drawer.selectedOrderId) {
      return;
    }

    state.drawer.associateLoading = true;
    state.errorMessage = '';

    try {
      const order = await service.associateCustomer(state.drawer.selectedOrderId, customerId);
      state.drawer.order = order;
      state.drawer.timeline = await service.loadTimeline(state.drawer.selectedOrderId);
      state.successMessage = 'Cliente associado com sucesso.';
      toastStore.success('Cliente associado com sucesso.');
      await loadOrders(state.meta.current_page);
      dispatchOrdersChanged();
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        state.errorMessage = error.response?.data?.message || 'Erro ao associar cliente.';
        toastStore.error(error.response?.data?.message || 'Erro ao associar cliente.');
      } else {
        state.errorMessage = 'Erro ao associar cliente.';
        toastStore.error('Erro ao associar cliente.');
      }
    } finally {
      state.drawer.associateLoading = false;
    }
  };

  const changeStatus = async (status: string) => {
    if (!state.drawer.selectedOrderId) {
      return;
    }

    state.drawer.associateLoading = true;
    state.errorMessage = '';

    try {
      const order = await service.changeStatus(state.drawer.selectedOrderId, status);
      state.drawer.order = order;
      state.drawer.timeline = await service.loadTimeline(state.drawer.selectedOrderId);
      state.successMessage = 'Status atualizado com sucesso.';
      toastStore.success('Status atualizado com sucesso.');
      await loadOrders(state.meta.current_page);
      dispatchOrdersChanged();
    } catch (error: unknown) {
      if (axios.isAxiosError(error)) {
        state.errorMessage = error.response?.data?.message || 'Erro ao alterar status.';
        toastStore.error(error.response?.data?.message || 'Erro ao alterar status.');
      } else {
        state.errorMessage = 'Erro ao alterar status.';
        toastStore.error('Erro ao alterar status.');
      }
    } finally {
      state.drawer.associateLoading = false;
    }
  };

  return {
    state,
    statusOptions: STATUS_OPTIONS,
    orderTypeOptions: ORDER_TYPE_OPTIONS,
    formattedSummary,
    loadCustomers,
    loadOrders,
    loadOrderDetails,
    resetFilters,
    closeDrawer,
    associateCustomer,
    changeStatus,
  };
};
