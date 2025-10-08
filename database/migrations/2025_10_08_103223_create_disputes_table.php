<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_01_01_000017_create_disputes_table.php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('reported_against')->constrained('users');
            $table->enum('type', ['quality', 'quantity', 'delay', 'damage', 'other']);
            $table->text('description');
            $table->json('evidence_photos')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])
                  ->default('open');
            $table->text('admin_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};