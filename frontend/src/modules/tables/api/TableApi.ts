import { apiClient } from '@/shared/api/client';

export interface RestaurantTableDto {
  id: number;
  user_id: number;
  number: number;
  capacity: number;
  location?: string | null;
  status: string;
  description?: string | null;
}

export class TableApi {
  async getAll() {
    return apiClient.get<{ status: string; data: RestaurantTableDto[] }>('/tables');
  }

  async create(payload: Partial<RestaurantTableDto> & { number: number }) {
    return apiClient.post<{ status: string; data: RestaurantTableDto }>('/tables', payload);
  }

  async updateStatus(id: number, status: string) {
    return apiClient.patch<{ status: string; data: RestaurantTableDto }>(`/tables/${id}/status`, { status });
  }
}
