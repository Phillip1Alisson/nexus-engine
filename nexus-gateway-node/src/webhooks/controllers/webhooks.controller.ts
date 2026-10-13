import { Controller, Post, Body, HttpCode, HttpStatus } from '@nestjs/common';
import { WebhooksService } from '../services/webhooks.service';
import { IncomingMessageDto } from '../dtos/incoming-message.dto';
import { WebhookGuard } from '../../common/guards/webhook.guard';

@Controller('webhooks')
export class WebhooksController {
  constructor(private readonly webhooksService: WebhooksService) {}

  @Post('messages')
  @UseGuards(WebhookGuard)
  @HttpCode(HttpStatus.ACCEPTED) // HTTP 202: Indica processamento assíncrono aceito
  async handleIncomingMessage(
    @Body() incomingMessageDto: IncomingMessageDto,
  ): Promise<{ message: string; eventId: string }> {
    await this.webhooksService.processIncomingMessage(incomingMessageDto);

    return {
      message: 'Webhook de mensagem recebido e adicionado à fila de processamento.',
      eventId: incomingMessageDto.messageEventId,
    };
  }
}
