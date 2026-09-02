<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->string('market', 10)->default('crypto')->after('order_id')->index();

            // PSE-specific fee columns (nullable, only used when market = pse)
            $table->decimal('broker_commission', 15, 8)->nullable()->after('close_fees');
            $table->decimal('pse_trans_fee', 15, 8)->nullable()->after('broker_commission');
            $table->decimal('sccp_fee', 15, 8)->nullable()->after('pse_trans_fee');
            $table->decimal('pse_vat', 15, 8)->nullable()->after('sccp_fee');
            $table->decimal('sales_tax', 15, 8)->nullable()->after('pse_vat');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn([
                'market',
                'broker_commission',
                'pse_trans_fee',
                'sccp_fee',
                'pse_vat',
                'sales_tax',
            ]);
        });
    }
};
