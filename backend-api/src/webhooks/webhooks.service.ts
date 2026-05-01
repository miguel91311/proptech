import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

interface WebhookPayload {
  event: string;
  data: any;
  timestamp: string;
}

@Injectable()
export class WebhooksService {
  constructor(private readonly prisma: PrismaService) {}

  async dispatch(event: string, data: any) {
    const payload: WebhookPayload = { event, data, timestamp: new Date().toISOString() };
    // Log para audit trail; em produção faria HTTP POST para URLs subscritas
    await this.prisma.auditLog.create({
      data: {
        userId: null,
        method: 'WEBHOOK',
        route: event,
        delta: payload as any,
      },
    });
    return { dispatched: true, event, timestamp: payload.timestamp };
  }
}
