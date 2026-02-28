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
    Schema::create('prediksis', function (Blueprint $table) {
        $table->id();
        $table->date('tanggal');
        $table->double('hasil_prediksi');
        $table->double('penjualan_aktual')->nullable();
        $table->double('error')->nullable();
        $table->float('rmse')->nullable();
        $table->float('mape')->nullable();
        $table->float('r_squared')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediksis');
    }
};
