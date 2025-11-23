import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

import { login as loginService, logout as logoutService, type LoginCredentials } from '../services/authService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as any,
    token: null as string | null,
    role: null as string | null,
    isAuthenticated: false,
  }),
  getters: {
    isLoggedIn(): boolean {
      return !!this.token && !!this.user;
    },
    userRole(): string | null {
      return this.role;
    },
  },
  actions: {
    async login(credentials: LoginCredentials) {
      try {
        const response = await loginService(credentials);
        
        if (response.success && response.data) {
          this.user = response.data.user;
          this.token = response.data.token;
          this.role = response.data.role;
          this.isAuthenticated = true;
          
          // Save to localStorage for persistence
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('user', JSON.stringify(response.data.user));
          localStorage.setItem('role', response.data.role);
          
          return response;
        }
        
        throw new Error(response.message || 'Login failed');
      } catch (error: any) {
        this.user = null;
        this.token = null;
        this.role = null;
        this.isAuthenticated = false;
        throw error;
      }
    },

    async logout() {
      try {
        await logoutService();
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        this.role = null;
        this.isAuthenticated = false;
        
        // Clear localStorage
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        localStorage.removeItem('role');
      }
    },

    restoreSession() {
      const token = localStorage.getItem('token');
      const user = localStorage.getItem('user');
      const role = localStorage.getItem('role');
      
      if (token && user) {
        this.token = token;
        this.user = JSON.parse(user);
        this.role = role;
        this.isAuthenticated = true;
      }
    },

    clearSession() {
      this.user = null;
      this.token = null;
      this.role = null;
      this.isAuthenticated = false;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('role');
    },
  },
});