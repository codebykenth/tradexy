<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            // Index for account mode and market filtering combined with user and date
            $table->index(['user_id', 'is_demo', 'market', 'close_datetime'], 'idx_trades_filter_close');
            // Index for strategy dashboard aggregates
            $table->index(['strategy_id', 'is_demo', 'market'], 'idx_strategy_performance');
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->index(['user_id', 'is_demo', 'market', 'date'], 'idx_balances_filter_date');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('idx_trades_filter_close');
            $table->dropIndex('idx_strategy_performance');
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->dropIndex('idx_balances_filter_date');
        });
    }
};
