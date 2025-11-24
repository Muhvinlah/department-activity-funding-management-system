import axios from 'axios';

// From your .env file
const API_URL = import.meta.env.VITE_API_BASE_URL;

export interface LoginCredentials {
  user_id: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data?: {
    user: {
      user_id: string;
      full_name: string;
      email: string;
      role_id: number;
      role?: {
        role_id: number;
        role_def: string;
      };
    };
    token: string;
    role: string;
  };
  errors?: Record<string, string[]>;
}

export const login = async (credentials: LoginCredentials): Promise<LoginResponse> => {
  const response = await axios.post(`${API_URL}/login`, credentials);
  return response.data;
};

export const register = async (credentials: {
  user_id: string;
  full_name: string;
  email: string;
  password: string;
  role_id: number;
}): Promise<LoginResponse> => {
  const response = await axios.post(`${API_URL}/register`, credentials);
  return response.data;
};

export const logout = async (): Promise<any> => {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      return { success: true, message: 'Already logged out' };
    }
    
    const response = await axios.post(`${API_URL}/logout`, {}, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    // Even if logout API fails, we still want to clear local session
    // Return success so the store can proceed with clearing data
    return { success: true, message: 'Logout processed' };
  }
};

export const getProfile = async (): Promise<any> => {
  const response = await axios.get(`${API_URL}/profile`, {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('token')}`
    }
  });
  return response.data;
};