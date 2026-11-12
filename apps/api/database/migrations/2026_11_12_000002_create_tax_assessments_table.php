<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tax_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->decimal('total_icms', 16, 2);
            $table->decimal('total_ipi', 16, 2);
            $table->unsignedInteger('invoice_count');
            $table->timestamps();
        });
        Schema::create('assessment_invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            // Cada nota entra em apenas uma apuração para impedir contagem duplicada.
            $table->unique('invoice_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('assessment_invoice');
        Schema::dropIfExists('tax_assessments');
    }
};
