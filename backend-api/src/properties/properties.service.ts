import { Injectable, NotFoundException } from '@nestjs/common';
import { CreatePropertyDto } from './dto/create-property.dto';
import { UpdatePropertyDto } from './dto/update-property.dto';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class PropertiesService {
  constructor(private prisma: PrismaService) {}

  async create(createPropertyDto: CreatePropertyDto) {
    const { Latitude, Longitude, ...propertyData } = createPropertyDto;
    const property = await this.prisma.property.create({ data: propertyData });
    if (Latitude !== undefined && Longitude !== undefined) {
      await this.prisma.$executeRaw`
        UPDATE "Property" 
        SET "Location" = ST_SetSRID(ST_MakePoint(${Longitude}, ${Latitude}), 4326) 
        WHERE "ListingKey" = ${property.ListingKey}
      `;
    }
    return this.findOne(property.ListingKey);
  }

  async findAll(filters?: {
    city?: string;
    minPrice?: number;
    maxPrice?: number;
    minBeds?: number;
    maxBeds?: number;
    propertyType?: string;
  }) {
    const where: any = {};
    if (filters?.city) where.City = { contains: filters.city, mode: 'insensitive' };
    if (filters?.propertyType) where.PropertyType = filters.propertyType;
    if (filters?.minPrice !== undefined || filters?.maxPrice !== undefined) {
      where.ListPrice = {};
      if (filters.minPrice !== undefined) where.ListPrice.gte = filters.minPrice;
      if (filters.maxPrice !== undefined) where.ListPrice.lte = filters.maxPrice;
    }
    if (filters?.minBeds !== undefined || filters?.maxBeds !== undefined) {
      where.BedroomsTotal = {};
      if (filters.minBeds !== undefined) where.BedroomsTotal.gte = filters.minBeds;
      if (filters.maxBeds !== undefined) where.BedroomsTotal.lte = filters.maxBeds;
    }

    const properties = await this.prisma.property.findMany({ where, orderBy: { OriginalEntryTimestamp: 'desc' } });
    return properties;
  }

  async findOne(id: string) {
    const result = await this.prisma.$queryRaw<any[]>`
      SELECT *, ST_X("Location"::geometry) as "Longitude", ST_Y("Location"::geometry) as "Latitude" 
      FROM "Property" 
      WHERE "ListingKey" = ${id}
    `;
    if (!result || result.length === 0) {
      throw new NotFoundException(`Imóvel com ID ${id} não encontrado`);
    }
    return result[0];
  }

  async update(id: string, updatePropertyDto: UpdatePropertyDto) {
    const { Latitude, Longitude, ...propertyData } = updatePropertyDto;
    const updated = await this.prisma.property.update({
      where: { ListingKey: id },
      data: propertyData,
    });
    if (Latitude !== undefined && Longitude !== undefined) {
      await this.prisma.$executeRaw`
        UPDATE "Property" 
        SET "Location" = ST_SetSRID(ST_MakePoint(${Longitude}, ${Latitude}), 4326) 
        WHERE "ListingKey" = ${id}
      `;
    }
    return this.findOne(id);
  }

  remove(id: string) {
    return this.prisma.property.delete({ where: { ListingKey: id } });
  }

  async searchGeo(lat: number, lng: number, radiusKm: number) {
    const properties = await this.prisma.$queryRaw<any[]>`
      SELECT *, ST_Distance("Location"::geography, ST_SetSRID(ST_MakePoint(${lng}, ${lat}), 4326)::geography) / 1000 AS distance_km
      FROM "Property"
      WHERE ST_DWithin(
        "Location"::geography,
        ST_SetSRID(ST_MakePoint(${lng}, ${lat}), 4326)::geography,
        ${radiusKm * 1000}
      )
      ORDER BY distance_km ASC
    `;
    return properties;
  }

  // Para o frontend: retorna todos com lat/lng
  async findAllWithCoordinates(filters?: {
    city?: string;
    minPrice?: number;
    maxPrice?: number;
    minBeds?: number;
    maxBeds?: number;
    propertyType?: string;
  }) {
    const properties = await this.findAll(filters);
    // Batch enrich with coordinates
    const enriched = [];
    for (const p of properties) {
      const geo = await this.prisma.$queryRaw<any[]>`
        SELECT ST_X("Location"::geometry) as "Longitude", ST_Y("Location"::geometry) as "Latitude"
        FROM "Property" WHERE "ListingKey" = ${p.ListingKey}
      `;
      enriched.push({ ...p, Longitude: geo[0]?.Longitude ?? null, Latitude: geo[0]?.Latitude ?? null });
    }
    return enriched;
  }
}
