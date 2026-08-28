import { api } from '../../shared/api/client'
import type { ApiEnvelope, Customer, Paginated } from '../../shared/types/api'

export type CustomerPayload = Omit<Customer, 'id'>

export async function listCustomers(q = ''): Promise<Paginated<Customer>> {
  const response = await api.get<ApiEnvelope<Paginated<Customer>>>('/customers', {
    params: { q: q || undefined, per_page: 100 },
  })
  return response.data.data
}

export async function createCustomer(payload: CustomerPayload): Promise<Customer> {
  const response = await api.post<ApiEnvelope<Customer>>('/customers', payload)
  return response.data.data
}

export async function updateCustomer(id: string, payload: CustomerPayload): Promise<Customer> {
  const response = await api.patch<ApiEnvelope<Customer>>('/customers/' + id, payload)
  return response.data.data
}

export async function deleteCustomer(id: string): Promise<void> {
  await api.delete('/customers/' + id)
}
