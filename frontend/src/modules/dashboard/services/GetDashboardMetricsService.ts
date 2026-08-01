import { DashboardApi } from '../api/DashboardApi';
import type { DashboardPayload } from '../types/DashboardMetrics';

export class GetDashboardMetricsService {
  private readonly api: DashboardApi;

  constructor(api: DashboardApi) {
    this.api = api;
  }

  async execute(): Promise<DashboardPayload> {
    const result = await this.api.getMetrics();

    if (result.status !== 'success' || !result.data) {
      throw new Error(result.message || 'Nao foi possivel carregar as metricas do dashboard.');
    }

    return result.data;
  }
}
