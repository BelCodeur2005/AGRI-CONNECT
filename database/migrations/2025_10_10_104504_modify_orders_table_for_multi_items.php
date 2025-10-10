<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000024_modify_orders_table_for_multi_items.php
return new class extends Migration
{
    /**
     * ATTENTION : Migration destructive si données existantes
     * Exécuter script de migration des données AVANT
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer colonnes devenues obsolètes
            $table->dropForeign(['offer_id']);
            $table->dropForeign(['producer_id']);
            $table->dropColumn([
                'offer_id',
                'producer_id', 
                'quantity',
                'unit_price',
            ]);
            
            // Modifier subtotal (sera calculé depuis items)
            // Note : déjà existe, pas besoin de recréer
            
            // Ajouter colonnes pour gestion multi-producteurs
            $table->integer('total_items')->default(0); // Nombre d'items
            $table->integer('items_confirmed')->default(0); // Items confirmés
            $table->integer('items_cancelled')->default(0); // Items annulés
            $table->boolean('is_multi_producer')->default(false); // Flag optimisation
            
            // Index pour recherches
            $table->index(['buyer_id', 'is_multi_producer']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Restaurer colonnes (perte de données si déjà migré)
            $table->foreignId('offer_id')->nullable()->constrained();
            $table->foreignId('producer_id')->nullable()->constrained();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            
            // Supprimer nouvelles colonnes
            $table->dropColumn([
                'total_items',
                'items_confirmed',
                'items_cancelled',
                'is_multi_producer',
            ]);
        });
    }
};