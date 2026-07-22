import { apiClient } from '@/shared/api/client';

export class AuthApi {
  static async login(credentials: Record<string, any>) {
    const response = await apiClient.post('/login', credentials);
    return response.data;
  }

  static async register(data: Record<string, any>) {
    const response = await apiClient.post('/register', data);
    return response.data;
  }
}
