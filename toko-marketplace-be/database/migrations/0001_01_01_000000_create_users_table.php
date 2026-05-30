<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto increment BIGINT PK [cite: 255]
            $table->string('name'); // VARCHAR(255) Nama lengkap [cite: 255]
            $table->string('email')->unique(); // VARCHAR(255) Unique [cite: 255]
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Bcrypt hashed [cite: 255]
            
            // Kolom role untuk Role-Based System (admin | staff)
            $table->enum('role', ['admin', 'staff']); // ENUM admin | staff [cite: 255]
            
            $table->rememberToken();
            $table->timestamps(); // Otomatis Laravel (created_at, updated_at) [cite: 255]
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};