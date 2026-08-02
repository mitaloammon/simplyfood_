import { apiClient } from '@/shared/api/client';

export interface ProductCategoryOption {
  id: number;
  name: string;
}

export interface ProductUnitOption {
  value: string;
  label: string;
}

export interface ProductQuickCreateDefaults {
  ativo: boolean;
  controla_estoque: boolean;
  produzido_cozinha: boolean;
  delivery: boolean;
  balcao: boolean;
  mesa: boolean;
  retirada: boolean;
  unidade: string;
}

export interface ProductQuickCreateOptions {
  categories: ProductCategoryOption[];
  units: ProductUnitOption[];
  defaults: ProductQuickCreateDefaults;
  permissions: {
    can_create: boolean;
    can_update: boolean;
  };
  validation_messages: Record<string, string>;
}

export interface CreateProductPayload {
  nome: string;
  category_id: number;
  preco_venda: number;
  unidade: string;
  descricao?: string;
  preco?: number;
  custo?: number;
  codigo_barras?: string;
  tempo_preparo?: number;
  controla_estoque?: boolean;
  produzido_cozinha?: boolean;
  delivery?: boolean;
  balcao?: boolean;
  mesa?: boolean;
  retirada?: boolean;
  ativo?: boolean;
  imagem?: string;
  imagem_file?: File;
}

export interface ProductDto {
  id: number;
  category_id: number;
  nome: string;
  descricao?: string | null;
  preco: number | string;
  preco_venda?: number | string;
  custo?: number | string | null;
  unidade?: string;
  codigo_barras?: string | null;
  controla_estoque?: boolean;
  produzido_cozinha?: boolean;
  delivery?: boolean;
  balcao?: boolean;
  mesa?: boolean;
  retirada?: boolean;
  created_by?: number | null;
  updated_by?: number | null;
  category?: ProductCategoryOption;
  tempo_preparo?: number;
  ativo?: boolean;
  imagem?: string | null;
}

export class ProductApi {
  async getQuickCreateOptions() {
    return apiClient.get<{ status: string; data: ProductQuickCreateOptions }>('/products/quick-create/options');
  }

  async getById(id: number) {
    return apiClient.get<{ status: string; data: ProductDto }>(`/products/${id}`);
  }

  async getActive() {
    return apiClient.get<{ status: string; data: ProductDto[] }>('/products/active');
  }

  async create(payload: CreateProductPayload | FormData) {
    return apiClient.post<{ status: string; data: ProductDto }>('/products', payload);
  }
}
