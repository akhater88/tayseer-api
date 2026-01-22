<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('favorited_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'favorited_user_id'], 'unique_favorite');
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['blocker_id', 'blocked_id'], 'unique_block');
            $table->index('blocked_id');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('reason', ['inappropriate_photos', 'offensive_content', 'fake_profile', 'harassment', 'contact_info', 'other']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('reported_user_id');
        });

        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('viewed_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['viewed_id', 'created_at']);
            $table->index(['viewer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('favorites');
    }
};
