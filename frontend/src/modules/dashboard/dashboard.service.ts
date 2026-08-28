import { api } from '../../shared/api/client'
import type { ApiEnvelope, DashboardMetrics } from '../../shared/types/api'

export async function getDashboardMetrics(): Promise<DashboardMetrics> {
  const response = await api.get<ApiEnvelope<DashboardMetrics>>('/dashboard/metrics')
  return response.data.data
}
