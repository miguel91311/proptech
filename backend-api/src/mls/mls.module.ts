import { Module } from '@nestjs/common';
import { HttpModule } from '@nestjs/axios';
import { MlsService } from './mls.service';

@Module({
  imports: [HttpModule],
  providers: [MlsService],
  exports: [MlsService],
})
export class MlsModule {}
