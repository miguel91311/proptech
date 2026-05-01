import { Controller, Get, Post, Body, Patch, Param, Delete, Query, UseGuards } from '@nestjs/common';
import { PropertiesService } from './properties.service';
import { CreatePropertyDto } from './dto/create-property.dto';
import { UpdatePropertyDto } from './dto/update-property.dto';
import { Roles } from '../core/decorators/roles.decorator';
import { Public } from '../core/decorators/public.decorator';

@Controller('properties')
export class PropertiesController {
  constructor(private readonly propertiesService: PropertiesService) {}

  @Post()
  @Roles('Platform Admin', 'Application Owner', 'Operator')
  create(@Body() createPropertyDto: CreatePropertyDto) {
    return this.propertiesService.create(createPropertyDto);
  }

  @Get()
  @Public()
  findAll(
    @Query('city') city?: string,
    @Query('minPrice') minPrice?: string,
    @Query('maxPrice') maxPrice?: string,
    @Query('minBeds') minBeds?: string,
    @Query('maxBeds') maxBeds?: string,
    @Query('propertyType') propertyType?: string,
    @Query('withCoords') withCoords?: string,
  ) {
    const filters = {
      city,
      minPrice: minPrice ? +minPrice : undefined,
      maxPrice: maxPrice ? +maxPrice : undefined,
      minBeds: minBeds ? +minBeds : undefined,
      maxBeds: maxBeds ? +maxBeds : undefined,
      propertyType,
    };
    if (withCoords === 'true') {
      return this.propertiesService.findAllWithCoordinates(filters);
    }
    return this.propertiesService.findAll(filters);
  }

  @Get('geo-search')
  @Public()
  searchGeo(
    @Query('lat') lat: string,
    @Query('lng') lng: string,
    @Query('radius') radiusKm: string,
  ) {
    return this.propertiesService.searchGeo(+lat, +lng, +radiusKm);
  }

  @Get(':id')
  @Public()
  findOne(@Param('id') id: string) {
    return this.propertiesService.findOne(id);
  }

  @Patch(':id')
  @Roles('Platform Admin', 'Application Owner', 'Operator')
  update(@Param('id') id: string, @Body() updatePropertyDto: UpdatePropertyDto) {
    return this.propertiesService.update(id, updatePropertyDto);
  }

  @Delete(':id')
  @Roles('Platform Admin', 'Application Owner')
  remove(@Param('id') id: string) {
    return this.propertiesService.remove(id);
  }
}
