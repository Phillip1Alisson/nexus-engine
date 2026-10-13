import { Injectable, CanActivate, ExecutionContext, UnauthorizedException, Logger } from '@nestjs/common';
import { FastifyRequest } from 'fastify';

@Injectable()
export class WebhookGuard implements CanActivate {
  private readonly logger = new Logger(WebhookGuard.name);

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest<FastifyRequest>();
    
    // Captura o token de autenticação ou assinatura digital enviada pelo parceiro no cabeçalho
    const signature = request.headers['x-nexus-signature'] || request.headers['authorization'];

    // Cláusula de Guarda: Rejeição imediata se não houver assinatura de segurança
    if (!signature) {
      this.logger.warn(`Bloqueio de borda: Requisição de Webhook sem assinatura criptográfica.`);
      throw new UnauthorizedException('Assinatura de segurança ausente no cabeçalho.');
    }

    // Validação do Token de Produção (Definido nas variáveis de ambiente seguras do Docker)
    const expectedToken = process.env.WEBHOOK_SECRET_TOKEN || 'nexus_secret_fallback';
    
    if (signature !== expectedToken) {
      this.logger.error(`Tentativa de invasão ou token corrompido detectado no Webhook.`);
      throw new UnauthorizedException('Assinatura criptográfica inválida.');
    }

    return true;
  }
}
