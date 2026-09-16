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
        Schema::create('assemblies', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedInteger('contract_id');
            $table->foreign('contract_id')->references('id')->on('contracts')->cascadeOnDelete();
            $table->integer('quantity');
            $table->unsignedInteger('industry_id');
            $table->foreign('industry_id')->references('id')->on('industries')->nullOnDelete();
            $table->enum('status',\App\Enums\Status::cases())->default(\App\Enums\Status::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assemblies');
    }
};
