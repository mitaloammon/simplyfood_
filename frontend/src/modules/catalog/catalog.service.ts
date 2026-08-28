import { api } from '../../shared/api/client'
import type { ApiEnvelope, Category, Paginated, Product } from '../../shared/types/api'

export interface CategoryPayload {
  name: string
  sort_order: number
  active: boolean
}

export interface ProductPayload {
  category_id: string | null
  name: string
  description: string | null
  price: number
  cost_price: number
  sku: string | null
  is_available: boolean
  preparation_time_minutes: number
}

export async function listCategories(): Promise<Paginated<Category>> {
  const response = await api.get<ApiEnvelope<Paginated<Category>>>('/categories', { params: { per_page: 100 } })
  return response.data.data
}

export async function createCategory(payload: CategoryPayload): Promise<Category> {
  const response = await api.post<ApiEnvelope<Category>>('/categories', payload)
  return response.data.data
}

export async function updateCategory(id: string, payload: Partial<CategoryPayload>): Promise<Category> {
  const response = await api.patch<ApiEnvelope<Category>>('/categories/' + id, payload)
  return response.data.data
}

export async function deleteCategory(id: string): Promise<void> {
  await api.delete('/categories/' + id)
}

export async function listProducts(q = ''): Promise<Paginated<Product>> {
  const response = await api.get<ApiEnvelope<Paginated<Product>>>('/products', {
    params: { q: q || undefined, per_page: 100 },
  })
  return response.data.data
}

export async function createProduct(payload: ProductPayload): Promise<Product> {
  const response = await api.post<ApiEnvelope<Product>>('/products', payload)
  return response.data.data
}

export async function updateProduct(id: string, payload: Partial<ProductPayload>): Promise<Product> {
  const response = await api.patch<ApiEnvelope<Product>>('/products/' + id, payload)
  return response.data.data
}

export async function deleteProduct(id: string): Promise<void> {
  await api.delete('/products/' + id)
}
