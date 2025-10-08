<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000009_create_orders_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // AG-20251007-0001
            $table->foreignId('buyer_id')->constrained()->onDelete('cascade');
            $table->foreignId('producer_id')->constrained();
            $table->foreignId('offer_id')->constrained();
            
            // Détails commande
            $table->decimal('quantity', 10, 2); // Quantité commandée
            $table->decimal('unit_price', 10, 2); // Prix unitaire au moment commande
            $table->decimal('subtotal', 12, 2); // Sous-total
            $table->decimal('platform_commission', 10, 2); // Commission Agri-Connect
            $table->decimal('delivery_cost', 10, 2)->default(0); // Frais livraison
            $table->decimal('total_amount', 12, 2); // Total à payer
            
            // Informations livraison
            $table->text('delivery_address');
            $table->foreignId('delivery_location_id')->constrained('locations');
            $table->dateTime('requested_delivery_date')->nullable();
            $table->text('delivery_notes')->nullable();
            
            // Status workflow
            $table->enum('status', [
                'pending', 'confirmed', 'payment_pending', 'paid', 
                'ready_for_pickup', 'in_transit', 'delivered', 
                'completed', 'cancelled', 'refunded'
            ])->default('pending');
            
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['buyer_id', 'status']);
            $table->index(['producer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};