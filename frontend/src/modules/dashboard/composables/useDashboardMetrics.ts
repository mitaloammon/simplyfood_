import { onMounted, ref } from 'vue';
import { DashboardApi } from '../api/DashboardApi';
import { GetDashboardMetricsService } from '../services/GetDashboardMetricsService';
import type { DashboardMetric, DashboardUserSummary } from '../types/DashboardMetrics';

const dashboardApi = new DashboardApi();
const getDashboardMetricsService = new GetDashboardMetricsService(dashboardApi);

export function useDashboardMetrics() {
  const loading = ref(false);
  const errorMessage = ref('');
  const metrics = ref<DashboardMetric[]>([]);
  const user = ref<DashboardUserSummary | null>(null);

  const loadMetrics = async () => {
    loading.value = true;
    errorMessage.value = '';

    try {
      const payload = await getDashboardMetricsService.execute();
      metrics.value = payload.metrics;
      user.value = payload.user;
    } catch (error: unknown) {
      if (error instanceof Error) {
        errorMessage.value = error.message;
      } else {
        errorMessage.value = 'Erro inesperado ao carregar o dashboard.';
      }
    } finally {
      loading.value = false;
    }
  };

  onMounted(loadMetrics);

  return {
    loading,
    errorMessage,
    metrics,
    user,
    loadMetrics,
  };
}
