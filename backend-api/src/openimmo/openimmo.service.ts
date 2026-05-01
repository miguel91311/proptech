import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class OpenImmoService {
  constructor(private readonly prisma: PrismaService) {}

  async generateOpenImmoXml() {
    const properties = await this.prisma.property.findMany();

    const xml = `<?xml version="1.0" encoding="UTF-8"?>
<openimmo>
  <uebertragung art="ONLINE" umfang="VOLL" modus="NEW" version="1.2.7" sendersoftware="PropTech-PT" senderversion="1.0"/>
  <anbieter>
    <anbieternr>PT001</anbieternr>
    <firma>PropTech Portugal</firma>
    <immobilie>
      ${properties.map((p) => this.toOpenImmoObject(p)).join('\n')}
    </immobilie>
  </anbieter>
</openimmo>`;

    return xml;
  }

  private toOpenImmoObject(p: any) {
    return `
      <objekt>
        <objektnr_intern>${p.ListingKey}</objektnr_intern>
        <objektnr_extern>${p.OriginatingSystemKey}</objektnr_extern>
        <objektart>
          <objektart_zusammen>${this.mapType(p.PropertyType)}</objektart_zusammen>
        </objektart>
        <geo>
          <plz>${p.PostalCode || ''}</plz>
          <ort>${p.City || ''}</ort>
          <strasse>${p.StreetAddress || ''}</strasse>
          <land iso_land="PRT"/>
        </geo>
        <kontaktperson>
          <name>PropTech Portugal</name>
        </kontaktperson>
        <preise>
          <kaufpreis>${p.ListPrice || 0}</kaufpreis>
          <waehrung iso_waehrung="EUR"/>
        </preise>
        <flaechen>
          <wohnflaeche>${p.LivingArea || 0}</wohnflaeche>
          <grundstuecksflaeche>${p.LotSizeArea || 0}</grundstuecksflaeche>
        </flaechen>
        <ausstattung>
          <zimmer>${p.BedroomsTotal || 0}</zimmer>
          <anzahl_badezimmer>${p.BathroomsTotalInteger || 0}</anzahl_badezimmer>
        </ausstattung>
        <zustand_angaben>
          <baujahr>${new Date(p.ListingContractDate || Date.now()).getFullYear()}</baujahr>
          <zustand>${this.mapCondition(p.StandardStatus)}</zustand>
        </zustand_angaben>
      </objekt>`;
  }

  private mapType(type: string) {
    const map: Record<string, string> = { Residential: 'WOHNUNG', Commercial: 'GEWERBE_IMMUBLIE', Land: 'GRUNDSTUECK' };
    return map[type] || 'WOHNUNG';
  }

  private mapCondition(status: string) {
    const map: Record<string, string> = { Active: 'GEPFLEGT', Pending: 'BAUFAELLIG', Closed: 'NEU' };
    return map[status] || 'GEPFLEGT';
  }
}
