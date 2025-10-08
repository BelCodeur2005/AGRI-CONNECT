<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000007_create_products_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories');
            $table->string('name'); // Plantain, Tomate, Macabo
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('unit', ['kg', 'tonne', 'litre', 'piece', 'bag', 'crate', 'bunch'])
                  ->default('kg');
            $table->boolean('is_perishable')->default(true);
            $table->integer('shelf_life_days')->nullable(); // Durée conservation
            $table->json('quality_criteria')->nullable(); // Critères qualité
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
