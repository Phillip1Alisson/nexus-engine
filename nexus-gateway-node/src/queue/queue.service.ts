import { Injectable, Inject, Logger } from '@nestjs/common';
import { ClientProxy } from '@nestjs/microservices';
import { lastValueFrom } from 'rxjs';
import { IncomingMessageDto } from '../webhooks/dtos/incoming-message.dto';

@Injectable()
export class QueueService {
  private readonly logger = new Logger(QueueService.name);

  constructor(
    @Inject('REDIS_SERVICE') private readonly redisClient: ClientProxy,
  ) {}

  /**
   * Envia o comando de baixa de estoque para a fila compartilhada do Redis.
   */
  async enqueueStockDeduction(payload: IncomingMessageDto): Promise<void> {
    // Cláusula de Guarda: Impede o envio caso o payload esteja corrompido
    if (!payload || !payload.messageEventId) {
      this.logger.warn('Despacho rejeitado: Dados de payload corrompidos ou ausentes.');
      return;
    }

    try {
      // O padrão de microservices do NestJS envia mensagens usando uma assinatura de canal estruturada
      // Canal: "inventory:deduct-stock" -> Payload: Dados do DTO formatados
      const pattern = 'inventory:deduct-stock';
      
      this.logger.log(`Enviando evento [${payload.messageEventId}] para a fila Redis do Core PHP.`);
      
      // Envia via padrão fire-and-forget (emit), convertendo o Observable para Promise de forma limpa
      await lastValueFrom(this.redisClient.emit(pattern, payload));
    } catch (error) {
      this.logger.error(
        `Falha crítica ao tentar injetar evento [${payload.messageEventId}] no Redis: ${(error as Error).message}`
      );
      throw error;
    }
  }
}
