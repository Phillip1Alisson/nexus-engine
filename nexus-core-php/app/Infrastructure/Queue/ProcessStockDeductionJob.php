<?php

declare(strict_types=1);

namespace App\Infrastructure\Queue;

use App\Application\Inventory\UseCases\DeductStockUseCase;
use App\Infrastructure\Persistence\EloquentInventoryRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Logger;
use Throwable;

final class ProcessStockDeductionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array{tenantId: string, productId: string, quantity: int, messageEventId: string} $payload
     */
    public function __construct(
        private readonly array $payload
    ) {}

    /**
     * Executa o processamento do Job consumido da fila do Redis.
     */
    public function handle(): void
    {
        // Cláusula de Guarda: Evita processar se os índices mínimos do payload estiverem corrompidos
        if (empty($this->payload['tenantId']) || empty($this->payload['productId'])) {
            return;
        }

        try {
            // Inversão de dependência manual para manter o Use Case puro e desacoplado
            $repository = new EloquentInventoryRepository();
            $useCase = new DeductStockUseCase($repository);

            $useCase->execute(
                tenantId: $this->payload['tenantId'],
                productId: $this->payload['productId'],
                quantity: (int) $this->payload['quantity']
            );
        } catch (Throwable $exception) {
            // Tratamento de erro limpo sem quebrar a execução global do worker
            Logger::error(
                sprintf(
                    "[Worker Error] Falha na baixa do evento [%s]: %s",
                    $this->payload['messageEventId'] ?? 'unknown',
                    $exception->getMessage()
                )
            );

            $this->fail($exception);
        }
    }
}
