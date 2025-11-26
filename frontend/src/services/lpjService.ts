// frontend/src/services/lpjService.ts

import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

export interface LpjData {
  tor_id: number;
  activity_result: string;
  activity_evaluation: string;
  actual_date: string;
  budget_used: number;
}

export interface LpjResponse {
  success: boolean;
  message?: string;
  data?: any;
  errors?: Record<string, string[]>;
}

class LpjService {
  private getAuthHeader() {
    const authStore = useAuthStore();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${authStore.token}`
    };
  }

  async createLpj(data: LpjData): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to create LPJ',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ created successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  // Supports multipart/form-data when LPJ includes file attachments
  async createLpjWithFiles(formData: FormData): Promise<LpjResponse> {
    try {
      const authStore = useAuthStore();
      const response = await fetch(`${API_URL}/lpj`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authStore.token}`
          // NOTE: Do not set Content-Type for FormData; browser will set the correct multipart boundary
        },
        body: formData
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to create LPJ',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ created successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async updateLpj(id: number, data: Partial<LpjData>): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}`, {
        method: 'PUT',
        headers: this.getAuthHeader(),
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to update LPJ',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ updated successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async getLpj(id: number): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch LPJ'
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

  async getMyLpjs(): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj?my_lpjs=true`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch LPJs'
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

  async getAllLpjs(status?: string): Promise<LpjResponse> {
    try {
      let url = `${API_URL}/lpj`;
      if (status) {
        url += `?status=${status}`;
      }
      
      const response = await fetch(url, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch LPJs'
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

  async submitLpj(id: number, comment?: string): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}/submit`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ comment: comment || '' })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to submit LPJ'
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ submitted successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async compareBudget(id: number): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}/compare-budget`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to compare budget'
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

  async deleteLpj(id: number): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}`, {
        method: 'DELETE',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to delete LPJ'
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ deleted successfully'
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async reviewBySecretary(id: number, action: 'approved' | 'rejected' | 'request_revision', catatan: string): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}/review-secretary`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ action, catatan })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to review LPJ',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ reviewed successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async verifyByAdmin(id: number, action: 'approved' | 'rejected' | 'request_revision', catatan: string): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}/verify-admin`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ action, catatan })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to verify LPJ',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ verified successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async approveByHead(id: number, action: 'approved' | 'rejected' | 'request_revision', catatan: string): Promise<LpjResponse> {
    try {
      const response = await fetch(`${API_URL}/lpj/${id}/approve-head`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ action, catatan })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to approve LPJ',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'LPJ approved successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }
}

export default new LpjService();
