import { Module } from '@nestjs/common';
import { APP_GUARD, APP_INTERCEPTOR } from '@nestjs/core';
import { JwtAuthGuard } from '../auth/guards/jwt-auth/jwt-auth.guard';
import { RolesGuard } from './guards/roles/roles.guard';
import { AuditLogInterceptor } from './interceptors/audit-log/audit-log.interceptor';
import { CacheModule } from './cache/cache.module';
import { CacheInterceptor } from './cache/cache.interceptor';

@Module({
  imports: [CacheModule],
  providers: [
    {
      provide: APP_GUARD,
      useClass: JwtAuthGuard, // Autenticação JWT global (Zero Trust)
    },
    {
      provide: APP_GUARD,
      useClass: RolesGuard, // Autorização RBAC global
    },
    {
      provide: APP_INTERCEPTOR,
      useClass: AuditLogInterceptor, // Auditoria forense global
    },
    {
      provide: APP_INTERCEPTOR,
      useClass: CacheInterceptor, // Cache em memória para GETs
    },
  ],
})
export class CoreModule { }
