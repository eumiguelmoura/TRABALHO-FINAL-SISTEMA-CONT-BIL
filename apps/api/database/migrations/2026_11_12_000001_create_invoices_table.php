<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('issue_date');
            $table->string('supplier', 180);
            $table->string('number', 30);
            $table->string('city', 100);
            $table->char('state', 2);
            $table->decimal('icms_base', 16, 2);
            $table->decimal('icms_total', 16, 2);
            $table->decimal('ipi_total', 16, 2);
            $table->decimal('gross_total', 16, 2);
            $table->decimal('net_total', 16, 2);
            $table->timestamps();
            $table->unique(['user_id', 'supplier', 'number'], 'invoices_owner_supplier_number_unique');
            $table->index(['user_id', 'issue_date']);
        });
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 70);
            $table->string('description', 250);
            $table->char('cfop', 4);
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('gross_total', 16, 2);
            $table->decimal('icms_base', 16, 2);
            $table->decimal('icms_rate', 7, 4);
            $table->decimal('icms_amount', 16, 2);
            $table->decimal('ipi_rate', 7, 4);
            $table->decimal('ipi_amount', 16, 2);
            $table->decimal('net_total', 16, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
