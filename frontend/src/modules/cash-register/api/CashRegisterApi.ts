import { apiClient } from '@/shared/api/client';

export interface CashRegisterDto {
  id: number;
  user_id: number;
  status: string;
  opening_balance: number;
  current_balance: number;
  opened_at?: string;
  closed_at?: string | null;
}

export interface CashTransactionDto {
  id: number;
  cash_register_id: number;
  user_id: number;
  type: string;
  amount: number;
  description?: string | null;
}

export class CashRegisterApi {
  async open(opening_balance: number) {
    return apiClient.post<{ status: string; data: CashRegisterDto }>('/cash/open', { opening_balance });
  }

  async current() {
    return apiClient.get<{ status: string; data: CashRegisterDto | null }>('/cash/current');
  }

  async history() {
    return apiClient.get<{ status: string; data: CashRegisterDto[] }>('/cash/history');
  }

  async transaction(payload: { type: string; amount: number; description?: string }) {
    return apiClient.post<{ status: string; data: CashTransactionDto }>('/cash/transaction', payload);
  }

  async close(payload: { declared_amount: number; blind_closing?: boolean; notes?: string }) {
    return apiClient.post('/cash/close', payload);
  }
}
