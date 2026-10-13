<?php

declare(strict_with=1);

namespace Tests\Unit\Domain\Inventory\Strategies;

use App\Domain\Inventory\Entities\InventoryBatch;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Strategies\PepsCostingStrategy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PepsCostingStrategyTest extends TestCase
{
    private PepsCostingStrategy $strategy;
    private string $tenantId;
    private string $productId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new PepsCostingStrategy();
        $this->tenantId = 'tenant-123';
        $this->productId = 'prod-abc';
    }

    /** @test */
    public function it_calculates_cmv_by_deducting_from_the_single_oldest_batch(): void
    {
        $batch = new InventoryBatch(
            id: 'batch-1',
            tenantId: $this->tenantId,
            productId: $this->productId,
            initialQuantity: 10,
            costPrice: 15.0, // Custo total inicial: 150.0
            createdAt: new DateTimeImmutable('2026-10-01 10:00:00')
        );

        // Deduz 4 unidades do lote de custo 15.0 -> CMV esperado: 4 * 15.0 = 60.0
        $cmv = $this->strategy->calculateCmv($this->productId, 4, [$batch]);

        $this->assertEquals(60.0, $cmv);
        $this->assertEquals(6, $batch->getRemainingQuantity());
    }

    /** @test */
    public function it_calculates_cmv_across_multiple_batches_using_fifo_order(): void
    {
        // Lote mais antigo: 5 unidades a R$ 10.00 cada
        $batchOld = new InventoryBatch(
            id: 'batch-old',
            tenantId: $this->tenantId,
            productId: $this->productId,
            initialQuantity: 5,
            costPrice: 10.0,
            createdAt: new DateTimeImmutable('2026-10-01 08:00:00')
        );

        // Lote mais recente: 10 unidades a R$ 20.00 cada
        $batchNew = new InventoryBatch(
            id: 'batch-new',
            tenantId: $this->tenantId,
            productId: $this->productId,
            initialQuantity: 10,
            costPrice: 20.0,
            createdAt: new DateTimeImmutable('2026-10-02 08:00:00')
        );

        $batches = [$batchOld, $batchNew];

        // Solicita 7 unidades. Deve consumir as 5 do antigo (5 * 10 = 50) e 2 do novo (2 * 20 = 40).
        // CMV Esperado: 50.0 + 40.0 = 90.0
        $cmv = $this->strategy->calculateCmv($this->productId, 7, $batches);

        $this->assertEquals(90.0, $cmv);
        $this->assertEquals(0, $batchOld->getRemainingQuantity());
        $this->assertEquals(8, $batchNew->getRemainingQuantity());
    }

    /** @test */
    public function it_throws_an_exception_when_requested_quantity_exceeds_available_stock(): void
    {
        $batch = new InventoryBatch(
            id: 'batch-unique',
            tenantId: $this->tenantId,
            productId: $this->productId,
            initialQuantity: 5,
            costPrice: 10.0,
            createdAt: new DateTimeImmutable('2026-10-01 12:00:00')
        );

        $this->expectException(InsufficientStockException::class);
        
        // Tenta tirar 10 unidades de um estoque que só possui 5
        $this->strategy->calculateCmv($this->productId, 10, [$batch]);
    }
}
