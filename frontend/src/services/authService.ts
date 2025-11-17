import axios from 'axios';

// From your .env file
const API_URL = import.meta.env.VITE_API_BASE_URL;

export const login = async (credentials: {nim: number; password: string}) => {
  const response = await axios.post(`${API_URL}/login`, credentials);
  return response.data;
};