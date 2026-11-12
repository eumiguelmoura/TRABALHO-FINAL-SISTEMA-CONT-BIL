<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('invoices', function (Blueprint $table) {
            // Na entrada, ICMS próprio normalmente é destacado, não abatido do valor da nota.
            $table->boolean('deduct_icms')->default(false);
            // IPI por fora compõe o total; desmarque quando o bruto informado já o inclui.
            $table->boolean('add_ipi_to_total')->default(true);
        });
    }

    public function down(): void {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['deduct_icms', 'add_ipi_to_total']);
        });
    }
};
