import { apiClient } from '@/shared/api/client';

export interface CreateCustomerDto {
  name: string;
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

export class CustomerApi {
  async create(data: CreateCustomerDto) {
    return apiClient.post('/customers', data);
  }

  async findByWhatsapp(whatsapp: string) {
    return apiClient.get(`/customers?whatsapp=${whatsapp}`);
  }
}
