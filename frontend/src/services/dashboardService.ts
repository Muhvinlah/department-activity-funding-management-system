import { useAuthStore } from '@/stores/authStore';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

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

export interface DashboardResponse {
  success: boolean;
  message?: string;
  data?: DashboardSummary;
  cached_at?: string;
}

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
}

export default new dashboardService();
