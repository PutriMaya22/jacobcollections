<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateKategoriBarang extends Migration
{
    public function up()
    {
        // Update kategori yang sudah ada (opsional)
        DB::table('barangs')->where('kategori', 'Elektronik')->update(['kategori' => 'Elektronik']);
        // Tambahkan update lainnya sesuai kebutuhan
    }

    public function down()
    {
        // Rollback jika diperlukan
    }
}