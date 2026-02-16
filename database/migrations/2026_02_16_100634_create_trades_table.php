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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('strategy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_id', 50);
            $table->string('symbol', 10);
            $table->enum('entry_side', ['long', 'short']);
            $table->enum('exit_side', ['long', 'short']);
            $table->decimal('entry_price', 15, 8);
            $table->decimal('exit_price', 15, 8);
            $table->decimal('quantity', 15, 8);
            $table->decimal('cum_entry_value', 15, 8);
            $table->decimal('cum_exit_value', 15, 8);
            $table->decimal('avg_entry_price', 15, 8);
            $table->decimal('avg_exit_price', 15, 8);
            $table->enum('entry_emotion', ['greed', 'fear', 'hope', 'regret', 'confidence', 'anxiety']);
            $table->enum('exit_emotion', ['greed', 'fear', 'hope', 'regret', 'confidence', 'anxiety']);
            $table->decimal('take_profit_price', 15, 8);
            $table->decimal('stop_loss_price', 15, 8);
            $table->string('timeframe', 5);
            $table->decimal('leverage', 5, 2)->default(1);
            $table->string('chart_picture')->nullable();
            $table->decimal('open_fees', 15, 8);
            $table->decimal('close_fees', 15, 8);
            $table->decimal('closed_pnl', 15, 8);
            $table->decimal('total_pnl', 15, 8);
            $table->dateTime('open_datetime');
            $table->dateTime('close_datetime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
