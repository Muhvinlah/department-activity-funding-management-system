import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// ============= Interfaces =============

export interface DashboardSummary {
  overview: {
    total_tor: number;
    total_lpj: number;
    total_users: number;
  };
  tor_statistics: Record<string, number>;
  lpj_statistics: Record<string, number>;
  budget_info: {
    total_budget: number;
    used_budget: number;
    remaining_budget: number;
    usage_percentage: number;
  } | null;
  pending_approvals: {
    tor_submitted: number;
    tor_reviewed: number;
    tor_verified: number;
    lpj_submitted: number;
    lpj_reviewed: number;
    lpj_verified: number;
  };
  recent_activities: Array<{
    id: number;
    type: string;
    activity_name: string;
    status: string;
    user: string;
    catatan: string | null;
    timestamp: string;
  }>;
  user_statistics: {
    my_tors: number;
    my_lpjs: number;
    my_approved_tors: number;
    my_approved_lpjs: number;
  } | null;
}

export interface ChartData {
  monthly_submissions: Array<{
    month: string;
    tor_count: number;
    lpj_count: number;
    total_count: number;
  }>;
  budget_by_category: Array<{
    category: string;
    amount: number;
  }>;
  lpj_budget_by_category: Array<{
    category: string;
    amount: number;
  }>;
  status_distribution: Array<{
    type: string;
    status: string;
    count: number;
    label: string;
  }>;
  budget_vs_realization: Array<{
    activity: string;
    budget_submitted: number;
    budget_used: number;
    variance: number;
  }>;
  approval_timeline: Array<{
    stage: string;
    average_days: number;
  }>;
  year: number;
}

export interface FilterParams {
  status?: string;
  category_id?: number;
  year?: number;
  start_date?: string;
  end_date?: string;
  budget_min?: number;
  budget_max?: number;
  per_page?: number;
}

export interface FilteredData {
  tors: {
    data: Array<any>;
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
  };
  statistics: {
    total_records: number;
    total_budget: number;
    average_budget: number;
    status_breakdown: Record<string, number>;
  };
  filters_applied: Record<string, any>;
}

export interface AnnualBudgetData {
  year: number;
  budget_overview: {
    total_budget: number;
    budget_used: number;
    budget_pending: number;
    budget_remaining: number;
    usage_percentage: number;
    pending_percentage: number;
  };
  budget_by_category: Array<{
    category: string;
    allocated: number;
    tor_count: number;
  }>;
  monthly_usage: Array<{
    month: string;
    monthly_usage: number;
    cumulative_usage: number;
  }>;
  top_activities: Array<{
    activity: string;
    budget: number;
    date_range: string;
  }>;
  statistics: {
    total_approved_tors: number;
    total_pending_tors: number;
    average_budget_per_activity: number;
  };
}

export interface DashboardResponse {
  success: boolean;
  message?: string;
  data?: DashboardSummary;
  cached_at?: string;
}

export interface ChartDataResponse {
  success: boolean;
  message?: string;
  data?: ChartData;
}

export interface FilteredDataResponse {
  success: boolean;
  message?: string;
  data?: FilteredData;
}

export interface AnnualBudgetDataResponse {
  success: boolean;
  message?: string;
  data?: AnnualBudgetData;
}

// ============= Service Class =============

class dashboardService {
  private getAuthHeader() {
    const authStore = useAuthStore();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${authStore.token}`
    };
  }

  async getSummary(): Promise<DashboardResponse> {
    try {
      const response = await fetch(`${API_URL}/dashboard/summary`, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch dashboard summary'
        };
      }

      return {
        success: true,
        data: result.data,
        cached_at: result.cached_at
      };
    } catch (error: any) {
      return {
        success: false,
        message: error.message || 'Network error occurred'
      };
    }
  }

  async getChartData(year?: number): Promise<ChartDataResponse> {
    try {
      const params = new URLSearchParams();
      if (year) {
        params.append('year', year.toString());
      }

      const url = `${API_URL}/dashboard/charts${params.toString() ? '?' + params.toString() : ''}`;
      const response = await fetch(url, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch chart data'
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

  async getFilteredData(filters: FilterParams): Promise<FilteredDataResponse> {
    try {
      const params = new URLSearchParams();
      
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });

      const url = `${API_URL}/dashboard/filter${params.toString() ? '?' + params.toString() : ''}`;
      const response = await fetch(url, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch filtered data'
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

  async getAnnualBudgetData(year?: number): Promise<AnnualBudgetDataResponse> {
    try {
      const params = new URLSearchParams();
      if (year) {
        params.append('year', year.toString());
      }

      const url = `${API_URL}/dashboard/annual-budget${params.toString() ? '?' + params.toString() : ''}`;
      const response = await fetch(url, {
        method: 'GET',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to fetch annual budget data'
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

  async refreshCache(): Promise<{ success: boolean; message?: string }> {
    try {
      const response = await fetch(`${API_URL}/dashboard/refresh-cache`, {
        method: 'POST',
        headers: this.getAuthHeader()
      });

      const result = await response.json();

      if (!response.ok) {
        return {
          success: false,
          message: result.message || 'Failed to refresh cache'
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

export default new dashboardService();
