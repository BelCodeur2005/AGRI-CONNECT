<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000028_add_offer_stock_movements.php
return new class extends Migration
{
    /**
     * Historique des mouvements de stock pour audit
     */
    public function up(): void
    {
        Schema::create('offer_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained();
            $table->enum('type', ['reservation', 'release', 'adjustment', 'sale']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('quantity_before', 10, 2);
            $table->decimal('quantity_after', 10, 2);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['offer_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_stock_movements');
    }
};