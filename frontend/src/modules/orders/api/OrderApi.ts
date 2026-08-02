import { apiClient } from '@/shared/api/client';

export type OrderType = 'MESA' | 'DELIVERY' | 'BALCAO';

export interface OrderItemInput {
  product_id: number;
  quantity: number;
  price: number;
}

export interface OrderPayload {
  customer_id?: number | null;
  status?: string;
  order_type?: OrderType;
  discount?: number;
  surcharge?: number;
  notes?: string;
  items: OrderItemInput[];
  total?: number;
}

export interface OrderItemDto {
  id: number;
  order_id: number;
  product_id: number;
  quantity: number;
  price: number;
  product?: {
    id: number;
    nome: string;
    preco: number;
  };
}

export interface OrderDto {
  id: number;
  user_id: number;
  customer_id: number | null;
  status: string;
  order_type?: OrderType;
  total: number;
  discount?: number;
  surcharge?: number;
  notes?: string | null;
  operator?: {
    id: number;
    name: string;
    role: string;
  };
  customer?: {
    id: number;
    name: string;
  };
  financial_summary?: {
    items_count: number;
    subtotal: number;
    discount: number;
    surcharge: number;
    total: number;
  };
  timeline?: OrderTimelineEntry[];
  items_count?: number;
  items?: OrderItemDto[];
  created_at?: string;
  updated_at?: string;
}

export interface OrderTimelineEntry {
  id: number;
  event_type: string;
  title: string;
  description?: string | null;
  metadata?: Record<string, unknown> | null;
  operator?: {
    id: number;
    name: string;
    role: string;
  } | null;
  created_at?: string;
}

export interface OrderManagementRow {
  id: number;
  order_number: number;
  customer?: {
    id: number;
    name: string;
  } | null;
  operator?: {
    id: number;
    name: string;
    role: string;
  } | null;
  order_type: OrderType;
  items_count: number;
  total: number;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface OrderManagementMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface OrderManagementFilters {
  order_number?: string;
  customer?: string;
  operator?: string;
  status?: string;
  order_type?: string;
  date?: string;
  value_min?: string;
  value_max?: string;
  page?: number;
  per_page?: number;
}

export class OrderApi {
  async getAll(filters?: Record<string, string>) {
    return apiClient.get<{ status: string; data: OrderDto[] }>('/orders', { params: filters });
  }

  async getById(id: number) {
    return apiClient.get<{ status: string; data: OrderDto }>(`/orders/${id}`);
  }

  async getManagement(filters?: OrderManagementFilters) {
    return apiClient.get<{ status: string; data: OrderManagementRow[]; meta: OrderManagementMeta }>('/orders/management', {
      params: filters,
    });
  }

  async getTimeline(id: number) {
    return apiClient.get<{ status: string; data: OrderTimelineEntry[] }>(`/orders/${id}/timeline`);
  }

  async associateCustomer(id: number, customer_id: number) {
    return apiClient.patch<{ status: string; message: string; data: OrderDto }>(`/orders/${id}/associate-customer`, { customer_id });
  }

  async create(payload: OrderPayload) {
    return apiClient.post<{ status: string; data: OrderDto }>('/orders', payload);
  }

  async update(id: number, payload: OrderPayload) {
    return apiClient.put<{ status: string; data: OrderDto }>(`/orders/${id}`, payload);
  }

  async remove(id: number) {
    return apiClient.delete<{ status: string; success: boolean; message: string }>(`/orders/${id}`);
  }

  async changeStatus(id: number, status: string) {
    return apiClient.patch<{ status: string; data: OrderDto; message: string }>(`/orders/${id}/status`, { status });
  }
}
