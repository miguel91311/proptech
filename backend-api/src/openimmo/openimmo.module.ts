import { Module } from '@nestjs/common';
import { OpenImmoController } from './openimmo.controller';
import { OpenImmoService } from './openimmo.service';
import { PrismaModule } from '../prisma/prisma.module';

@Module({
  imports: [PrismaModule],
  controllers: [OpenImmoController],
  providers: [OpenImmoService],
})
export class OpenImmoModule {}
