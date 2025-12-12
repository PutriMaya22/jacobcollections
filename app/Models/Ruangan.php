<?php

// app/Models/Ruangan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangans'; 
    protected $fillable = [
        'nama',
        'keterangan'
    ];

    /**
     * Relasi dengan Barang
     */
    public function barangs()
    {
        return $this->hasMany(Barang::class, 'id_ruangan');
    }

    /**
     * Accessor untuk total barang di ruangan
     */
    public function getTotalBarangAttribute()
    {
        return $this->barangs()->sum('total');
    }

    /**
     * Accessor untuk jumlah jenis barang di ruangan
     */
    public function getJumlahJenisBarangAttribute()
    {
        return $this->barangs()->count();
    }
}

