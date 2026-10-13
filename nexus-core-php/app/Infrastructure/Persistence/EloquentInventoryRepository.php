<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Inventory\Entities\InventoryBatch;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    /**
     * @return array<InventoryBatch>
     */
    public function findAvailableBatches(string $tenantId, string $productId): array
    {
        // Busca lotes com saldo maior que zero usando Database Builder nativo
        // Ordenação cronológica (created_at ASC) é CRUCIAL para garantir a lógica FIFO do PEPS
        $records = DB::table('inventory_batches')
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $batches = [];
        foreach ($records as $record) {
            $batches[] = new InventoryBatch(
                id: (string) $record->id,
                tenantId: $record->tenant_id,
                productId: $record->product_id,
                initialQuantity: (int) $record->initial_quantity,
                costPrice: (float) $record->cost_price,
                createdAt: new DateTimeImmutable($record->created_at)
            );
        }

        return $batches;
    }

    /**
     * Atualiza os lotes modificados em lote dentro de uma transação Database atômica.
     *
     * @param array<InventoryBatch> $batches
     */
    public function saveMany(array $batches): void
    {
        if (empty($batches)) {
            return;
        }

        DB::transaction(function () use ($batches): void {
            foreach ($batches as $batch) {
                DB::table('inventory_batches')
                    ->where('id', $batch->getId())
                    ->update([
                        'remaining_quantity' => $batch->getRemainingQuantity(),
                        'updated_at' => now(),
                    ]);
            }
        });
    }
}
