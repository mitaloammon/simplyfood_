import { api } from '../../shared/api/client'
import type { ApiEnvelope, CashShift, Paginated } from '../../shared/types/api'

export async function getCurrentShift(): Promise<CashShift | null> {
  const response = await api.get<ApiEnvelope<CashShift | null>>('/cash/current')
  return response.data.data
}

export async function getCashHistory(): Promise<Paginated<CashShift>> {
  const response = await api.get<ApiEnvelope<Paginated<CashShift>>>('/cash/history', { params: { per_page: 100 } })
  return response.data.data
}

export async function openCash(cash_register_id: string, opening_balance: number): Promise<CashShift> {
  const response = await api.post<ApiEnvelope<CashShift>>('/cash/open', { cash_register_id, opening_balance })
  return response.data.data
}

export async function addCashMovement(type: 'BLEED' | 'SUPPLEMENT', amount: number, description: string): Promise<void> {
  await api.post('/cash/movements', { type, amount, description: description || null })
}

export async function closeCash(closing_balance: number, notes: string): Promise<CashShift> {
  const response = await api.post<ApiEnvelope<CashShift>>('/cash/close', {
    closing_balance,
    notes: notes || null,
  })
  return response.data.data
}
