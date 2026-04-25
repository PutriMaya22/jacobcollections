<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data_penjualan', function (Blueprint $table) {
            // Tambah kolom id sebagai primary key auto increment
            $table->id()->first();
        });
    }

    public function down()
    {
        Schema::table('data_penjualan', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }
};