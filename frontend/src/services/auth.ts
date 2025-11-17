import { defineStore } from 'pinia';
import { login as loginService } from '@/services/authService';
import type { User } from '../services/types';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: null as string | null,
    error: null as string | null,
  }),
  actions: {
    async login(credentials: { nim: number; password: string }) {
      this.error = null;
      try {
        const data = await loginService(credentials);
        this.user = data.user;
        this.token = data.token;
        localStorage.setItem('authToken', data.token);
      } catch (err: any) {
        this.error = err.message;
        console.error('Authentication Error:', err.message);
      }
    },
    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('authToken');
    },
  },
});