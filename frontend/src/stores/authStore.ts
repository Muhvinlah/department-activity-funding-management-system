import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

import { login as loginService } from '../services/authService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),
  actions: {
    async login(credentials: { nim: number; password: string }) {
      const data = await loginService(credentials);
      this.user = data.user;
      this.token = data.token;
      // You can also save the token to localStorage here
    },
  },
});