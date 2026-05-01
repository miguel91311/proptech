import { Controller, Get, UseGuards } from '@nestjs/common';
import { ConsentsService } from './consents.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth/jwt-auth.guard';
import { Roles } from '../core/decorators/roles.decorator';

@Controller('consents')
export class ConsentsController {
  constructor(private readonly consentsService: ConsentsService) {}

  @Get()
  @UseGuards(JwtAuthGuard)
  @Roles('Platform Admin')
  async findAll() {
    return this.consentsService.findAll();
  }
}
