import { apiClient } from '@/shared/api/client';

export interface CreateProductPayload {
  nome: string;
  descricao?: string;
  preco: number;
  tempo_preparo?: number;
  ativo?: boolean;
  category_id?: number;
}

export interface ProductDto {
  id: number;
  nome: string;
  descricao?: string | null;
  preco: number | string;
  tempo_preparo?: number;
  ativo?: boolean;
}

export class ProductApi {
  async getActive() {
    return apiClient.get<{ status: string; data: ProductDto[] }>('/products/active');
  }

  async create(payload: CreateProductPayload) {
    return apiClient.post<{ status: string; data: ProductDto }>('/products', payload);
  }
}
