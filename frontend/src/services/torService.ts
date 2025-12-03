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

class torService {
  private getAuthHeader() {
    const authStore = useAuthStore();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
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

  async updateTor(id: number, data: FormData): Promise<TorResponse> {
    try {
      
      data.append('_method', 'PUT');

      const authStore = useAuthStore();
      const headers: Record<string, string> = {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      };
      // Do NOT set Content-Type for FormData, browser does it automatically with boundary

      const response = await fetch(`${API_URL}/tor/${id}`, {
        method: 'POST',
        headers: headers,
        body: data
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

  async getMyApprovedTors(): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor?my_tors=true&status=approved_by_head`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch approved TORs'
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

  async getAllTors(status?: string): Promise<TorResponse> {
    try {
      let url = `${API_URL}/tor`;
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

  async reviewBySecretary(id: number, action: 'approved' | 'rejected' | 'request_revision', catatan: string): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}/review-secretary`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ action, catatan })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to review TOR',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'TOR reviewed successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async verifyByAdmin(id: number, action: 'approved' | 'rejected' | 'request_revision', catatan: string): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}/verify-admin`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ action, catatan })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to verify TOR',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'TOR verified successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async approveByHead(id: number, action: 'approved' | 'rejected' | 'request_revision', catatan: string): Promise<TorResponse> {
    try {
      const response = await fetch(`${API_URL}/tor/${id}/approve-head`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify({ action, catatan })
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to approve TOR',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'TOR approved successfully',
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async uploadAttachment(torId: number, file: File, fileType: string): Promise<TorResponse> {
    try {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('tor_id', String(torId));
      formData.append('file_type', fileType);

      const authStore = useAuthStore();
      const headers: Record<string, string> = {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      };
      // Do NOT set Content-Type for FormData

      const response = await fetch(`${API_URL}/attachments/upload`, {
        method: 'POST',
        headers: headers,
        body: formData
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to upload attachment',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message || 'Attachment uploaded successfully',
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

export default new torService();
