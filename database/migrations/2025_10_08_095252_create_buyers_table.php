<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000004_create_buyers_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained(); // Où il est situé
            $table->string('business_name'); // Nom restaurant/hôtel
            $table->enum('business_type', ['restaurant', 'hotel', 'supermarket', 'processor', 'exporter', 'other'])
                  ->default('restaurant');
            $table->string('business_license')->nullable(); // Numéro registre commerce
            $table->text('delivery_address');
            $table->string('tax_id')->nullable(); // Numéro contribuable
            $table->integer('stars_rating')->nullable(); // 3*, 4*, 5* pour hôtels
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};

