export type Role = 'ADMIN' | 'MANAGER' | 'CASHIER' | 'WAITER' | 'KITCHEN'

export interface User {
  id: string
  name: string
  email: string
  role: Role
  establishment_id: string
}

export interface ApiEnvelope<T> {
  status: 'success' | 'error'
  data: T
  message: string
  errors?: Record<string, string[]>
}

export interface Paginated<T> {
  current_page: number
  data: T[]
  last_page: number
  per_page: number
  total: number
}

export interface DashboardMetrics {
  open_orders: number
  occupied_tables: number
  today_revenue: number
  open_shift: boolean
}

export interface Customer {
  id: string
  name: string
  phone: string | null
  email: string | null
  document: string | null
  address: string | null
}

export interface Category {
  id: string
  name: string
  sort_order: number
  active: boolean
}

export interface Product {
  id: string
  category_id: string | null
  name: string
  description: string | null
  price: string
  cost_price: string
  sku: string | null
  is_available: boolean
  preparation_time_minutes: number
}

export type TableStatus = 'FREE' | 'OCCUPIED' | 'RESERVED' | 'BILLING'

export interface DiningTable {
  id: string
  number: number
  capacity: number
  status: TableStatus
}

export type CommandStatus = 'FREE' | 'OPEN' | 'BLOCKED' | 'CLOSED'

export interface Command {
  id: string
  code: string
  status: CommandStatus
  table: Pick<DiningTable, 'id' | 'number' | 'status'> | null
}

export type OrderStatus = 'OPEN' | 'IN_PREPARATION' | 'READY' | 'DELIVERED' | 'CLOSED' | 'CANCELLED'
export type OrderType = 'TABLE' | 'COMMAND' | 'COUNTER'

export interface OrderItem {
  id: string
  product: Pick<Product, 'id' | 'name' | 'sku'>
  quantity: number
  unit_price: string
  total_price: string
  status: string
  notes: string | null
}

export interface Order {
  id: string
  customer_id: string | null
  table_id: string | null
  command_id: string | null
  order_type: OrderType
  status: OrderStatus
  subtotal: string
  discount: string
  total_amount: string
  items: OrderItem[]
  created_at: string
}

export interface CashMovement {
  id: string
  type: 'BLEED' | 'SUPPLEMENT'
  amount: string
  description: string | null
  created_at: string
}

export interface CashShift {
  id: string
  cash_register: { id: string; name: string; location: string | null }
  opening_balance: string
  closing_balance: string | null
  opened_at: string
  closed_at: string | null
  status: 'OPEN' | 'CLOSED'
  notes: string | null
  movements?: CashMovement[]
}
