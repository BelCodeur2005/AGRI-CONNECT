<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Bafoussam - Douala"
            $table->foreignId('origin_location_id')->constrained('locations');
            $table->foreignId('destination_location_id')->constrained('locations');
            $table->integer('distance_km');
            $table->integer('estimated_duration_hours');
            $table->decimal('base_transport_cost', 10, 2); // Coût de base
            $table->text('road_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('all_weather')->default(true); // Praticable toute année
            $table->json('waypoints')->nullable(); // Points de passage
            $table->timestamps();

            $table->index(['origin_location_id', 'destination_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
