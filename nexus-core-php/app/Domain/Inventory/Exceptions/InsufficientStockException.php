<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Exceptions;

use Exception;

final class InsufficientStockException extends Exception
{
    public static function forProduct(string $productId, int $requested, int $available): self
    {
        return new self(
            sprintf(
                "Estoque insuficiente para o produto [%s]. Solicitado: %d, Disponível: %d.",
                $productId,
                $requested,
                $available
            )
        );
    }
}
