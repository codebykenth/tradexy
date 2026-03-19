<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->index('total_pnl', 'idx_perf_trades_pnl');
            $table->index('symbol', 'idx_perf_trades_symbol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('idx_perf_trades_pnl');
            $table->dropIndex('idx_perf_trades_symbol');
        });
    }
};
