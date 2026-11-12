<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('catalog_products', function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sku',70);
            $table->string('description',250);
            $table->char('cfop',4);
            $table->decimal('unit_price',14,2);
            $table->decimal('icms_rate',7,4)->default(0);
            $table->decimal('ipi_rate',7,4)->default(0);
            $table->timestamps();
            $table->unique(['user_id','sku']);
        });
    }
    public function down(): void { Schema::dropIfExists('catalog_products'); }
};
