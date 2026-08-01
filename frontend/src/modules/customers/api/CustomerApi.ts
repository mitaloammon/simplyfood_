import { apiClient } from '@/shared/api/client';

export interface CreateCustomerDto {
  name: string;
  phone: string;
  email: string;
  whatsapp: string;
  cpf_cnpj: string;
  cep?: string;
  address?: string;
  number?: string | number;
  complement?: string;
  neighborhood?: string;
  city?: string;
  state?: string;
}

export interface CustomerDto {
  id: number;
  user_id?: number | null;
  name: string;
  phone: string;
  formatted_phone?: string;
  whatsapp?: string | null;
  email?: string | null;
  cep?: string | null;
  address?: string | null;
  number?: string | null;
  neighborhood?: string | null;
  city?: string | null;
  state?: string | null;
  cpf_cnpj?: string | null;
  created_at?: string;
  updated_at?: string;
}

export class CustomerApi {
  async create(data: CreateCustomerDto) {
    return apiClient.post('/customers', data);
  }

  async getAll(filters?: Record<string, string>) {
    return apiClient.get<{ status: string; data: CustomerDto[] }>('/customers', {
      params: filters,
    });
  }

  async update(id: number, data: Partial<CreateCustomerDto>) {
    return apiClient.put<{ status: string; data: CustomerDto }>(`/customers/${id}`, data);
  }

  async delete(id: number) {
    return apiClient.delete<{ status: string; success: boolean; message: string }>(`/customers/${id}`);
  }

  async findByWhatsapp(whatsapp: string) {
    return apiClient.get(`/customers?whatsapp=${whatsapp}`);
  }
}
