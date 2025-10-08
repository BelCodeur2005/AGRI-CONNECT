<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('location_id')->constrained(); // Lieu de collecte
            $table->string('title'); // Ex: "500 kg de plantain mûr"
            $table->text('description')->nullable();
            $table->decimal('quantity_available', 10, 2); // Quantité disponible
            $table->decimal('quantity_reserved', 10, 2)->default(0); // Déjà réservée
            $table->decimal('min_order_quantity', 10, 2)->nullable(); // Commande min
            $table->decimal('price_per_unit', 10, 2); // Prix par kg/unité (FCFA)
            $table->date('harvest_date')->nullable();
            $table->date('available_from'); // Disponible à partir de
            $table->date('available_until'); // Disponible jusqu'à
            $table->json('photos')->nullable(); // Array de photos
            $table->string('quality_grade')->nullable(); // A, B, C
            $table->boolean('organic')->default(false);
            $table->enum('status', ['active', 'reserved', 'sold_out', 'expired', 'inactive'])
                  ->default('active');
            $table->integer('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'available_from']);
            $table->index('producer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};