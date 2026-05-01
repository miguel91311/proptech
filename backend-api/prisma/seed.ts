import { PrismaClient } from '@prisma/client';
import { PrismaPg } from '@prisma/adapter-pg';
import { Pool } from 'pg';
import * as bcrypt from 'bcrypt';
import * as dotenv from 'dotenv';

dotenv.config();

const connectionString = process.env.DATABASE_URL!;
const pool = new Pool({ connectionString });
const adapter = new PrismaPg(pool);
const prisma = new PrismaClient({ adapter });

const PORTUGAL_PROPERTIES = [
  {
    OriginatingSystemKey: 'SEED-LIS-001',
    StandardStatus: 'Active',
    PropertyType: 'Residential',
    PropertySubType: 'Apartamento',
    StreetAddress: 'Rua Augusta 150, 3º Esq.',
    City: 'Lisboa',
    StateOrProvince: 'Lisboa',
    PostalCode: '1100-053',
    Latitude: 38.7139,
    Longitude: -9.1394,
    BedroomsTotal: 2,
    BathroomsTotalInteger: 1,
    LivingArea: 78,
    ListPrice: 385000,
    ListingContractDate: new Date('2026-03-15'),
    ExpirationDate: new Date('2026-09-15'),
  },
  {
    OriginatingSystemKey: 'SEED-CAS-002',
    StandardStatus: 'Active',
    PropertyType: 'Residential',
    PropertySubType: 'Moradia',
    StreetAddress: 'Avenida Marginal 42',
    City: 'Cascais',
    StateOrProvince: 'Lisboa',
    PostalCode: '2750-642',
    Latitude: 38.697,
    Longitude: -9.422,
    BedroomsTotal: 4,
    BathroomsTotalInteger: 3,
    LivingArea: 240,
    ListPrice: 1250000,
    ListingContractDate: new Date('2026-02-10'),
    ExpirationDate: new Date('2026-08-10'),
  },
  {
    OriginatingSystemKey: 'SEED-PRT-003',
    StandardStatus: 'Active',
    PropertyType: 'Residential',
    PropertySubType: 'Apartamento',
    StreetAddress: 'Rua de Cedofeita 88',
    City: 'Porto',
    StateOrProvince: 'Porto',
    PostalCode: '4050-175',
    Latitude: 41.15,
    Longitude: -8.61,
    BedroomsTotal: 1,
    BathroomsTotalInteger: 1,
    LivingArea: 52,
    ListPrice: 210000,
    ListingContractDate: new Date('2026-04-01'),
    ExpirationDate: new Date('2026-10-01'),
  },
  {
    OriginatingSystemKey: 'SEED-SIN-004',
    StandardStatus: 'Active',
    PropertyType: 'Residential',
    PropertySubType: 'Moradia',
    StreetAddress: 'Rua da Ferraria 12',
    City: 'Sintra',
    StateOrProvince: 'Lisboa',
    PostalCode: '2710-616',
    Latitude: 38.798,
    Longitude: -9.388,
    BedroomsTotal: 3,
    BathroomsTotalInteger: 2,
    LivingArea: 160,
    ListPrice: 650000,
    ListingContractDate: new Date('2026-01-20'),
    ExpirationDate: new Date('2026-07-20'),
  },
  {
    OriginatingSystemKey: 'SEED-ALV-005',
    StandardStatus: 'Active',
    PropertyType: 'Residential',
    PropertySubType: 'Apartamento',
    StreetAddress: 'Avenida dos Descobrimentos 200',
    City: 'Albufeira',
    StateOrProvince: 'Faro',
    PostalCode: '8200-145',
    Latitude: 37.0891,
    Longitude: -8.2479,
    BedroomsTotal: 2,
    BathroomsTotalInteger: 2,
    LivingArea: 95,
    ListPrice: 320000,
    ListingContractDate: new Date('2026-03-01'),
    ExpirationDate: new Date('2026-09-01'),
  },
  {
    OriginatingSystemKey: 'SEED-CBR-006',
    StandardStatus: 'Active',
    PropertyType: 'Residential',
    PropertySubType: 'Apartamento',
    StreetAddress: 'Rua Ferreira Borges 45',
    City: 'Coimbra',
    StateOrProvince: 'Coimbra',
    PostalCode: '3000-180',
    Latitude: 40.2033,
    Longitude: -8.4103,
    BedroomsTotal: 3,
    BathroomsTotalInteger: 2,
    LivingArea: 110,
    ListPrice: 275000,
    ListingContractDate: new Date('2026-04-10'),
    ExpirationDate: new Date('2026-10-10'),
  },
];

async function main() {
  // Roles
  for (const name of ['Platform Admin', 'Application Owner', 'Operator']) {
    await prisma.role.upsert({ where: { name }, update: {}, create: { name, description: name } });
  }

  // Admin user
  const adminRole = await prisma.role.findUnique({ where: { name: 'Platform Admin' } });
  if (adminRole) {
    await prisma.user.upsert({
      where: { email: 'admin@proptech.local' },
      update: {},
      create: { email: 'admin@proptech.local', passwordHash: await bcrypt.hash('Admin123!', 12), name: 'Admin', roleId: adminRole.id },
    });
  }

  // Seed properties with PostGIS coordinates
  for (const prop of PORTUGAL_PROPERTIES as any[]) {
    const existing = await prisma.property.findUnique({ where: { OriginatingSystemKey: prop.OriginatingSystemKey } });
    if (existing) continue;

    const { Latitude, Longitude, ...data } = prop;

    const created = await prisma.property.create({
      data: { ...data, Currency: 'EUR', Country: 'PT' },
    });

    // Update geometry with raw query
    await prisma.$executeRaw`SET search_path TO public, topology;`;
    await prisma.$executeRaw`
      UPDATE "Property" 
      SET "Location" = ST_SetSRID(ST_MakePoint(${Longitude}, ${Latitude}), 4326) 
      WHERE "ListingKey" = ${created.ListingKey}
    `;
  }

  console.log(`Seeded ${PORTUGAL_PROPERTIES.length} properties.`);
}

main()
  .catch((e) => { console.error(e); process.exit(1); })
  .finally(async () => { await prisma.$disconnect(); });
