import { apiClient } from '@/shared/api/client';

export interface CommandDto {
  id: number;
  user_id: number;
  table_id: number;
  customer_id?: number | null;
  status: string;
  subtotal: number;
  total: number;
  notes?: string | null;
}

export class CommandApi {
  async getAll() {
    return apiClient.get<{ status: string; data: CommandDto[] }>('/commands');
  }

  async create(payload: {
    table_id: number;
    customer_id?: number;
    subtotal?: number;
    total?: number;
    notes?: string;
  }) {
    return apiClient.post<{ status: string; data: CommandDto }>('/commands', payload);
  }

  async updateStatus(id: number, status: string) {
    return apiClient.patch<{ status: string; data: CommandDto }>(`/commands/${id}/status`, { status });
  }
}
