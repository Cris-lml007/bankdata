<?php

use App\Enums\Status;
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
        Schema::create('assembly_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assembly_id');
            $table->unsignedBigInteger('component_id');
            $table->foreign('assembly_id')->references('id')->on('assemblies')->cascadeOnDelete();
            $table->foreign('component_id')->references('id')->on('component_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->unsignedInteger('industry_id');
            $table->foreign('industry_id')->references('id')->on('industries')->nullOnDelete();
            $table->enum('status',\App\Enums\Status::cases())->default(Status::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_components');
    }
};
