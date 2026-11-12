<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('suppliers',function(Blueprint $table){$table->id();$table->foreignId('user_id')->constrained()->cascadeOnDelete();$table->string('name',180);$table->string('city',100);$table->char('state',2);$table->string('document',20)->nullable();$table->timestamps();$table->unique(['user_id','name']);});}
 public function down():void {Schema::dropIfExists('suppliers');}
};
