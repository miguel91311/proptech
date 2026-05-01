import { Controller, Get, Res } from '@nestjs/common';
import { OpenImmoService } from './openimmo.service';
import type { Response } from 'express';
import { Public } from '../core/decorators/public.decorator';

@Controller('exports')
export class OpenImmoController {
  constructor(private readonly openImmoService: OpenImmoService) {}

  @Get('openimmo.xml')
  @Public()
  async exportXml(@Res() res: Response) {
    const xml = await this.openImmoService.generateOpenImmoXml();
    res.setHeader('Content-Type', 'application/xml');
    res.setHeader('Content-Disposition', 'attachment; filename="openimmo-export.xml"');
    res.send(xml);
  }
}
