import { Injectable, ForbiddenException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class KycService {
  constructor(private readonly prisma: PrismaService) {}

  async submit(userId: string, documentUrl: string, selfieUrl: string) {
    return this.prisma.kycRequest.upsert({
      where: { userId },
      update: { documentUrl, selfieUrl, status: 'pending', updatedAt: new Date() },
      create: { userId, documentUrl, selfieUrl, status: 'pending' },
    });
  }

  async getStatus(userId: string) {
    const record = await this.prisma.kycRequest.findUnique({ where: { userId } });
    if (!record) return { status: 'not_submitted' };
    return record;
  }

  async findAll() {
    return this.prisma.kycRequest.findMany({
      include: { user: { select: { id: true, name: true, email: true } } },
      orderBy: { createdAt: 'desc' },
    });
  }

  async verify(targetUserId: string, adminUser: any) {
    if (adminUser.role?.name !== 'Platform Admin') {
      throw new ForbiddenException('Apenas Platform Admin pode verificar KYC.');
    }
    return this.prisma.kycRequest.update({
      where: { userId: targetUserId },
      data: { status: 'verified', verifiedBy: adminUser.id, verifiedAt: new Date() },
    });
  }
}
