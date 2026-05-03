import axios from "axios";

// Usar proxy reverso /api → API real (mesmo domínio no Railway)
const API_URL = typeof window !== 'undefined' ? '/api' : (process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000');

export const api = axios.create({
  baseURL: API_URL,
  headers: { "Content-Type": "application/json" },
});

// Interceptor JWT
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Interceptor 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      window.location.href = "/login";
    }
    return Promise.reject(error);
  }
);

// Funções do admin
export const login = (email: string, password: string) =>
  api.post("/auth/login", { email, password });

export const getProfile = () => api.get("/auth/profile");

export const getProperties = () => api.get("/properties");

export const getUsers = () => api.get("/users");

export const getAuditLogs = () => api.get("/audit-logs");

export const getKycRequests = () => api.get("/kyc");