<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;

final class InventoryBatch
{
    private int $remainingQuantity;

    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $productId,
        private readonly int $initialQuantity,
        private readonly float $costPrice,
        private readonly DateTimeImmutable $createdAt
    ) {
        $this->remainingQuantity = $initialQuantity;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getCostPrice(): float
    {
        return $this->costPrice;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRemainingQuantity(): int
    {
        return $this->remainingQuantity;
    }

    public function deductQuantity(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->remainingQuantity = max(0, $this->remainingQuantity - $amount);
    }
}
