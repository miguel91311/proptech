import { Controller, Post, Body, Get, UseGuards } from '@nestjs/common';
import { WebhooksService } from './webhooks.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth/jwt-auth.guard';
import { Roles } from '../core/decorators/roles.decorator';
import { Public } from '../core/decorators/public.decorator';

class DispatchDto {
  event: string;
  data: any;
}

@Controller('webhooks')
export class WebhooksController {
  constructor(private readonly webhooksService: WebhooksService) {}

  @Post('dispatch')
  @UseGuards(JwtAuthGuard)
  @Roles('Platform Admin')
  async dispatch(@Body() dto: DispatchDto) {
    return this.webhooksService.dispatch(dto.event, dto.data);
  }

  @Get('health')
  @Public()
  health() {
    return { status: 'ok' };
  }
}
