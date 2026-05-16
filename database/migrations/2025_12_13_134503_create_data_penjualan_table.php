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
        Schema::create('data_penjualan', function (Blueprint $table) {
            $table->dateTime('tanggal')->nullable();
            $table->float('total_pesanan')->nullable();
            $table->float('total_penjualan')->nullable();
            $table->integer('hari_dalam_minggu')->nullable();
            $table->bigInteger('weekend')->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_penjualan');
    }
};