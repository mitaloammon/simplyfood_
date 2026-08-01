import { apiClient } from '@/shared/api/client';
import type { DashboardMetricsResponse } from '../types/DashboardMetrics';

export class DashboardApi {
  async getMetrics(): Promise<DashboardMetricsResponse> {
    const response = await apiClient.get<DashboardMetricsResponse>('/dashboard/metrics');
    return response.data;
  }
}
