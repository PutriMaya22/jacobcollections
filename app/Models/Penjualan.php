<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'data_penjualan';
    
    // 🔥 PRIMARY KEY ADALAH TANGGAL
    protected $primaryKey = 'tanggal';
    public $incrementing = false;
    protected $keyType = 'date';
    
    // Nonaktifkan timestamps
    public $timestamps = false;
    
    protected $fillable = [
        'tanggal',
        'total_penjualan',
        'total_pesanan',
        'hari_dalam_minggu',
        'weekend',
        'bulan',
        'tahun'
    ];
    
    protected $casts = [
        'tanggal' => 'datetime',
        'total_penjualan' => 'integer',
        'total_pesanan' => 'integer',
        'hari_dalam_minggu' => 'integer',
        'weekend' => 'integer',
        'bulan' => 'integer',
        'tahun' => 'integer'
    ];
    
    // Accessor untuk format rupiah
    public function getTotalPenjualanFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total_penjualan, 0, ',', '.');
    }
    
    // Scope untuk filter bulan
    public function scopeBulan($query, $bulan, $tahun = null)
    {
        $tahun = $tahun ?? now()->year;
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}