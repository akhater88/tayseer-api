<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->char('code', 2)->unique();
            $table->string('phone_code', 10);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->index('country_id');
        });

        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('work_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_fields');
        Schema::dropIfExists('nationalities');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
};
