export interface Property {
  ListingKey: string;
  OriginatingSystemKey: string;
  StandardStatus: string;
  PropertyType: string;
  PropertySubType?: string | null;
  City?: string | null;
  StateOrProvince?: string | null;
  PostalCode?: string | null;
  BedroomsTotal?: number | null;
  BathroomsTotalInteger?: number | null;
  LivingArea?: number | null;
  LotSizeArea?: number | null;
  ListPrice?: number | null;
  UnparsedAddress?: string | null;
  Latitude?: number | null;
  Longitude?: number | null;
  createdAt: string;
  updatedAt: string;
}

export interface CreatePropertyDto {
  OriginatingSystemKey: string;
  StandardStatus: string;
  PropertyType: string;
  PropertySubType?: string;
  City?: string;
  StateOrProvince?: string;
  PostalCode?: string;
  BedroomsTotal?: number;
  BathroomsTotalInteger?: number;
  LivingArea?: number;
  LotSizeArea?: number;
  ListPrice?: number;
  UnparsedAddress?: string;
  Latitude?: number;
  Longitude?: number;
}
