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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_ruangan')->constrained('ruangans')->onDelete('cascade');
            $table->string('foto')->nullable();
            $table->string('nama');
            $table->string('kategori');
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat']);
            $table->date('tanggal_pembelian');
            $table->enum('status', ['tersedia', 'dipinjam', 'perbaikan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
