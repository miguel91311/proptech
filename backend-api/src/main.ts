import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { ValidationPipe } from '@nestjs/common';

async function bootstrap() {
  try {
    console.log('🚀 Starting NestJS application...');
    console.log('📍 PORT:', process.env.PORT ?? 3000);
    console.log('🌍 NODE_ENV:', process.env.NODE_ENV);
    console.log('🔌 DATABASE_URL exists:', !!process.env.DATABASE_URL);

    const app = await NestFactory.create(AppModule);
    console.log('✅ NestJS app created');

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
      .addBearerAuth()
      .build();

    const document = SwaggerModule.createDocument(app, config);
    SwaggerModule.setup('api', app, document);

    const port = process.env.PORT ?? 3000;
    await app.listen(port, '0.0.0.0');
    console.log(`🎯 Application is running on: http://0.0.0.0:${port}`);
  } catch (error) {
    console.error('💥 FATAL ERROR during startup:', error);
    process.exit(1);
  }
}
bootstrap();
