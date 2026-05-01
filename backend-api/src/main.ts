import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { ValidationPipe } from '@nestjs/common';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);

  const allowedOrigins = (
    process.env.CORS_ORIGINS || 'http://localhost:3001,http://127.0.0.1:3001'
  ).split(',');

  app.enableCors({
    origin: allowedOrigins,
    credentials: true,
    methods: 'GET,HEAD,PUT,PATCH,POST,DELETE',
    allowedHeaders: 'Content-Type, Accept, Authorization',
  });

  app.useGlobalPipes(
    new ValidationPipe({
      whitelist: true,
      forbidNonWhitelisted: true,
      transform: true,
    }),
  );

  const config = new DocumentBuilder()
    .setTitle('PropTech API - Painel Imobiliário')
    .setDescription(
      'A API de backend corporativa baseada no RESO Data Dictionary 2.0',
    )
    .setVersion('1.0')
    .addBearerAuth() // Suporte para JWT no painel do Swagger
    .build();

  const document = SwaggerModule.createDocument(app, config);
  SwaggerModule.setup('api', app, document); // Configura o endpoint /api

  await app.listen(process.env.PORT ?? 3000, '0.0.0.0');
}
bootstrap();
