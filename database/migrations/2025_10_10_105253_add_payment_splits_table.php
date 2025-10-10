<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000029_add_payment_splits_table.php
return new class extends Migration
{
    /**
     * Pour diviser paiement entre producteurs (commande multi-producteurs)
     */
    public function up(): void
    {
        Schema::create('payment_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('producer_id')->constrained();
            $table->decimal('amount', 12, 2); // Part du producteur
            $table->decimal('platform_commission', 10, 2);
            $table->decimal('net_amount', 12, 2); // Montant après commission
            $table->enum('status', ['pending', 'held', 'released', 'failed'])->default('pending');
            $table->string('transaction_reference')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->index(['payment_id', 'producer_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_splits');
    }
};