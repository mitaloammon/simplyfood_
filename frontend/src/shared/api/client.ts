import axios from 'axios';
import { useAuthStore } from '@/shared/stores/auth';

export const apiClient = axios.create({
  baseURL: (import.meta.env.VITE_API_URL as string) || 'http://localhost:8000/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// Interceptor to inject Authorization token
apiClient.interceptors.request.use(
  (config) => {
    try {
      const authStore = useAuthStore();
      if (authStore.token) {
        config.headers.Authorization = `Bearer ${authStore.token}`;
      }
    } catch {
      // Pinia might not be initialized yet
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);
