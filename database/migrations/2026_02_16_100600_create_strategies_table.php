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
        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('description', 255);
            $table->enum('status', ['active', 'inactive', 'testing'])->default('inactive');
            $table->json('category');
            $table->json('markets');
            $table->json('timeframes');
            $table->decimal('target_rr', 10, 2)->default(0);
            $table->decimal('max_risk_per_trade', 10, 2)->default(0);
            $table->string('color', 7)->default('#000000'); // Hex color code
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategies');
    }
};
