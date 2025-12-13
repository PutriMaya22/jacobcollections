<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'data_barang';

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama',
        'kategori',
        'harga'
    ];
}
