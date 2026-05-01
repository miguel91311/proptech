import { Controller, Post, Get, Body, UseGuards, Request, Param } from '@nestjs/common';
import { KycService } from './kyc.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth/jwt-auth.guard';
import { Roles } from '../core/decorators/roles.decorator';

class SubmitKycDto {
  documentUrl: string;
  selfieUrl: string;
}

@Controller('kyc')
export class KycController {
  constructor(private readonly kycService: KycService) {}

  @Post('submit')
  @UseGuards(JwtAuthGuard)
  async submit(@Body() dto: SubmitKycDto, @Request() req: any) {
    return this.kycService.submit(req.user.id, dto.documentUrl, dto.selfieUrl);
  }

  @Get('status')
  @UseGuards(JwtAuthGuard)
  async getStatus(@Request() req: any) {
    return this.kycService.getStatus(req.user.id);
  }

  @Get()
  @UseGuards(JwtAuthGuard)
  @Roles('Platform Admin')
  async findAll() {
    return this.kycService.findAll();
  }

  @Post(':userId/verify')
  @UseGuards(JwtAuthGuard)
  @Roles('Platform Admin')
  async verify(@Param('userId') userId: string, @Request() req: any) {
    return this.kycService.verify(userId, req.user);
  }
}
