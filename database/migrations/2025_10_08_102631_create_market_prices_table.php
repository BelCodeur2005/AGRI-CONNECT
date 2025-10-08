<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000015_create_market_prices_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->decimal('min_price', 10, 2); // Prix min du jour
            $table->decimal('max_price', 10, 2); // Prix max du jour
            $table->decimal('avg_price', 10, 2); // Prix moyen
            $table->decimal('suggested_price', 10, 2); // Prix suggéré aux producteurs
            $table->date('price_date');
            $table->string('source')->nullable(); // D'où vient l'info
            $table->timestamps();

            $table->unique(['product_id', 'location_id', 'price_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};