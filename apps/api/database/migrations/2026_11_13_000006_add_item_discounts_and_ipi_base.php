<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('discount_total', 16, 2)->default(0)->after('gross_total');
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 16, 2)->default(0)->after('gross_total');
            $table->decimal('ipi_base', 16, 2)->nullable()->after('discount_amount');
            $table->decimal('unit_price', 14, 4)->change();
        });
        // Registros antigos usavam o valor bruto como base de IPI.
        DB::table('invoice_items')->whereNull('ipi_base')->update(['ipi_base'=>DB::raw('gross_total')]);
    }
    public function down(): void {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['discount_amount','ipi_base']);
            $table->decimal('unit_price', 14, 2)->change();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('discount_total');
        });
    }
};
