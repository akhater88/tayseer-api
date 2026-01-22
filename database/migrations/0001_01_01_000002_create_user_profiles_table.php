<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 100)->nullable(); // Encrypted, private
            $table->date('date_of_birth');
            $table->foreignId('nationality_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->constrained();
            $table->foreignId('city_id')->constrained();
            $table->enum('marital_status', ['single', 'divorced', 'widowed', 'married']);
            $table->unsignedTinyInteger('number_of_children')->default(0);
            $table->unsignedTinyInteger('number_of_wives')->default(0); // Males only
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->enum('skin_color', ['very_light', 'light', 'wheatish', 'brown', 'dark'])->nullable();
            $table->enum('body_type', ['slim', 'athletic', 'average', 'curvy', 'heavy'])->nullable();
            $table->enum('religious_level', ['very_religious', 'religious', 'moderate', 'not_religious']);
            $table->enum('prayer_level', ['all_prayers', 'most_prayers', 'some_prayers', 'rarely', 'never']);
            $table->enum('smoking', ['no', 'yes', 'occasionally', 'quit'])->default('no');
            $table->enum('beard_type', ['full_beard', 'light_beard', 'no_beard'])->nullable(); // Males
            $table->enum('hijab_type', ['niqab', 'hijab', 'no_hijab'])->nullable(); // Females
            $table->enum('education_level', ['high_school', 'diploma', 'bachelors', 'masters', 'phd', 'other'])->nullable();
            $table->foreignId('work_field_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_title', 100)->nullable();
            $table->text('about_me')->nullable();
            $table->text('partner_preferences')->nullable();
            $table->unsignedTinyInteger('profile_completion')->default(0);
            $table->timestamps();

            $table->index('marital_status');
            $table->index('religious_level');
            $table->index(['country_id', 'city_id']);
            $table->index('profile_completion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
