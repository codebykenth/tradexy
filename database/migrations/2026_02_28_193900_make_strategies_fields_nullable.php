<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('strategies', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
            $table->json('category')->nullable()->change();
            $table->json('markets')->nullable()->change();
            $table->json('timeframes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('strategies', function (Blueprint $table) {
            $table->string('description', 255)->nullable(false)->change();
            $table->json('category')->nullable(false)->change();
            $table->json('markets')->nullable(false)->change();
            $table->json('timeframes')->nullable(false)->change();
        });
    }
};
