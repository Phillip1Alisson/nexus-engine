import { Injectable, Logger } from '@nestjs/common';
import { IncomingMessageDto } from '../dtos/incoming-message.dto';

@ModuleContext({ role: 'application-service' })
@Injectable()
export class WebhooksService {
  private readonly logger = new Logger(WebhooksService.name);

  async processIncomingMessage(payload: IncomingMessageDto): Promise<{ success: boolean; eventId: string }> {
    // Cláusula de Guarda: Validação preemptiva se o payload estivesse nulo ou corrompido
    if (!payload || payload.quantity <= 0) {
      this.logger.warn(`Tentativa de processamento rejeitada: payload inválido.`);
      return { success: false, eventId: payload?.messageEventId || 'unknown' };
    }

    this.logger.log(
      `Ingestão de Webhook: Evento [${payload.messageEventId}] recebido para o Tenant [${payload.tenantId}]. ` +
      `Enfileirando baixa de ${payload.quantity} unidades do produto [${payload.productId}].`
    );

    // TODO: Implementar injeção do job de fila no Redis (BullMQ) para processamento assíncrono pelo PHP core
    return {
      success: true,
      eventId: payload.messageEventId,
    };
  }
}
