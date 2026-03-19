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
            // Speeds up dashboard stats filtering by user and close time
            $table->index(['user_id', 'close_datetime']);

            // Speeds up ranking (best/worst trades)
            $table->index('total_pnl');

            // Speeds up top symbols grouping
            $table->index('symbol');
        });

        Schema::table('balances', function (Blueprint $table) {
            // Speeds up equity curve data retrieval
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'close_datetime']);
            $table->dropIndex(['total_pnl']);
            $table->dropIndex(['symbol']);
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
        });
    }
};
