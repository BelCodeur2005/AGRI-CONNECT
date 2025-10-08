<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique(); // Principal pour auth
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['producer', 'buyer', 'transporter', 'admin'])->default('producer');
            $table->string('profile_photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // Vérifié par admin
            $table->string('fcm_token')->nullable(); // Pour push notifications
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};