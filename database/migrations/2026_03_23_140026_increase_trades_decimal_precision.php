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
            // Increase precision and scale to handle large crypto quantities and totals
            $table->decimal('quantity', 28, 8)->change();
            $table->decimal('cum_entry_value', 28, 8)->change();
            $table->decimal('cum_exit_value', 28, 8)->change();
            $table->decimal('avg_entry_price', 28, 8)->change();
            $table->decimal('avg_exit_price', 28, 8)->change();
            $table->decimal('take_profit_price', 28, 8)->nullable()->change();
            $table->decimal('stop_loss_price', 28, 8)->nullable()->change();
            $table->decimal('open_fees', 28, 8)->nullable()->change();
            $table->decimal('close_fees', 28, 8)->nullable()->change();
            $table->decimal('closed_pnl', 28, 8)->change();
            $table->decimal('total_pnl', 28, 8)->change();
            $table->decimal('leverage', 8, 2)->default(1)->change();

            // PSE specific fees (already nullable)
            $table->decimal('broker_commission', 28, 8)->nullable()->change();
            $table->decimal('pse_trans_fee', 28, 8)->nullable()->change();
            $table->decimal('sccp_fee', 28, 8)->nullable()->change();
            $table->decimal('pse_vat', 28, 8)->nullable()->change();
            $table->decimal('sales_tax', 28, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('quantity', 15, 8)->change();
            $table->decimal('cum_entry_value', 15, 8)->change();
            $table->decimal('cum_exit_value', 15, 8)->change();
            $table->decimal('avg_entry_price', 15, 8)->change();
            $table->decimal('avg_exit_price', 15, 8)->change();
            $table->decimal('take_profit_price', 15, 8)->nullable()->change();
            $table->decimal('stop_loss_price', 15, 8)->nullable()->change();
            $table->decimal('open_fees', 15, 8)->nullable()->change();
            $table->decimal('close_fees', 15, 8)->nullable()->change();
            $table->decimal('closed_pnl', 15, 8)->change();
            $table->decimal('total_pnl', 15, 8)->change();
            $table->decimal('leverage', 5, 2)->default(1)->change();

            $table->decimal('broker_commission', 15, 8)->nullable()->change();
            $table->decimal('pse_trans_fee', 15, 8)->nullable()->change();
            $table->decimal('sccp_fee', 15, 8)->nullable()->change();
            $table->decimal('pse_vat', 15, 8)->nullable()->change();
            $table->decimal('sales_tax', 15, 8)->nullable()->change();
        });
    }
};
