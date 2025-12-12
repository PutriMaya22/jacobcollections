<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_ruangan',
        'nomor_seri',
        'foto',
        'nama',
        'total',
        'kategori',
        'kondisi',
        'tanggal_pembelian',
        'status'
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
    ];

    /**
     * Relasi dengan Ruangan
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan');
    }

    /**
     * Relasi dengan Barang Masuk
     */
    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class, 'id_barang');
    }

    /**
     * Relasi dengan Barang Keluar
     */
    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'id_barang');
    }

    /**
     * Scope untuk barang dengan stok rendah
     */
    public function scopeStokRendah($query, $batas = 5)
    {
        return $query->where('total', '<', $batas)->where('total', '>', 0);
    }

    /**
     * Scope untuk barang habis
     */
    public function scopeStokHabis($query)
    {
        return $query->where('total', '=', 0);
    }

    /**
     * Scope untuk barang rusak
     */
    public function scopeBarangRusak($query)
    {
        return $query->where('kondisi', '!=', 'baik');
    }

    /**
     * Scope untuk kategori jaringan
     */
    public function scopeKategoriJaringan($query)
    {
        $kategoriJaringan = [
            'Router & Switch',
            'Access Point', 
            'Network Cable',
            'Network Tool',
            'Server'
        ];
        
        return $query->whereIn('kategori', $kategoriJaringan);
    }

    /**
     * Accessor untuk status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'tersedia' => 'green',
            'dipinjam' => 'yellow',
            'perbaikan' => 'red',
            default => 'gray'
        };
    }

    /**
     * Accessor untuk kondisi badge color
     */
    public function getKondisiColorAttribute()
    {
        return match($this->kondisi) {
            'baik' => 'green',
            'rusak_ringan' => 'yellow',
            'rusak_berat' => 'red',
            default => 'gray'
        };
    }

    /**
     * Accessor untuk kondisi text
     */
    public function getKondisiTextAttribute()
    {
        return match($this->kondisi) {
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Accessor untuk status text
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'tersedia' => 'Tersedia',
            'dipinjam' => 'Dipinjam',
            'perbaikan' => 'Perbaikan',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Accessor untuk tipe kategori (jaringan/umum)
     */
    public function getTipeKategoriAttribute()
    {
        $kategoriJaringan = [
            'Router & Switch',
            'Access Point',
            'Network Cable', 
            'Network Tool',
            'Server'
        ];
        
        return in_array($this->kategori, $kategoriJaringan) ? 'jaringan' : 'umum';
    }
}