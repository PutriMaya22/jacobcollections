<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    // karena nama tabel tidak default (bukan penjualans)
    protected $table = 'data_penjualan';

    protected $fillable = [
        'tanggal',
        'total_penjualan',
        'total_pesanan',
        'penjualan_perpesanan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
