<?php

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
            // Adds composite index for the 'latest('close_datetime')' query
            $table->index(['user_id', 'close_datetime']);
        });

        Schema::table('balances', function (Blueprint $table) {
            // Adds composite index for the 'latest('date')' query
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
        });

        Schema::table('balances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
        });
    }
};
