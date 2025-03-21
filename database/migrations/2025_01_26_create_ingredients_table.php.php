<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('inci')->nullable();
            $table->text('description')->nullable();
            $table->decimal('moq', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_sample')->default(false);
            $table->boolean('in_stock')->default(false);
            $table->decimal('stock_amount', 10, 2)->nullable();
            $table->foreignId('supplier_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};