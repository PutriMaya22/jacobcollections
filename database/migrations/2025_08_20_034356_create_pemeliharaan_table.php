<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pemeliharaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('jenis_pemeliharaan', ['rutin', 'preventif', 'korektif']);
            $table->decimal('biaya', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->string('teknisi');
            $table->enum('status', ['dijadwalkan', 'dalam_pengerjaan', 'selesai', 'ditunda'])->default('dijadwalkan');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pemeliharaan');
    }
};