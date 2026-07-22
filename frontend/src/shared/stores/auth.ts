import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface UserState {
  id: number | string;
  name: string;
  email: string;
  role: string;
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('sf_token'));
  const user = ref<UserState | null>(
    localStorage.getItem('sf_user')
      ? JSON.parse(localStorage.getItem('sf_user')!)
      : null
  );

  const isAuthenticated = computed(() => !!token.value);
  const userRole = computed(() => user.value?.role || null);

  function setAuth(newToken: string, newUser: UserState) {
    token.value = newToken;
    user.value = newUser;
    localStorage.setItem('sf_token', newToken);
    localStorage.setItem('sf_user', JSON.stringify(newUser));
  }

  function clearAuth() {
    token.value = null;
    user.value = null;
    localStorage.removeItem('sf_token');
    localStorage.removeItem('sf_user');
  }

  return {
    token,
    user,
    isAuthenticated,
    userRole,
    setAuth,
    clearAuth
  };
});
