import { Test, TestingModule } from '@nestjs/testing';
import { PropertiesService } from './properties.service';
import { PrismaService } from '../prisma/prisma.service';
import { NotFoundException } from '@nestjs/common';

describe('PropertiesService', () => {
  let service: PropertiesService;
  let prisma: PrismaService;

  const mockPrisma = {
    property: {
      create: jest.fn(),
      findMany: jest.fn(),
      update: jest.fn(),
      delete: jest.fn(),
    },
    $executeRaw: jest.fn(),
    $queryRaw: jest.fn(),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        PropertiesService,
        { provide: PrismaService, useValue: mockPrisma },
      ],
    }).compile();

    service = module.get<PropertiesService>(PropertiesService);
    prisma = module.get<PrismaService>(PrismaService);
    jest.clearAllMocks();
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });

  describe('create', () => {
    it('should create property without coordinates', async () => {
      const dto = {
        OriginatingSystemKey: 'MLS-1',
        StandardStatus: 'Active',
        PropertyType: 'Residential',
      };
      mockPrisma.property.create.mockResolvedValue({
        ListingKey: 'uuid-1',
        ...dto,
      });

      const result = await service.create(dto);
      expect(result.ListingKey).toBe('uuid-1');
      expect(mockPrisma.property.create).toHaveBeenCalledWith({ data: dto });
      expect(mockPrisma.$executeRaw).not.toHaveBeenCalled();
    });

    it('should create property with PostGIS coordinates', async () => {
      const dto = {
        OriginatingSystemKey: 'MLS-2',
        StandardStatus: 'Active',
        PropertyType: 'Residential',
        Latitude: 38.7,
        Longitude: -9.1,
      };
      mockPrisma.property.create.mockResolvedValue({ ListingKey: 'uuid-2' });
      mockPrisma.$queryRaw.mockResolvedValue([
        { ListingKey: 'uuid-2', Latitude: 38.7, Longitude: -9.1 },
      ]);

      const result = await service.create(dto);
      expect(mockPrisma.$executeRaw).toHaveBeenCalled();
      expect(result).toBeDefined();
    });
  });

  describe('findAll', () => {
    it('should return array of properties', async () => {
      const properties = [{ ListingKey: '1' }, { ListingKey: '2' }];
      mockPrisma.property.findMany.mockResolvedValue(properties);

      const result = await service.findAll();
      expect(result).toHaveLength(2);
    });
  });

  describe('findOne', () => {
    it('should return property with coordinates', async () => {
      mockPrisma.$queryRaw.mockResolvedValue([
        { ListingKey: '1', Longitude: -9.1, Latitude: 38.7 },
      ]);
      const result = await service.findOne('1');
      expect(result.ListingKey).toBe('1');
    });

    it('should throw NotFoundException if property not found', async () => {
      mockPrisma.$queryRaw.mockResolvedValue([]);
      await expect(service.findOne('99')).rejects.toThrow(NotFoundException);
    });
  });

  describe('update', () => {
    it('should update property', async () => {
      const dto = { ListPrice: 500000 };
      mockPrisma.property.update.mockResolvedValue({ ListingKey: '1', ...dto });

      const result = await service.update('1', dto);
      expect(result.ListPrice).toBe(500000);
    });
  });

  describe('remove', () => {
    it('should delete property', async () => {
      mockPrisma.property.delete.mockResolvedValue({ ListingKey: '1' });
      const result = await service.remove('1');
      expect(result.ListingKey).toBe('1');
    });
  });

  describe('searchGeo', () => {
    it('should return properties within radius', async () => {
      const properties = [{ ListingKey: '1', distance_km: 1.5 }];
      mockPrisma.$queryRaw.mockResolvedValue(properties);

      const result = await service.searchGeo(38.7, -9.1, 5);
      expect(result).toHaveLength(1);
      expect(mockPrisma.$queryRaw).toHaveBeenCalled();
    });
  });
});
