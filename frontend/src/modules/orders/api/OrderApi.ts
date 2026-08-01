import { apiClient } from '@/shared/api/client';

export interface OrderItemInput {
  product_id: number;
  quantity: number;
  price: number;
}

export interface OrderPayload {
  customer_id: number;
  status?: string;
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
  customer_id: number;
  status: string;
  total: number;
  customer?: {
    id: number;
    name: string;
  };
  items?: OrderItemDto[];
  created_at?: string;
}

export class OrderApi {
  async getAll(filters?: Record<string, string>) {
    return apiClient.get<{ status: string; data: OrderDto[] }>('/orders', { params: filters });
  }

  async getById(id: number) {
    return apiClient.get<{ status: string; data: OrderDto }>(`/orders/${id}`);
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
