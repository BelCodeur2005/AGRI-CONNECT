<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000012_create_ratings_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            
            // Qui note qui
            $table->foreignId('rater_id')->constrained('users'); // Celui qui note
            $table->morphs('rateable'); // Celui qui est noté (producer/buyer/transporter)
            
            // Notes (1-5)
            $table->integer('overall_score')->unsigned(); // Note globale
            $table->integer('quality_score')->unsigned()->nullable(); // Qualité produit
            $table->integer('punctuality_score')->unsigned()->nullable(); // Ponctualité
            $table->integer('communication_score')->unsigned()->nullable(); // Communication
            $table->integer('packaging_score')->unsigned()->nullable(); // Emballage
            
            // Commentaire
            $table->text('comment')->nullable();
            $table->text('admin_response')->nullable(); // Réponse de l'équipe
            
            // Modération
            $table->boolean('is_verified')->default(false); // Vérifié réel
            $table->boolean('is_featured')->default(false); // Mis en avant
            
            $table->timestamps();

            $table->unique(['order_id', 'rater_id', 'rateable_type']);
            $table->index(['rateable_type', 'rateable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};