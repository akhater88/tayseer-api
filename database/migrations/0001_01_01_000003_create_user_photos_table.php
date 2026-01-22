<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('thumbnail_path', 500)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_photos');
    }
};
