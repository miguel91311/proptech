import {
  IsString,
  IsOptional,
  IsNumber,
  IsNotEmpty,
  Min,
} from 'class-validator';

export class CreatePropertyDto {
  @IsString()
  @IsNotEmpty()
  OriginatingSystemKey: string;

  @IsString()
  @IsOptional()
  ListAOR?: string;

  @IsString()
  @IsNotEmpty()
  StandardStatus: string;

  @IsString()
  @IsNotEmpty()
  PropertyType: string;

  @IsString()
  @IsOptional()
  PropertySubType?: string;

  @IsString()
  @IsOptional()
  StreetAddress?: string;

  @IsString()
  @IsOptional()
  City?: string;

  @IsString()
  @IsOptional()
  StateOrProvince?: string;

  @IsString()
  @IsOptional()
  PostalCode?: string;

  @IsString()
  @IsOptional()
  Country?: string;

  @IsNumber()
  @IsOptional()
  Latitude?: number;

  @IsNumber()
  @IsOptional()
  Longitude?: number;

  @IsNumber()
  @IsOptional()
  @Min(0)
  BedroomsTotal?: number;

  @IsNumber()
  @IsOptional()
  @Min(0)
  BathroomsTotalInteger?: number;

  @IsNumber()
  @IsOptional()
  @Min(0)
  LivingArea?: number;

  @IsNumber()
  @IsOptional()
  @Min(0)
  LotSizeArea?: number;

  @IsNumber()
  @IsOptional()
  @Min(0)
  ListPrice?: number;

  @IsString()
  @IsOptional()
  Currency?: string;
}
