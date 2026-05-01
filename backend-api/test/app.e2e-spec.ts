import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import request from 'supertest';
import { AppModule } from './../src/app.module';
import { PrismaService } from './../src/prisma/prisma.service';
import * as bcrypt from 'bcrypt';

describe('AppController (e2e)', () => {
  let app: INestApplication;
  let prisma: PrismaService;
  let authToken: string;

  beforeAll(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();

    app = moduleFixture.createNestApplication();
    await app.init();

    prisma = app.get<PrismaService>(PrismaService);

    // Cleanup
    await prisma.auditLog.deleteMany();
    await prisma.user.deleteMany();
    await prisma.role.deleteMany();
    await prisma.property.deleteMany();

    // Seed roles
    const adminRole = await prisma.role.create({
      data: { name: 'Platform Admin', description: 'Admin' },
    });
    const operatorRole = await prisma.role.create({
      data: { name: 'Operator', description: 'Operator' },
    });

    // Seed admin user
    const passwordHash = await bcrypt.hash('Admin123!', 12);
    await prisma.user.create({
      data: {
        email: 'admin@proptech.local',
        passwordHash,
        name: 'Admin',
        roleId: adminRole.id,
      },
    });

    // Seed operator user
    const opPasswordHash = await bcrypt.hash('Operator123!', 12);
    await prisma.user.create({
      data: {
        email: 'operator@proptech.local',
        passwordHash: opPasswordHash,
        name: 'Operator',
        roleId: operatorRole.id,
      },
    });
  });

  afterAll(async () => {
    await app.close();
  });

  describe('Auth', () => {
    it('/auth/login (POST) - should return JWT for valid credentials', async () => {
      const res = await request(app.getHttpServer())
        .post('/auth/login')
        .send({ email: 'admin@proptech.local', password: 'Admin123!' })
        .expect(201);

      expect(res.body.access_token).toBeDefined();
      authToken = res.body.access_token;
      expect(res.body.user.email).toBe('admin@proptech.local');
      expect(res.body.user.role).toBe('Platform Admin');
    });

    it('/auth/login (POST) - should reject invalid credentials', async () => {
      await request(app.getHttpServer())
        .post('/auth/login')
        .send({ email: 'admin@proptech.local', password: 'wrong' })
        .expect(401);
    });

    it('/auth/profile (GET) - should require authentication', async () => {
      await request(app.getHttpServer()).get('/auth/profile').expect(401);
    });

    it('/auth/profile (GET) - should return profile with valid token', async () => {
      const res = await request(app.getHttpServer())
        .get('/auth/profile')
        .set('Authorization', `Bearer ${authToken}`)
        .expect(200);

      expect(res.body.email).toBe('admin@proptech.local');
    });
  });

  describe('Properties', () => {
    let propertyId: string;

    it('/properties (POST) - should create property when authenticated', async () => {
      const res = await request(app.getHttpServer())
        .post('/properties')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          OriginatingSystemKey: 'E2E-TEST-001',
          StandardStatus: 'Active',
          PropertyType: 'Residential',
          ListPrice: 350000,
          City: 'Lisboa',
        })
        .expect(201);

      propertyId = res.body.ListingKey;
      expect(propertyId).toBeDefined();
      expect(res.body.OriginatingSystemKey).toBe('E2E-TEST-001');
    });

    it('/properties (GET) - should return properties', async () => {
      const res = await request(app.getHttpServer())
        .get('/properties')
        .expect(200);
      expect(Array.isArray(res.body)).toBe(true);
      expect(res.body.length).toBeGreaterThan(0);
    });

    it('/properties/:id (GET) - should return single property', async () => {
      const res = await request(app.getHttpServer())
        .get(`/properties/${propertyId}`)
        .expect(200);

      expect(res.body.ListingKey).toBe(propertyId);
    });

    it('/properties/:id (PATCH) - should update property', async () => {
      const res = await request(app.getHttpServer())
        .patch(`/properties/${propertyId}`)
        .set('Authorization', `Bearer ${authToken}`)
        .send({ ListPrice: 400000 })
        .expect(200);

      expect(res.body.ListPrice).toBe(400000);
    });

    it('/properties/:id (DELETE) - should forbid Operator from deleting', async () => {
      // Login as operator
      const opLogin = await request(app.getHttpServer())
        .post('/auth/login')
        .send({ email: 'operator@proptech.local', password: 'Operator123!' })
        .expect(201);

      const opToken = opLogin.body.access_token;

      await request(app.getHttpServer())
        .delete(`/properties/${propertyId}`)
        .set('Authorization', `Bearer ${opToken}`)
        .expect(403);
    });

    it('/properties/:id (DELETE) - should allow Admin to delete', async () => {
      await request(app.getHttpServer())
        .delete(`/properties/${propertyId}`)
        .set('Authorization', `Bearer ${authToken}`)
        .expect(200);
    });
  });

  describe('Audit Logs', () => {
    it('should have created audit logs for mutations', async () => {
      const logs = await prisma.auditLog.findMany({
        where: { route: { contains: '/properties' } },
      });

      expect(logs.length).toBeGreaterThanOrEqual(3); // POST, PATCH, DELETE
    });
  });
});
