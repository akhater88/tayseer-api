<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('female_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('guardian_name', 100);
            $table->string('guardian_phone', 20);
            $table->enum('relationship', ['father', 'brother', 'son', 'uncle', 'grandfather']);
            $table->string('invitation_code', 10)->unique();
            $table->enum('status', ['pending', 'sent', 'accepted', 'expired'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('invitation_code');
            $table->index('status');
            $table->index(['female_user_id', 'status']);
        });

        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('female_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('relationship', ['father', 'brother', 'son', 'uncle', 'grandfather']);
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->unique(['guardian_user_id', 'female_user_id'], 'unique_guardian_female');
            $table->index('female_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('guardian_invitations');
    }
};
