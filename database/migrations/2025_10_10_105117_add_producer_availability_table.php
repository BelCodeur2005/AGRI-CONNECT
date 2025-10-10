<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000027_add_producer_availability_table.php
return new class extends Migration
{
    /**
     * Gérer disponibilité des producteurs (calendrier)
     */
    public function up(): void
    {
        Schema::create('producer_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('available_from')->nullable();
            $table->time('available_to')->nullable();
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable(); // Ex: "Marché ce jour"
            $table->timestamps();
            
            $table->unique(['producer_id', 'date']);
            $table->index(['date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producer_availability');
    }
};