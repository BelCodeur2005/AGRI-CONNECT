<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000010_create_payments_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique(); // ID transaction Mobile Money
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['orange_money', 'mtn_momo', 'cash', 'bank_transfer']);
            $table->enum('status', ['pending', 'held', 'released', 'refunded', 'failed'])
                  ->default('pending');
            
            // Informations payer (acheteur)
            $table->foreignId('payer_id')->constrained('users');
            $table->string('payer_phone');
            
            // Informations payee (producteur)
            $table->foreignId('payee_id')->constrained('users');
            $table->string('payee_phone')->nullable();
            
            // Métadonnées Mobile Money
            $table->json('payment_metadata')->nullable(); // Réponse API
            $table->string('operator_reference')->nullable(); // Ref opérateur
            
            // Timestamps workflow
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('held_at')->nullable(); // Quand argent bloqué
            $table->timestamp('released_at')->nullable(); // Quand libéré au producteur
            $table->timestamp('refunded_at')->nullable();
            
            $table->timestamps();

            $table->index('order_id');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
