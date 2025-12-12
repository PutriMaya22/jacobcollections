<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_barang',
        'jumlah',
        'tanggal_masuk',
        'supplier',
        'keterangan',
        'penerima'
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'jumlah' => 'integer'
    ];

    /**
     * Relasi dengan Barang
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    /**
     * Scope untuk data terbaru
     */
    public function scopeTerbaru($query, $hari = 1)
    {
        return $query->where('created_at', '>=', now()->subDays($hari));
    }

    /**
     * Boot method untuk update stok barang saat barang masuk
     */
    protected static function booted()
    {
        static::created(function ($barangMasuk) {
            $barang = $barangMasuk->barang;
            $barang->increment('total', $barangMasuk->jumlah);
        });

        static::updated(function ($barangMasuk) {
            if ($barangMasuk->isDirty('jumlah')) {
                $barang = $barangMasuk->barang;
                $selisih = $barangMasuk->jumlah - $barangMasuk->getOriginal('jumlah');
                $barang->increment('total', $selisih);
            }
        });

        static::deleted(function ($barangMasuk) {
            $barang = $barangMasuk->barang;
            $barang->decrement('total', $barangMasuk->jumlah);
        });
    }
}