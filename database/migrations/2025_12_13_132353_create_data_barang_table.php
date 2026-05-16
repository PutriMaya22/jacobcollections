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
        Schema::create('data_barang', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('kode_produk')->nullable();
            $table->text('nama')->nullable();
            $table->enum('kategori', ['Panjang', 'Pendek', 'Denim', 'Sedang Diskon'])->default('Panjang');
            $table->string('status_produk', 50)->nullable();
            $table->decimal('rasio_penjualan', 5, 2)->nullable();
            $table->bigInteger('total_penjualan')->nullable();
            $table->integer('total_dilihat')->nullable();
            $table->integer('total_klik')->nullable();
            $table->integer('total_pesanan')->nullable();
            $table->decimal('persentase_klik', 5, 2)->nullable();
            $table->decimal('tingkat_konversi', 5, 2)->nullable();
            $table->bigInteger('penjualan_per_pesanan')->nullable();
            $table->integer('total_pembeli')->nullable();
            $table->integer('produk_unik_dilihat')->nullable();
            $table->integer('produk_unik_diklik')->nullable();
            $table->integer('stok')->default(0);
            $table->integer('rekomendasi_restock')->default(0);
            $table->integer('prioritas_restock')->default(4);
            $table->string('status_restock', 100)->nullable();
            $table->integer('estimasi_laku')->default(0);
            $table->timestamp('last_predicted_at')->nullable();
            $table->date('tanggal_penjualan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_barang');
    }
};