<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the enum check constraints first (PostgreSQL)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE trades DROP CONSTRAINT IF EXISTS trades_entry_emotion_check');
            DB::statement('ALTER TABLE trades DROP CONSTRAINT IF EXISTS trades_exit_emotion_check');
        }

        Schema::table('trades', function (Blueprint $table) {
            // Change emotions from enum to nullable string (accepts any value)
            $table->string('entry_emotion', 50)->nullable()->change();
            $table->string('exit_emotion', 50)->nullable()->change();

            // Make these fields nullable (not always available from API)
            $table->decimal('take_profit_price', 15, 8)->nullable()->change();
            $table->decimal('stop_loss_price', 15, 8)->nullable()->change();
            $table->string('timeframe', 5)->nullable()->change();
            $table->decimal('open_fees', 15, 8)->nullable()->change();
            $table->decimal('close_fees', 15, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->string('entry_emotion', 50)->nullable(false)->change();
            $table->string('exit_emotion', 50)->nullable(false)->change();
            $table->decimal('take_profit_price', 15, 8)->nullable(false)->change();
            $table->decimal('stop_loss_price', 15, 8)->nullable(false)->change();
            $table->string('timeframe', 5)->nullable(false)->change();
            $table->decimal('open_fees', 15, 8)->nullable(false)->change();
            $table->decimal('close_fees', 15, 8)->nullable(false)->change();
        });
    }
};
