<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000025_add_cart_functionality.php
return new class extends Migration
{
    /**
     * Table pour panier temporaire (avant création commande)
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('offer_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->text('notes')->nullable(); // Notes acheteur
            $table->timestamps();
            
            // Un utilisateur ne peut avoir qu'une seule quantité par offre dans son panier
            $table->unique(['user_id', 'offer_id']);
            
            // Index pour performances
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
