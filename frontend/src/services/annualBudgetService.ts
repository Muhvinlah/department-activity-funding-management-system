import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// ============= Interfaces =============

export interface AnnualBudget {
  budget_id: number;
  tahun: string;
  budget: number;
  created_at?: string;
  updated_at?: string;
}

export interface AnnualBudgetDetails extends AnnualBudget {
  remaining_budget: number;
  usage_percentage: number;
  total_tors: number;
  approved_tors: number;
}

export interface CreateAnnualBudgetData {
  tahun: string;
  budget: number;
}

export interface UpdateAnnualBudgetData {
  tahun?: string;
  budget?: number;
}

export interface AnnualBudgetResponse {
  success: boolean;
  message?: string;
  data?: AnnualBudget | AnnualBudget[];
  errors?: Record<string, string[]>;
}

export interface AnnualBudgetDetailsResponse {
  success: boolean;
  message?: string;
  data?: {
    budget: AnnualBudget;
    remaining_budget: number;
    usage_percentage: number;
    total_tors: number;
    approved_tors: number;
  };
  errors?: Record<string, string[]>;
}

// ============= Service Class =============

class AnnualBudgetService {
  private getAuthHeader() {
    const authStore = useAuthStore();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${authStore.token}`
    };
  }

  async getAll(): Promise<AnnualBudgetResponse> {
    try {
      const response = await fetch(`${API_URL}/annual-budgets`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch annual budgets'
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

  async getById(id: number): Promise<AnnualBudgetDetailsResponse> {
    try {
      const response = await fetch(`${API_URL}/annual-budgets/${id}`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch annual budget'
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

  async create(data: CreateAnnualBudgetData): Promise<AnnualBudgetResponse> {
    try {
      const response = await fetch(`${API_URL}/annual-budgets`, {
        method: 'POST',
        headers: this.getAuthHeader(),
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to create annual budget',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message,
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async update(id: number, data: UpdateAnnualBudgetData): Promise<AnnualBudgetResponse> {
    try {
      const response = await fetch(`${API_URL}/annual-budgets/${id}`, {
        method: 'PUT',
        headers: this.getAuthHeader(),
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to update annual budget',
          errors: result.errors
        };
      }

      return {
        success: true,
        message: result.message,
        data: result.data
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async delete(id: number): Promise<{ success: boolean; message?: string }> {
    try {
      const response = await fetch(`${API_URL}/annual-budgets/${id}`, {
        method: 'DELETE',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to delete annual budget'
        };
      }

      return {
        success: true,
        message: result.message
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }
}

export default new AnnualBudgetService();
