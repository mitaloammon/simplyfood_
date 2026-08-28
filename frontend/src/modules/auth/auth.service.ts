import { api } from '../../shared/api/client'
import type { ApiEnvelope, User } from '../../shared/types/api'

interface LoginResponse {
  token: string
  token_type: 'Bearer'
  user: User
}

export async function loginRequest(email: string, password: string): Promise<LoginResponse> {
  const response = await api.post<ApiEnvelope<LoginResponse>>('/auth/login', { email, password })
  return response.data.data
}

export async function logoutRequest(): Promise<void> {
  await api.post('/auth/logout')
}
