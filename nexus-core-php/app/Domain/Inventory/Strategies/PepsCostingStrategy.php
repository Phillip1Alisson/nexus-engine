<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Strategies;

use App\Domain\Inventory\Entities\InventoryBatch;
use App\Domain\Inventory\Exceptions\InsufficientStockException;

final class PepsCostingStrategy
{
    /**
     * Calcula o CMV e atualiza os saldos dos lotes com base na regra PEPS (FIFO).
     *
     * @param array<InventoryBatch> $batches Lotes disponíveis ordenados por data ASC
     * @throws InsufficientStockException
     */
    public function calculateCmv(string $productId, int $quantityToDeduct, array $batches): float
    {
        if ($quantityToDeduct <= 0) {
            return 0.0;
        }

        $totalAvailable = array_reduce(
            $batches,
            fn (int $carry, InventoryBatch $b): int => $carry + $b->getRemainingQuantity(),
            0
        );

        if ($quantityToDeduct > $totalAvailable) {
            throw InsufficientStockException::forProduct($productId, $quantityToDeduct, $totalAvailable);
        }

        return $this->processQueue($quantityToDeduct, $batches);
    }

    /**
     * Processa a fila decrementando os lotes e somando o custo acumulado.
     *
     * @param array<InventoryBatch> $batches
     */
    private function processQueue(int $targetQuantity, array $batches): float
    {
        $accumulatedCmv = 0.0;
        $needed = $targetQuantity;

        foreach ($batches as $batch) {
            if ($needed <= 0) {
                break;
            }

            $available = $batch->getRemainingQuantity();
            if ($available <= 0) {
                continue;
            }

            $taken = min($needed, $available);
            $accumulatedCmv += $taken * $batch->getCostPrice();

            $batch->deductQuantity($taken);
            $needed -= $taken;
        }

        return $accumulatedCmv;
    }
}

