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
        Schema::table('trades', function (Blueprint $blueprint) {
            $blueprint->boolean('is_demo')->default(false)->after('ai_analysis');
            $blueprint->index('is_demo'); // Index for filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $blueprint) {
            $blueprint->dropColumn('is_demo');
        });
    }
};
