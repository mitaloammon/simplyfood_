import { api } from '../../shared/api/client'
import type { ApiEnvelope, Order, OrderStatus, OrderType, Paginated } from '../../shared/types/api'

export interface OrderItemPayload {
  product_id: string
  quantity: number
  notes: string | null
}

export interface CreateOrderPayload {
  order_type: OrderType
  table_id: string | null
  command_id: string | null
  customer_id: string | null
  items: OrderItemPayload[]
}

export async function listOrders(status = ''): Promise<Paginated<Order>> {
  const response = await api.get<ApiEnvelope<Paginated<Order>>>('/orders', {
    params: { status: status || undefined, per_page: 100 },
  })
  return response.data.data
}

export async function createOrder(payload: CreateOrderPayload): Promise<Order> {
  const response = await api.post<ApiEnvelope<Order>>('/orders', payload)
  return response.data.data
}

export async function addOrderItem(orderId: string, payload: OrderItemPayload): Promise<Order> {
  const response = await api.post<ApiEnvelope<Order>>('/orders/' + orderId + '/items', payload)
  return response.data.data
}

export async function updateOrderStatus(orderId: string, status: Exclude<OrderStatus, 'OPEN'>): Promise<Order> {
  const response = await api.patch<ApiEnvelope<Order>>('/orders/' + orderId + '/status', { status })
  return response.data.data
}

export async function addPayment(orderId: string, payment_method: string, amount: number): Promise<void> {
  await api.post('/orders/' + orderId + '/payments', { payment_method, amount })
}
