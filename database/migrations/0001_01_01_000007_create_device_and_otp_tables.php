<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 500);
            $table->enum('platform', ['ios', 'android', 'web']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('token');
            $table->index('user_id');
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('code', 10);
            $table->enum('purpose', ['registration', 'login', 'password_reset']);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['phone', 'purpose']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('device_tokens');
    }
};
