import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { ThrottlerModule } from '@nestjs/throttler';
import { APP_GUARD } from '@nestjs/core';
import { ThrottlerGuard } from '@nestjs/throttler';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { PrismaModule } from './prisma/prisma.module';
import { CoreModule } from './core/core.module';
import { AuthModule } from './auth/auth.module';
import { PropertiesModule } from './properties/properties.module';
import { MlsModule } from './mls/mls.module';
import { KycModule } from './kyc/kyc.module';
import { OpenImmoModule } from './openimmo/openimmo.module';
import { WebhooksModule } from './webhooks/webhooks.module';
import { UsersModule } from './users/users.module';
import { AuditLogsModule } from './audit-logs/audit-logs.module';
import { ConsentsModule } from './consents/consents.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    ThrottlerModule.forRoot([
      {
        name: 'default',
        ttl: 60000, // 1 minuto
        limit: 100, // 100 requests por minuto
      },
      {
        name: 'auth',
        ttl: 60000,
        limit: 20, // 20 login attempts por minuto
      },
    ]),
    PrismaModule,
    CoreModule,
    AuthModule,
    PropertiesModule,
    MlsModule,
    KycModule,
    OpenImmoModule,
    WebhooksModule,
    UsersModule,
    AuditLogsModule,
    ConsentsModule,
  ],
  controllers: [AppController],
  providers: [
    AppService,
    {
      provide: APP_GUARD,
      useClass: ThrottlerGuard,
    },
  ],
})
export class AppModule {}
