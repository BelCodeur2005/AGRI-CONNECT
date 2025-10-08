<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000011_create_deliveries_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('transporter_id')->nullable()->constrained();
            
            // Lieux
            $table->foreignId('pickup_location_id')->constrained('locations');
            $table->text('pickup_address');
            $table->foreignId('delivery_location_id')->constrained('locations');
            $table->text('delivery_address');
            
            // Planification
            $table->dateTime('scheduled_pickup_at')->nullable();
            $table->dateTime('scheduled_delivery_at')->nullable();
            
            // Réalité
            $table->dateTime('actual_pickup_at')->nullable();
            $table->dateTime('actual_delivery_at')->nullable();
            
            // Status
            $table->enum('status', [
                'pending', 'assigned', 'picked_up', 
                'in_transit', 'arrived', 'delivered', 'failed'
            ])->default('pending');
            
            // Tracking GPS
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_update')->nullable();
            
            // Informations livraison
            $table->text('delivery_notes')->nullable();
            $table->string('delivery_proof_photo')->nullable(); // Photo à livraison
            $table->string('signature')->nullable(); // Signature digitale
            
            // Qualité
            $table->boolean('on_time')->nullable(); // Livré à temps?
            $table->integer('delay_minutes')->nullable(); // Minutes de retard
            
            $table->timestamps();

            $table->index(['transporter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};

