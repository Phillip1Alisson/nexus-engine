import { Test, TestingModule } from '@nestjs/testing';
import { FastifyAdapter, NestFastifyApplication } from '@nestjs/platform-fastify';
import { ValidationPipe, HttpStatus } from '@nestjs/common';
import request from 'supertest';
import { beforeEach, describe, it, expect, vi } from 'vitest';
import { AppModule } from '../src/app.module';
import { QueueService } from '../src/queue/queue.service';

describe('Webhooks Ingestion (E2E)', () => {
  let app: NestFastifyApplication;
  
  // Criação de um Mock limpo do serviço de mensagens Redis
  const mockQueueService = {
    enqueueStockDeduction: vi.fn().mockImplementation(() => Promise.resolve()),
  };

  beforeEach(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    })
      .overrideProvider(QueueService)
      .useValue(mockQueueService) // Injeta o Mock anulando a necessidade de um Redis físico rodando
      .compile();

    app = moduleFixture.createNestApplication<NestFastifyApplication>(new FastifyAdapter());
    
    // Ativa as mesmas validações do main.ts nos testes
    app.useGlobalPipes(
      new ValidationPipe({
        whitelist: true,
        transform: true,
        forbidNonWhitelisted: true,
      }),
    );

    await app.init();
    await app.getHttpServer().ready(); // Sincroniza o Fastify
  });

  it('deve aceitar um webhook válido e responder com status 202 ACCEPTED', async () => {
    const validPayload = {
      tenantId: 'd3b07384-d113-4956-bc7e-aa45a6c382de', // UUID v4 válido
      productId: 'peca-cambio-01',
      quantity: 5,
      messageEventId: 'evt_123456',
    };

    const response = await request(app.getHttpServer())
      .post('/webhooks/messages')
      .send(validPayload);

    expect(response.status).toBe(HttpStatus.ACCEPTED);
    expect(response.body).toHaveProperty('message');
    expect(response.body.eventId).toBe('evt_123456');
    
    // Garante que o gateway tentou empurrar a mensagem para a fila assíncrona
    expect(mockQueueService.enqueueStockDeduction).toHaveBeenCalledWith(expect.objectContaining({
      productId: 'peca-cambio-01',
      quantity: 5,
    }));
  });

  it('deve rejeitar payloads com tenantId fora do padrão UUID v4 com erro 400 BAD REQUEST', async () => {
    const invalidPayload = {
      tenantId: 'id-invalido-comum', // Viola o decorator IsUUID do DTO
      productId: 'peca-freio',
      quantity: 2,
      messageEventId: 'evt_999',
    };

    const response = await request(app.getHttpServer())
      .post('/webhooks/messages')
      .send(invalidPayload);

    expect(response.status).toBe(HttpStatus.BAD_REQUEST);
    expect(response.body.message).toContain('O tenant_id deve ser un UUID v4 válido.');
  });

  it('deve rejeitar payloads com quantidades zeradas ou negativas', async () => {
    const invalidPayload = {
      tenantId: 'd3b07384-d113-4956-bc7e-aa45a6c382de',
      productId: 'oleo-motor',
      quantity: 0, // Viola o decorator Min(1) do DTO
      messageEventId: 'evt_888',
    };

    const response = await request(app.getHttpServer())
      .post('/webhooks/messages')
      .send(invalidPayload);

    expect(response.status).toBe(HttpStatus.BAD_REQUEST);
  });
});
