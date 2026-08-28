import { defineStore } from 'pinia'
import { api } from '../api/client'

type User = { id: string; name: string; email: string; role: string; establishment_id: string }

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') as string | null,
    user: null as User | null,
  }),
  actions: {
    async login(email: string, password: string) {
      const { data } = await api.post('/auth/login', { email, password })
      this.token = data.data.token
      this.user = data.data.user
      localStorage.setItem('token', this.token!)
    },
    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
    },
  },
})
