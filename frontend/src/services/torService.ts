// frontend/src/services/torService.ts

import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface TorData {
  activity_name: string;
  activity_background: string;
  activity_purpose: string;
  participant: string;
  start_date: string;
  end_date: string;
  budget_submitted: number;
  pic: string;
  category_id: number;
  budget_id: number;
}

export interface TorResponse {
  success: boolean;
  message?: string;
  data?: any;
  errors?: Record<string, string[]>;
}

class TorService {
  private getAuthHeader() {
    const authStore = useAuthStore();
    return {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${authStore.token}`
    };
  }

  async createTor(data: TorData): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to create TOR',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'TOR created successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async updateTor(id: number, data: Partial<TorData>): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}`, {
        method: 'PUT',
        headers: this.getAuthHeader(),
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to update TOR',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'TOR updated successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async getTor(id: number): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch TOR'
        };
      }

      return {
        success: true,
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async getMyTors(): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor?my_tors=true`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch TORs'
        };
      }

      return {
        success: true,
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async submitTor(id: number, comment?: string): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}/submit`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ comment: comment || '' })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to submit TOR'
        };
      }

      return {
        success: true,
        message: result.message || 'TOR submitted successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async deleteTor(id: number): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}`, {
        method: 'DELETE',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to delete TOR'
        };
      }

      return {
        success: true,
        message: result.message || 'TOR deleted successfully'
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }
}

export default new TorService();
