<?php

namespace App\Imports;

use App\Models\Penjualan;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PenjualanImport
{
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $zeroValueCount = 0;
    protected $errors = [];

    /**
     * Main import method
     */
    public function import($file)
    {
        try {
            // Load file
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows)) {
                throw new \Exception("File kosong");
            }
            
            // Ambil header (baris pertama)
            $header = array_shift($rows);
            
            // Map header ke index
            $columnMap = $this->mapColumns($header);
            
            if (empty($columnMap['tanggal']) || empty($columnMap['penjualan']) || empty($columnMap['pesanan'])) {
                // Jika tidak menemukan kolom yang sesuai, gunakan urutan default: kolom 0=tanggal, 1=penjualan, 2=pesanan
                $columnMap = [
                    'tanggal' => 0,
                    'penjualan' => 1,
                    'pesanan' => 2
                ];
            }
            
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Skip baris kosong
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    $tanggal = $row[$columnMap['tanggal']] ?? null;
                    $totalPenjualan = $row[$columnMap['penjualan']] ?? null;
                    $totalPesanan = $row[$columnMap['pesanan']] ?? null;
                    
                    // Validasi data
                    if (empty($tanggal)) {
                        $this->addError("Baris " . ($rowIndex + 2) . ": Tanggal kosong");
                        continue;
                    }
                    
                    // Format tanggal (handle berbagai format)
                    $tanggalFormatted = $this->formatDate($tanggal);
                    if (!$tanggalFormatted) {
                        $this->addError("Baris " . ($rowIndex + 2) . ": Format tanggal '" . $tanggal . "' tidak valid");
                        continue;
                    }
                    
                    // Bersihkan angka penjualan dengan format Indonesia
                    $totalPenjualanClean = $this->parseIndonesianNumber($totalPenjualan);
                    
                    // Bersihkan angka pesanan dengan format Indonesia
                    $totalPesananClean = $this->parseIndonesianNumber($totalPesanan);
                    
                    // Catat data dengan nilai 0
                    if ($totalPenjualanClean == 0 || $totalPesananClean == 0) {
                        $this->zeroValueCount++;
                    }
                    
                    // Cek duplikasi tanggal
                    $existing = Penjualan::whereDate('tanggal', $tanggalFormatted)->first();
                    if ($existing) {
                        $this->addError("Baris " . ($rowIndex + 2) . ": Tanggal " . $tanggalFormatted . " sudah ada");
                        continue;
                    }
                    
                    $carbonDate = Carbon::parse($tanggalFormatted);
                    
                    // Simpan ke database (termasuk nilai 0)
                    Penjualan::create([
                        'tanggal' => $tanggalFormatted,
                        'total_penjualan' => $totalPenjualanClean,
                        'total_pesanan' => $totalPesananClean,
                        'hari_dalam_minggu' => $carbonDate->dayOfWeek,
                        'weekend' => $carbonDate->isWeekend() ? 1 : 0,
                        'bulan' => $carbonDate->month,
                        'tahun' => $carbonDate->year,
                    ]);
                    
                    $this->successCount++;
                    
                } catch (\Exception $e) {
                    $this->addError("Baris " . ($rowIndex + 2) . ": " . $e->getMessage());
                }
            }
            
            if ($this->successCount == 0 && $this->failedCount == 0) {
                throw new \Exception("Tidak ada data yang valid untuk diimport");
            }
            
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Map kolom header ke index
     */
    private function mapColumns($header)
    {
        $map = [
            'tanggal' => null,
            'penjualan' => null,
            'pesanan' => null
        ];
        
        $tanggalKeywords = ['tanggal', 'date', 'tgl', 'hari', 'day'];
        $penjualanKeywords = ['total_penjualan', 'penjualan', 'total', 'jumlah_penjualan', 'sales', 'revenue', 'omzet'];
        $pesananKeywords = ['total_pesanan', 'pesanan', 'order', 'jumlah_pesanan', 'orders', 'qty_order'];
        
        foreach ($header as $index => $column) {
            $columnLower = strtolower(trim((string)$column));
            
            // Cek tanggal
            if ($map['tanggal'] === null) {
                foreach ($tanggalKeywords as $keyword) {
                    if (str_contains($columnLower, $keyword)) {
                        $map['tanggal'] = $index;
                        break;
                    }
                }
            }
            
            // Cek penjualan
            if ($map['penjualan'] === null) {
                foreach ($penjualanKeywords as $keyword) {
                    if (str_contains($columnLower, $keyword)) {
                        $map['penjualan'] = $index;
                        break;
                    }
                }
            }
            
            // Cek pesanan
            if ($map['pesanan'] === null) {
                foreach ($pesananKeywords as $keyword) {
                    if (str_contains($columnLower, $keyword)) {
                        $map['pesanan'] = $index;
                        break;
                    }
                }
            }
        }
        
        return $map;
    }
    
    /**
     * Format tanggal ke Y-m-d (support berbagai format)
     */
    private function formatDate($date)
    {
        if (empty($date)) return null;
        
        // Jika sudah Carbon
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }
        
        // Jika numeric (Excel serial)
        if (is_numeric($date)) {
            try {
                $unix = ($date - 25569) * 86400;
                return date('Y-m-d', $unix);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        $str = trim((string) $date);
        
        // Hapus teks hari (Monday, Tuesday, etc)
        $str = preg_replace('/\b(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday|Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu)\b/i', '', $str);
        $str = trim($str);
        
        try {
            // Format dd-mm-yyyy atau dd/mm/yyyy
            if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $str, $matches)) {
                return Carbon::createFromDate($matches[3], $matches[2], $matches[1])->format('Y-m-d');
            }
            
            // Format yyyy-mm-dd atau yyyy/mm/dd
            if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $str, $matches)) {
                return Carbon::createFromDate($matches[1], $matches[2], $matches[3])->format('Y-m-d');
            }
            
            // Format d-m-yyyy dengan bulan teks (contoh: 25-Jan-2024)
            if (preg_match('/^(\d{1,2})[- ](\w+)[- ](\d{4})$/', $str, $matches)) {
                return Carbon::createFromFormat('d M Y', $matches[1] . ' ' . $matches[2] . ' ' . $matches[3])->format('Y-m-d');
            }
            
            // Biarkan Carbon mencoba
            return Carbon::parse($str)->format('Y-m-d');
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Parsing angka format Indonesia ke integer
     * Menangani format:
     * - 337.250  -> 337250
     * - 112.416,67 -> 112416
     * - 1.500.000 -> 1500000
     * - Rp 337.250 -> 337250
     * - 337250 (langsung) -> 337250
     */
    private function parseIndonesianNumber($value)
    {
        // Jika kosong
        if (empty($value) && $value !== 0 && $value !== '0') {
            return 0;
        }
        
        // Konversi ke string
        $str = (string) $value;
        
        // Hapus prefix Rp / rupiah
        $str = preg_replace('/^Rp\s*/i', '', $str);
        
        // Jika sudah numeric, return langsung
        if (is_numeric($str) && !str_contains($str, '.') && !str_contains($str, ',')) {
            return (int) $str;
        }
        
        // Hapus semua titik (pemisah ribuan)
        $str = str_replace('.', '', $str);
        
        // Ganti koma dengan titik (desimal) lalu ambil bagian integer saja
        $str = str_replace(',', '.', $str);
        
        // Ambil bagian integer (sebelum desimal)
        if (str_contains($str, '.')) {
            $parts = explode('.', $str);
            $str = $parts[0];
        }
        
        // Hapus semua yang bukan angka
        $str = preg_replace('/[^0-9]/', '', $str);
        
        // Konversi ke integer
        $result = (int) $str;
        
        return $result < 0 ? 0 : $result;
    }
    
    /**
     * Add error message (kept for compatibility)
     */
    private function addError($error)
    {
        $this->failedCount++;
        $this->errors[] = $error;
    }
    
    /**
     * Get success count
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }
    
    /**
     * Get failed count
     */
    public function getFailedCount()
    {
        return $this->failedCount;
    }
    
    /**
     * Get zero value count
     */
    public function getZeroValueCount()
    {
        return $this->zeroValueCount;
    }
    
    /**
     * Get errors array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}