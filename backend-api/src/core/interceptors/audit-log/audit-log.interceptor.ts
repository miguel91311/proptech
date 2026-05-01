import {
  CallHandler,
  ExecutionContext,
  Injectable,
  NestInterceptor,
} from '@nestjs/common';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { PrismaService } from '../../../prisma/prisma.service';

@Injectable()
export class AuditLogInterceptor implements NestInterceptor {
  constructor(private readonly prisma: PrismaService) {}

  intercept(context: ExecutionContext, next: CallHandler): Observable<any> {
    const request = context.switchToHttp().getRequest();
    const method = request.method;

    if (!['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      return next.handle();
    }

    const user = request.user;
    const userId = user ? user.id : null;
    const route = request.originalUrl || request.url;
    const body = request.body;

    // Inferir entidade e ID da rota (ex: /properties/abc-123)
    const entityInfo = this.inferEntityFromRoute(route);

    // Buscar estado anterior se for update ou delete
    const beforePromise =
      ['PUT', 'PATCH', 'DELETE'].includes(method) && entityInfo
        ? this.fetchBeforeState(entityInfo.entity, entityInfo.id)
        : Promise.resolve(null);

    return next.handle().pipe(
      tap({
        next: async (responseBody) => {
          const before = await beforePromise;
          const delta = this.buildDelta(
            method,
            entityInfo,
            before,
            body,
            responseBody,
          );

          this.prisma.auditLog
            .create({
              data: {
                userId: userId,
                method: method,
                route: route,
                delta: delta,
              },
            })
            .catch((err) => {
              console.error('Falha ao registar Auditoria Forense:', err);
            });
        },
        error: async (err) => {
          const before = await beforePromise;
          const delta = this.buildDelta(
            method,
            entityInfo,
            before,
            body,
            null,
            err.message,
          );

          this.prisma.auditLog
            .create({
              data: {
                userId: userId,
                method: method,
                route: route,
                delta: delta,
              },
            })
            .catch((logErr) => {
              console.error(
                'Falha ao registar Auditoria Forense (erro):',
                logErr,
              );
            });
        },
      }),
    );
  }

  private inferEntityFromRoute(
    route: string,
  ): { entity: string; id: string } | null {
    // Padrão: /api/properties/:id ou /properties/:id
    const match = route.match(/\/(properties|users|roles)\/([^\/\?]+)/);
    if (match) {
      return { entity: match[1], id: match[2] };
    }
    return null;
  }

  private async fetchBeforeState(entity: string, id: string): Promise<any> {
    try {
      if (entity === 'properties') {
        const result = await this.prisma.$queryRaw<any[]>`
          SELECT *, ST_X("Location"::geometry) as "Longitude", ST_Y("Location"::geometry) as "Latitude"
          FROM "Property"
          WHERE "ListingKey" = ${id}
        `;
        return result && result.length > 0 ? result[0] : null;
      }
      if (entity === 'users') {
        return this.prisma.user.findUnique({
          where: { id },
          include: { role: true },
        });
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  private buildDelta(
    method: string,
    entityInfo: { entity: string; id: string } | null,
    before: any,
    requestBody: any,
    responseBody: any,
    errorMessage?: string,
  ) {
    const base = {
      action: method,
      entity: entityInfo?.entity || 'unknown',
      entityId: entityInfo?.id || null,
      timestamp: new Date().toISOString(),
    };

    if (errorMessage) {
      return { ...base, status: 'failed', error: errorMessage, requestBody };
    }

    if (method === 'POST') {
      return { ...base, status: 'created', after: responseBody || requestBody };
    }

    if (method === 'DELETE') {
      return { ...base, status: 'deleted', before };
    }

    // PUT / PATCH
    return {
      ...base,
      status: 'updated',
      before,
      after: responseBody || requestBody,
    };
  }
}
