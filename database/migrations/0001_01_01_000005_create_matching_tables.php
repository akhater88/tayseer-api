<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('message', 100)->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined', 'withdrawn'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['sender_id', 'receiver_id'], 'unique_interest');
            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_1_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_2_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('matched_at')->useCurrent();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamps();

            $table->index('user_1_id');
            $table->index('user_2_id');
            $table->index('status');
            $table->index('matched_at');
        });

        Schema::create('chat_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending_female', 'pending_guardian', 'approved', 'rejected'])->default('pending_female');
            $table->foreignId('guardian_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('guardian_reviewed_at')->nullable();
            $table->enum('guardian_decision', ['approved', 'rejected'])->nullable();
            $table->string('guardian_rejection_reason', 255)->nullable();
            $table->string('firebase_conversation_id', 100)->nullable();
            $table->timestamps();

            $table->index('match_id');
            $table->index('status');
            $table->index(['receiver_id', 'status']);
            $table->index(['guardian_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_requests');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('interests');
    }
};
