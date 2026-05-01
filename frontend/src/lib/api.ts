const API_BASE = process.env.NEXT_PUBLIC_API_URL || "http://localhost:3000";

export interface PropertyApi {
  ListingKey: string;
  OriginatingSystemKey: string;
  StandardStatus: string;
  PropertyType: string;
  PropertySubType?: string;
  StreetAddress?: string;
  City?: string;
  StateOrProvince?: string;
  PostalCode?: string;
  Country?: string;
  Latitude?: number;
  Longitude?: number;
  BedroomsTotal?: number;
  BathroomsTotalInteger?: number;
  LivingArea?: number;
  LotSizeArea?: number;
  ListPrice?: number;
  Currency?: string;
  ListingContractDate?: string;
  ExpirationDate?: string;
  ModificationTimestamp?: string;
  OriginalEntryTimestamp?: string;
}

export async function fetchProperties(filters?: Record<string, string>): Promise<PropertyApi[]> {
  const qs = filters ? new URLSearchParams({ ...filters, withCoords: "true" }).toString() : "withCoords=true";
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 5000);
  try {
    const res = await fetch(`${API_BASE}/properties?${qs}`, {
      signal: controller.signal,
      cache: "no-store",
    });
    clearTimeout(timeout);
    if (!res.ok) throw new Error("Failed to fetch properties");
    return res.json();
  } catch {
    clearTimeout(timeout);
    return [];
  }
}

export async function fetchProperty(id: string): Promise<PropertyApi> {
  const res = await fetch(`${API_BASE}/properties/${id}`, { next: { revalidate: 30 } });
  if (!res.ok) throw new Error("Failed to fetch property");
  return res.json();
}

export async function searchGeo(lat: number, lng: number, radiusKm = 10) {
  const res = await fetch(`${API_BASE}/properties/geo-search?lat=${lat}&lng=${lng}&radius=${radiusKm}`);
  if (!res.ok) throw new Error("Geo search failed");
  return res.json();
}
