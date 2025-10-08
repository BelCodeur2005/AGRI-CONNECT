<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000005_create_transporters_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transporters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type'); // Pickup, Truck, Van
            $table->string('vehicle_registration');
            $table->string('vehicle_photo')->nullable();
            $table->string('driver_license_number');
            $table->string('driver_license_photo')->nullable();
            $table->decimal('max_capacity_kg', 8, 2); // Capacité max
            $table->boolean('has_refrigeration')->default(false);
            $table->json('service_areas')->nullable(); // [location_id, location_id]
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->integer('total_deliveries')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_certified')->default(false); // Certifié par Agri-Connect
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporters');
    }
};