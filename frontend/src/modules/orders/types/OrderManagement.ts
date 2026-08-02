import type { CustomerDto } from '@/modules/customers/api/CustomerApi';
import type { OrderDto, OrderManagementFilters, OrderManagementMeta, OrderManagementRow, OrderTimelineEntry } from '../api/OrderApi';

export type OrderStatus = 'WAITING_PAYMENT' | 'PAID' | 'PREPARING' | 'OUT_FOR_DELIVERY' | 'DELIVERED' | 'CANCELLED';

export type OrderDrawerState = {
  open: boolean;
  loading: boolean;
  selectedOrderId: number | null;
  order: OrderDto | null;
  timeline: OrderTimelineEntry[];
  associateLoading: boolean;
};

export type OrderFiltersState = Required<Omit<OrderManagementFilters, 'page' | 'per_page'>>;

export type OrderManagementState = {
  loading: boolean;
  errorMessage: string;
  successMessage: string;
  rows: OrderManagementRow[];
  meta: OrderManagementMeta;
  filters: OrderFiltersState;
  drawer: OrderDrawerState;
  customers: CustomerDto[];
};

export const DEFAULT_ORDER_META: OrderManagementMeta = {
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
};

export const DEFAULT_ORDER_FILTERS: OrderFiltersState = {
  order_number: '',
  customer: '',
  operator: '',
  status: '',
  order_type: '',
  date: '',
  value_min: '',
  value_max: '',
};
