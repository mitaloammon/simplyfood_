export interface DashboardUserSummary {
  id: number;
  name: string;
  role: string;
}

export interface DashboardMetric {
  key: string;
  title: string;
  value: string;
  description: string;
  icon: string;
}

export interface DashboardPayload {
  user: DashboardUserSummary;
  metrics: DashboardMetric[];
}

export interface DashboardMetricsResponse {
  status: 'success' | 'error';
  message?: string;
  data?: DashboardPayload;
}
