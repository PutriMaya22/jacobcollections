<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_barang',
        'jumlah',
        'tanggal_keluar',
        'tujuan',
        'keterangan',
        'penanggung_jawab'
    ];

    protected $casts = [
        'tanggal_keluar' => 'date',
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
     * Boot method untuk update stok barang saat barang keluar
     */
    protected static function booted()
    {
        static::created(function ($barangKeluar) {
            $barang = $barangKeluar->barang;
            if ($barang->total >= $barangKeluar->jumlah) {
                $barang->decrement('total', $barangKeluar->jumlah);
            }
        });

        static::updated(function ($barangKeluar) {
            if ($barangKeluar->isDirty('jumlah')) {
                $barang = $barangKeluar->barang;
                $selisihLama = $barangKeluar->getOriginal('jumlah');
                $selisihBaru = $barangKeluar->jumlah;
                
                // Kembalikan stok lama, lalu kurangi dengan stok baru
                $barang->increment('total', $selisihLama);
                if ($barang->total >= $selisihBaru) {
                    $barang->decrement('total', $selisihBaru);
                }
            }
        });

        static::deleted(function ($barangKeluar) {
            $barang = $barangKeluar->barang;
            $barang->increment('total', $barangKeluar->jumlah);
        });
    }
}