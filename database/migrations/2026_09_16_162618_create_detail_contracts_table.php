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
        Schema::create('detail_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('contract_id')->nullable();
            $table->unsignedInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_contracts');
    }
};
