import axios from 'axios'
import type { ApiEnvelope } from '../types/api'

export function apiErrorMessage(error: unknown): string {
  if (!axios.isAxiosError<ApiEnvelope<null>>(error)) return 'Não foi possível concluir a operação.'

  const response = error.response?.data
  if (!response) return 'Não foi possível conectar à API.'

  const validation = response.errors ? Object.values(response.errors).flat()[0] : null
  return validation || response.message || 'Não foi possível concluir a operação.'
}
