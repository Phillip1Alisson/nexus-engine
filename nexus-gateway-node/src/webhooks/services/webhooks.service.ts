import { Injectable, Logger } from '@nestjs/common';
import { IncomingMessageDto } from '../dtos/incoming-message.dto';
import { QueueService } from '../../queue/queue.service';

@Injectable()
export class WebhooksService {
  private readonly logger = new Logger(WebhooksService.name);

  // Injeção do serviço de mensageria Redis por inversão de dependência (DIP)
  constructor(private readonly queueService: QueueService) {}

  async processIncomingMessage(payload: IncomingMessageDto): Promise<{ success: boolean; eventId: string }> {
    // Cláusula de Guarda: Validação preemptiva de integridade
    if (!payload || payload.quantity <= 0) {
      this.logger.warn('Tentativa de processamento rejeitada: payload inválido.');
      return { success: false, eventId: payload?.messageEventId || 'unknown' };
    }

    this.logger.log(`Ingestão de Webhook: Evento [${payload.messageEventId}] aceito com sucesso.`);

    // Despacha a mensagem de forma assíncrona para o Redis (Processamento não-bloqueante)
    await this.queueService.enqueueStockDeduction(payload);

    return {
      success: true,
      eventId: payload.messageEventId,
    };
  }
}
