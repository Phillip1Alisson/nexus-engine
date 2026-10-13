<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('product_id');
            $table->integer('initial_quantity');
            $table->integer('remaining_quantity');
            $table->decimal('cost_price', 12, 4); // Suporta alta precisão monetária 
            $table->timestamps();

            // ÍNDICE COMPOSTO CRUCIAL PARA SÊNIOR: 
            // Otimiza drasticamente a busca do algoritmo PEPS por Tenant, Produto e Fila Cronológica
            $table->index(['tenant_id', 'product_id', 'remaining_quantity', 'created_at'], 'idx_peps_fifo_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
