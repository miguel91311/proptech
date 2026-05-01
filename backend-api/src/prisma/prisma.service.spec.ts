import { Test, TestingModule } from '@nestjs/testing';
import { PrismaService } from './prisma.service';
import { ConfigService } from '@nestjs/config';

jest.mock('pg', () => {
  return {
    Pool: jest.fn().mockImplementation(() => ({
      end: jest.fn().mockResolvedValue(undefined),
    })),
  };
});

jest.mock('@prisma/adapter-pg', () => {
  return {
    PrismaPg: jest.fn().mockImplementation(() => ({
      queryRaw: jest.fn(),
      query: jest.fn(),
    })),
  };
});

jest.mock('@prisma/client', () => {
  return {
    PrismaClient: jest.fn().mockImplementation(() => ({
      $connect: jest.fn().mockResolvedValue(undefined),
      $disconnect: jest.fn().mockResolvedValue(undefined),
    })),
  };
});

describe('PrismaService', () => {
  let service: PrismaService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        PrismaService,
        {
          provide: ConfigService,
          useValue: {
            get: jest
              .fn()
              .mockReturnValue('postgresql://test:test@localhost:5432/test'),
          },
        },
      ],
    }).compile();

    service = module.get<PrismaService>(PrismaService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
