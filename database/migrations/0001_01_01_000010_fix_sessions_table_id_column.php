<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the sessions table exists and needs fixing
        if (Schema::hasTable('sessions')) {
            $columnType = DB::selectOne("SHOW COLUMNS FROM sessions WHERE Field = 'id'")?->Type ?? '';

            // If the id column is an integer type, we need to fix it
            if (str_contains(strtolower($columnType), 'int')) {
                // Drop the existing sessions table and recreate with correct schema
                Schema::dropIfExists('sessions');

                Schema::create('sessions', function (Blueprint $table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }
        }
    }

    public function down(): void
    {
        // No rollback needed - the original schema was incorrect
    }
};
