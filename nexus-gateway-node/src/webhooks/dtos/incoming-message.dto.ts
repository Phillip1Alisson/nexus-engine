import { IsString, IsNotEmpty, IsInt, Min, IsUUID } from 'class-validator';

export class IncomingMessageDto {
  @IsUUID('4', { message: 'O tenant_id deve ser un UUID v4 válido.' })
  @IsNotEmpty({ message: 'O tenant_id é obrigatório.' })
  tenantId: string;

  @IsString({ message: 'O product_id deve ser uma string de texto.' })
  @IsNotEmpty({ message: 'O product_id é obrigatório.' })
  productId: string;

  @IsInt({ message: 'A quantidade deve ser um número inteiro.' })
  @Min(1, { message: 'A quantidade mínima a ser baixada é 1 unidade.' })
  quantity: number;

  @IsString({ message: 'O identificador do evento de mensagem deve ser uma string.' })
  @IsNotEmpty({ message: 'O message_event_id é obrigatório.' })
  messageEventId: string;
}
