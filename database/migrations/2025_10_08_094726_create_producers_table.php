<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000003_create_producers_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained(); // Où il produit
            $table->string('farm_name')->nullable();
            $table->decimal('farm_size', 8, 2)->nullable(); // En hectares
            $table->text('farm_address')->nullable();
            $table->string('id_card_number')->nullable(); // CNI
            $table->string('id_card_photo')->nullable();
            $table->json('certifications')->nullable(); // Bio, etc.
            $table->integer('years_experience')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_ratings')->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0); // En FCFA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producers');
    }
};