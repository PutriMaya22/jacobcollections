<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('prediksis', function (Blueprint $table) {
        $table->string('metode_analisis')->nullable()->after('evaluasi_captured_at');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prediksis', function (Blueprint $table) {
            //
        });
    }
};
