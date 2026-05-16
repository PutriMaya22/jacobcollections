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
            $table->decimal('rata_rata_historis', 15, 2)->nullable();
            $table->string('status_prediksi', 50)->nullable();
            $table->integer('total_pesanan')->default(0);
            $table->timestamp('pesanan_updated_at')->nullable();
            $table->double('penjualan_aktual')->nullable();
            $table->double('error')->nullable();
            $table->decimal('static_error', 15, 2)->nullable();
            $table->timestamp('evaluasi_captured_at')->nullable();
            $table->string('metode_analisis', 255)->nullable();
            $table->decimal('static_rmse', 15, 2)->nullable();
            $table->decimal('static_mape', 10, 2)->nullable();
            $table->decimal('static_r_squared', 10, 4)->nullable();
            $table->text('catatan')->nullable();
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