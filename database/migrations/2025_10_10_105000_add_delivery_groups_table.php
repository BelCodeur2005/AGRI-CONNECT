<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000026_add_delivery_groups_table.php
return new class extends Migration
{
    /**
     * Pour optimiser livraisons multi-producteurs
     */
    public function up(): void
    {
        Schema::create('delivery_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_number')->unique(); // DG-20251007-0001
            $table->foreignId('transporter_id')->nullable()->constrained();
            $table->foreignId('delivery_location_id')->constrained('locations');
            $table->text('delivery_address');
            
            // Planification
            $table->date('scheduled_date');
            $table->time('scheduled_time_from')->nullable();
            $table->time('scheduled_time_to')->nullable();
            
            // Status groupe
            $table->enum('status', [
                'pending',
                'assigned',
                'in_progress',
                'completed',
                'failed'
            ])->default('pending');
            
            // Statistiques
            $table->integer('total_orders')->default(0);
            $table->integer('total_producers')->default(0);
            $table->decimal('total_weight', 10, 2)->default(0);
            $table->decimal('total_delivery_cost', 10, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['scheduled_date', 'status']);
        });
        
        // Lier deliveries aux groupes
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('delivery_group_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('sequence_in_group')->nullable(); // Ordre dans la tournée
            
            $table->index('delivery_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['delivery_group_id']);
            $table->dropColumn(['delivery_group_id', 'sequence_in_group']);
        });
        
        Schema::dropIfExists('delivery_groups');
    }
};