import { defineStore } from 'pinia'
import { loginRequest, logoutRequest } from '../../modules/auth/auth.service'
import { getAccessToken, setAccessToken } from '../api/token'
import type { User } from '../types/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: getAccessToken(),
    user: null as User | null,
  }),
  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
  },
  actions: {
    async login(email: string, password: string) {
      const result = await loginRequest(email, password)
      setAccessToken(result.token)
      this.token = result.token
      this.user = result.user
    },
    async logout() {
      try {
        await logoutRequest()
      } finally {
        setAccessToken(null)
        this.token = null
        this.user = null
      }
    },
  },
})
