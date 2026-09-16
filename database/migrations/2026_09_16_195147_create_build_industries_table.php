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
        Schema::create('build_industries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('industry_id');
            $table->foreign('industry_id')->references('id')->on('industries');
            $table->integer('quantity');
            $table->morphs('buildeable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('build_industries');
    }
};
