import axios from "axios";

// Usar proxy reverso /api → API real
const API_URL = typeof window !== 'undefined' ? '/api' : (process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000');

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    "Content-Type": "application/json",
  },
});

// Tipo para propriedades da API
export interface PropertyApi {
  ListingKey: string;
  StreetAddress?: string;
  City?: string;
  ListPrice?: number;
  PropertySubType?: string;
  PropertyType?: string;
  BedroomsTotal?: number;
  BathroomsTotalInteger?: number;
  LivingArea?: number;
  Latitude?: number;
  Longitude?: number;
}

// Função para buscar propriedades
export async function fetchProperties(filters: Record<string, string> = {}): Promise<PropertyApi[]> {
  try {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value) params.append(key, value);
    });

    const response = await api.get(`/properties?${params.toString()}`);
    return response.data || [];
  } catch (error) {
    console.error('Error fetching properties:', error);
    return [];
  }
}
