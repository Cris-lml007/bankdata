<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Modelo afectado: relación polimórfica
            $table->morphs('loggable');

            // Usuario que realizó la acción
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Acción realizada: created, updated, deleted, etc.
            $table->string('event', 50);

            $table->string('description')->nullable();

            // Valores anteriores y nuevos
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
