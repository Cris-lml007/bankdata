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
        Schema::table('assemblies', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_contract_id')
                ->nullable()
                ->after('contract_id');

            $table->foreign('detail_contract_id')->references('id')->on('detail_contracts');
            $table->index('detail_contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->dropForeign('detail_contract_id');
            $table->dropIndex('detail_contract_id');
            $table->dropColumn('detail_contract_id');
        });
    }
};
