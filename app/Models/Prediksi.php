<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Prediksi extends Model
{
    use HasFactory;

    protected $table = 'prediksis';
    
    protected $primaryKey = 'id';
    
    public $timestamps = true;
    
    protected $fillable = [
        'tanggal',
        'hasil_prediksi',
        'total_pesanan',
        'penjualan_aktual',
        'error',
        'static_error',
        'static_mape',
        'static_rmse',
        'static_r_squared',
        'evaluasi_captured_at',
        'catatan',
        'pesanan_updated_at',
        'metode_analisis',
    ];
    
    protected $casts = [
        'tanggal' => 'date',
        'hasil_prediksi' => 'decimal:2',
        'total_pesanan' => 'integer',
        'penjualan_aktual' => 'decimal:2',
        'error' => 'decimal:2',
        'static_error' => 'decimal:2',
        'static_mape' => 'decimal:2',
        'static_rmse' => 'decimal:2',
        'static_r_squared' => 'decimal:4',
        'evaluasi_captured_at' => 'datetime',
        'pesanan_updated_at' => 'datetime',
    ];
    
    // Aksesors
    public function getHasilPrediksiFormattedAttribute()
    {
        return 'Rp ' . number_format($this->hasil_prediksi, 0, ',', '.');
    }
    
    public function getPenjualanAktualFormattedAttribute()
    {
        return $this->penjualan_aktual ? 'Rp ' . number_format($this->penjualan_aktual, 0, ',', '.') : '-';
    }
    
    public function getStatusPencapaianAttribute()
    {
        if (!$this->penjualan_aktual) return 'Belum Update';
        
        $persen = ($this->penjualan_aktual / $this->hasil_prediksi) * 100;
        
        if ($persen >= 100) return 'Tercapai';
        if ($persen >= 80) return 'Mendekati Target';
        if ($persen >= 50) return 'On Progress';
        return 'Perlu Aksi';
    }
    
    public function getStatusColorAttribute()
    {
        return match($this->status_pencapaian) {
            'Tercapai' => 'green',
            'Mendekati Target' => 'orange',
            'On Progress' => 'blue',
            default => 'red'
        };
    }
}