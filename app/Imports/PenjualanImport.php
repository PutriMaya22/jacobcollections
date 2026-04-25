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
    protected $zeroValueCount = 0; // Tambahan: counter untuk data nilai 0
    protected $errors = [];

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
                    
                    // 🔥 BARU: Bersihkan angka penjualan dengan format Indonesia (titik dan koma)
                    $totalPenjualanClean = $this->parseIndonesianNumber($totalPenjualan);
                    
                    // 🔥 BARU: Bersihkan angka pesanan dengan format Indonesia
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
        
        // Jika sudah dalam format Excel serial number
        if (is_numeric($date)) {
            try {
                // Excel serial number to date
                $unix = ($date - 25569) * 86400;
                return date('Y-m-d', $unix);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        $dateStr = trim((string)$date);
        
        try {
            // Format Y-m-d
            if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $dateStr)) {
                return Carbon::parse($dateStr)->format('Y-m-d');
            }
            
            // Format Y/m/d
            if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $dateStr)) {
                return Carbon::parse($dateStr)->format('Y-m-d');
            }
            
            // Format d/m/Y atau d-m-Y
            if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}$/', $dateStr)) {
                return Carbon::createFromFormat('d/m/Y', str_replace('-', '/', $dateStr))->format('Y-m-d');
            }
            
            // Format m/d/Y
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $dateStr)) {
                return Carbon::createFromFormat('m/d/Y', $dateStr)->format('Y-m-d');
            }
            
            // Format lain (biarkan Carbon mencoba)
            return Carbon::parse($dateStr)->format('Y-m-d');
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 🔥 FUNGSI BARU 1: Parsing angka format Indonesia
     * Menangani format:
     * - 337.250  -> 337250
     * - 112.416,67 -> 112416 (integer)
     * - 1.500.000 -> 1500000
     * - Rp 337.250 -> 337250
     * - 337250 (langsung) -> 337250
     * - 337,250 (koma sebagai ribuan) -> 337250
     * - 112416.67 -> 112416
     */
   /**
 * 🔥 PARSING ANGKA FORMAT INDONESIA - VERSI SEDERHANA
 */
/**
 * 🔥 PARSING ANGKA FORMAT INDONESIA - FINAL FIX
 * Contoh: "337.250" -> 337250, "1.500.000" -> 1500000
 */
/**
 * 🔥 PARSING ANGKA - VERSION FINAL
 */
private function parseIndonesianNumber($value)
{
    // Jika kosong
    if (empty($value) && $value !== 0) {
        return 0;
    }
    
    // Konversi ke string dan bersihkan
    $str = (string) $value;
    
    // Hapus semua titik dan koma
    $str = str_replace('.', '', $str);
    $str = str_replace(',', '', $str);
    
    // Hapus semua yang bukan angka
    $str = preg_replace('/[^0-9]/', '', $str);
    
    // Konversi ke integer
    $result = (int) $str;
    
    return $result;
}
    
    /**
     * 🔥 FUNGSI BARU 2: Bersihkan angka (tetap dipertahankan untuk kompatibilitas)
     */
    private function cleanNumber($value)
    {
        if (empty($value) && $value !== 0 && $value !== '0') {
            return 0;
        }
        
        // Jika sudah numeric
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        $str = trim((string)$value);
        
        // Hapus semua karakter kecuali angka
        $cleaned = preg_replace('/[^0-9]/', '', $str);
        
        if (empty($cleaned)) {
            return 0;
        }
        
        $result = (int) $cleaned;
        
        return $result < 0 ? 0 : $result;
    }
    
    private function addError($error)
    {
        $this->failedCount++;
        $this->errors[] = $error;
    }
    
    public function getSuccessCount()
    {
        return $this->successCount;
    }
    
    public function getFailedCount()
    {
        return $this->failedCount;
    }
    
    public function getZeroValueCount()
    {
        return $this->zeroValueCount;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}