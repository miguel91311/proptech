import { Test, TestingModule } from '@nestjs/testing';
import { AuthService } from './auth.service';
import { PrismaService } from '../prisma/prisma.service';
import { JwtService } from '@nestjs/jwt';
import { ConfigService } from '@nestjs/config';
import {
  UnauthorizedException,
  ConflictException,
  ForbiddenException,
} from '@nestjs/common';
import * as bcrypt from 'bcrypt';

jest.mock('bcrypt');

describe('AuthService', () => {
  let service: AuthService;
  let prisma: PrismaService;

  const mockPrisma = {
    user: {
      findUnique: jest.fn(),
      create: jest.fn(),
    },
    role: {
      findUnique: jest.fn(),
    },
  };

  const mockJwtService = {
    sign: jest.fn().mockReturnValue('mocked-jwt-token'),
  };

  const mockConfigService = {
    get: jest.fn((key: string) => {
      if (key === 'JWT_SECRET') return 'test-secret';
      if (key === 'JWT_EXPIRATION') return '1h';
      return null;
    }),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        AuthService,
        { provide: PrismaService, useValue: mockPrisma },
        { provide: JwtService, useValue: mockJwtService },
        { provide: ConfigService, useValue: mockConfigService },
      ],
    }).compile();

    service = module.get<AuthService>(AuthService);
    prisma = module.get<PrismaService>(PrismaService);
    jest.clearAllMocks();
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });

  describe('validateUser', () => {
    it('should return user without password if credentials are valid', async () => {
      const user = {
        id: '1',
        email: 'test@test.com',
        passwordHash: 'hashed',
        name: 'Test',
        role: { id: 1, name: 'Operator' },
      };
      mockPrisma.user.findUnique.mockResolvedValue(user);
      (bcrypt.compare as jest.Mock).mockResolvedValue(true);

      const result = await service.validateUser('test@test.com', 'password');
      expect(result).toEqual({
        id: '1',
        email: 'test@test.com',
        name: 'Test',
        role: { id: 1, name: 'Operator' },
      });
    });

    it('should throw UnauthorizedException if user not found', async () => {
      mockPrisma.user.findUnique.mockResolvedValue(null);
      await expect(service.validateUser('x@x.com', 'pass')).rejects.toThrow(
        UnauthorizedException,
      );
    });

    it('should throw UnauthorizedException if password is invalid', async () => {
      mockPrisma.user.findUnique.mockResolvedValue({ passwordHash: 'hash' });
      (bcrypt.compare as jest.Mock).mockResolvedValue(false);
      await expect(service.validateUser('x@x.com', 'pass')).rejects.toThrow(
        UnauthorizedException,
      );
    });
  });

  describe('login', () => {
    it('should return access_token and user', async () => {
      const user = {
        id: '1',
        email: 'test@test.com',
        name: 'Test',
        role: { name: 'Operator' },
      };
      jest.spyOn(service, 'validateUser').mockResolvedValue(user as any);

      const result = await service.login('test@test.com', 'password');
      expect(result.access_token).toBe('mocked-jwt-token');
      expect(result.user.email).toBe('test@test.com');
    });
  });

  describe('register', () => {
    it('should create a new user when admin requests it', async () => {
      mockPrisma.user.findUnique.mockResolvedValue(null);
      mockPrisma.role.findUnique.mockResolvedValue({ id: 1, name: 'Operator' });
      (bcrypt.hash as jest.Mock).mockResolvedValue('newHash');
      mockPrisma.user.create.mockResolvedValue({
        id: '2',
        email: 'new@test.com',
        name: 'New',
        role: { id: 1, name: 'Operator' },
        passwordHash: 'newHash',
      });

      const admin = { role: { name: 'Platform Admin' } };
      const result = await service.register(
        'new@test.com',
        'pass',
        'New',
        'Operator',
        admin,
      );
      expect(result.email).toBe('new@test.com');
    });

    it('should throw ForbiddenException if non-admin tries to register', async () => {
      const user = { role: { name: 'Operator' } };
      await expect(
        service.register('a@a.com', 'pass', 'A', 'Operator', user),
      ).rejects.toThrow(ForbiddenException);
    });

    it('should throw ConflictException if email exists', async () => {
      mockPrisma.user.findUnique.mockResolvedValue({ id: '1' });
      const admin = { role: { name: 'Platform Admin' } };
      await expect(
        service.register('a@a.com', 'pass', 'A', 'Operator', admin),
      ).rejects.toThrow(ConflictException);
    });
  });

  describe('getProfile', () => {
    it('should return user profile without password', async () => {
      mockPrisma.user.findUnique.mockResolvedValue({
        id: '1',
        email: 'test@test.com',
        name: 'Test',
        passwordHash: 'secret',
        role: { id: 1, name: 'Operator' },
      });

      const result = await service.getProfile('1');
      expect(result).not.toHaveProperty('passwordHash');
      expect(result.email).toBe('test@test.com');
    });

    it('should throw UnauthorizedException if user not found', async () => {
      mockPrisma.user.findUnique.mockResolvedValue(null);
      await expect(service.getProfile('99')).rejects.toThrow(
        UnauthorizedException,
      );
    });
  });
});
