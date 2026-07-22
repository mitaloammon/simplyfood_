import { AuthApi } from '../api/AuthApi';
import { useAuthStore } from '@/shared/stores/auth';

export class AuthService {
  private authStore = useAuthStore();

  async login(credentials: Record<string, any>): Promise<void> {
    const result = await AuthApi.login(credentials);
    if (result.status === 'success' && result.data) {
      const { token, user } = result.data;
      this.authStore.setAuth(token, user);
    } else {
      throw new Error(result.message || 'Login failed.');
    }
  }

  async register(data: Record<string, any>): Promise<void> {
    const result = await AuthApi.register(data);
    if (result.status === 'success' && result.data) {
      const { token, user } = result.data;
      this.authStore.setAuth(token, user);
    } else {
      throw new Error(result.message || 'Registration failed.');
    }
  }

  logout(): void {
    this.authStore.clearAuth();
  }
}
