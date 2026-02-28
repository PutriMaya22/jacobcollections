<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediksi extends Model
{
    use HasFactory;
    protected $fillable = [
    'tanggal',
    'hasil_prediksi',
    'penjualan_aktual',
    'error',
    'rmse',
    'mape',
    'r_squared'
];
}
