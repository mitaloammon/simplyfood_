import { api } from '../../shared/api/client';
export async function getDashboardMetrics() {
    const response = await api.get('/dashboard/metrics');
    return response.data.data;
}
