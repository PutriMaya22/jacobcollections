<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'data_barang';
    
    // Nonaktifkan timestamps jika tabel tidak memiliki created_at/updated_at
    public $timestamps = false;
    
    protected $fillable = [
        'kode_produk',
        'nama',
        'kategori',
        'status_produk',
        'rasio_penjualan',
        'total_penjualan',
        'total_dilihat',
        'total_klik',
        'total_pesanan',
        'persentase_klik',
        'tingkat_konversi',
        'tanggal_penjualan',
        'penjualan_per_pesanan',
        'total_pembeli',
        'produk_unik_dilihat',
        'produk_unik_diklik',
        'stok'
    ];
    
    protected $casts = [
        'stok' => 'integer',
        'harga' => 'integer',
        'hpp' => 'integer',
        'total_penjualan' => 'integer',
        'total_dilihat' => 'integer',
        'total_klik' => 'integer',
        'total_pesanan' => 'integer',
        'persentase_klik' => 'float',
        'tingkat_konversi' => 'float',
        'penjualan_per_pesanan' => 'float'
    ];
    
    // Accessor untuk format harga
    public function getHargaFormattedAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }
    
    // Accessor untuk format HPP
    public function getHppFormattedAttribute()
    {
        return 'Rp ' . number_format($this->hpp, 0, ',', '.');
    }
    
    // Hitung margin
    public function getMarginAttribute()
    {
        return $this->harga - $this->hpp;
    }
    
    // Hitung margin persen
    public function getMarginPersenAttribute()
    {
        return $this->harga > 0 ? ($this->margin / $this->harga) * 100 : 0;
    }
    
    // Scope untuk produk stok menipis
    public function scopeStokMenipis($query)
    {
        return $query->where('stok', '>', 0)->where('stok', '<=', 10);
    }
    
    // Scope untuk produk habis
    public function scopeHabis($query)
    {
        return $query->where('stok', '<=', 0);
    }
}