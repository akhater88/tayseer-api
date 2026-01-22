<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'moderator'])->default('moderator');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('new_registrations_male')->default(0);
            $table->unsignedInteger('new_registrations_female')->default(0);
            $table->unsignedInteger('active_users')->default(0);
            $table->unsignedInteger('interests_sent')->default(0);
            $table->unsignedInteger('matches_created')->default(0);
            $table->unsignedInteger('conversations_started')->default(0);
            $table->unsignedInteger('guardian_approvals')->default(0);
            $table->unsignedInteger('guardian_rejections')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_statistics');
        Schema::dropIfExists('admins');
    }
};
