import { Module } from '@nestjs/common';
import { WebhooksModule } from './webhooks/webhooks.module';
import { QueueModule } from './queue/queue.module';

@Module({
  imports: [QueueModule, WebhooksModule],
  controllers: [],
  providers: [],
})
export class AppModule {}
