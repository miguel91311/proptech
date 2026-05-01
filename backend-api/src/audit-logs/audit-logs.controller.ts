import { Controller, Get, UseGuards } from '@nestjs/common';
import { AuditLogsService } from './audit-logs.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth/jwt-auth.guard';
import { Roles } from '../core/decorators/roles.decorator';

@Controller('audit-logs')
export class AuditLogsController {
  constructor(private readonly auditLogsService: AuditLogsService) {}

  @Get()
  @UseGuards(JwtAuthGuard)
  @Roles('Platform Admin')
  async findAll() {
    return this.auditLogsService.findAll();
  }
}
