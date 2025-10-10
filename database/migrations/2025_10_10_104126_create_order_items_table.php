<?php

// database/migrations/2025_01_01_000023_create_order_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('offer_id')->constrained();
            $table->foreignId('producer_id')->constrained(); // Dénormalisé pour perfs
            $table->foreignId('product_id')->constrained(); // Dénormalisé pour perfs
            
            // Snapshot des données au moment de la commande
            $table->string('product_name'); // Au cas où produit supprimé
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2); // Prix au moment commande
            $table->decimal('subtotal', 12, 2); // quantity * unit_price
            $table->decimal('platform_commission', 10, 2);
            
            // Status par item (permet gestion granulaire)
            $table->enum('status', [
                'pending',      // En attente confirmation producteur
                'confirmed',    // Confirmé par producteur
                'cancelled',    // Annulé (par producteur ou acheteur)
                'ready',        // Prêt pour collecte
                'collected',    // Collecté par transporteur
                'delivered',    // Livré
                'completed'     // Confirmé par acheteur
            ])->default('pending');
            
            $table->text('producer_notes')->nullable(); // Notes du producteur
            $table->text('cancellation_reason')->nullable();
            
            // Timestamps par item
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->timestamps();
            
            // Index pour performances
            $table->index(['order_id', 'status']);
            $table->index('producer_id');
            $table->index('offer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};