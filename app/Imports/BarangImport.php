<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Facades\Log;

class BarangImport
{
    private $successCount = 0;
    private $failedCount = 0;
    private $errors = [];
    private $tanggalPenjualan;
    
    public function __construct($tanggalPenjualan = null)
    {
        $this->tanggalPenjualan = $tanggalPenjualan ?? date('Y-m-d');
        Log::info('BarangImport - Tanggal Penjualan: ' . $this->tanggalPenjualan);
    }
    
    public function import($filePath)
    {
        try {
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                throw new \Exception('PhpSpreadsheet tidak terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
            }
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows)) {
                throw new \Exception('File Excel kosong');
            }
            
            $header = array_shift($rows);
            
            Log::info('Header Excel: ' . json_encode($header));
            
            $mapping = [
                'kode_produk' => $this->findColumn($header, ['Kode Produk', 'kode_produk', 'Kode', 'SKU', 'ID Produk']),
                'nama' => $this->findColumn($header, ['Produk', 'produk', 'Nama Produk', 'nama_produk', 'Nama', 'Nama Barang']),
                'status_produk' => $this->findColumn($header, ['Status Produk', 'status_produk', 'Status', 'Status Produk Saat Ini']),
                'total_penjualan' => $this->findColumn($header, ['Penjualan (IDR)', 'Penjualan', 'total_penjualan']),
                'total_dilihat' => $this->findColumn($header, ['Jumlah Produk Dilihat', 'Dilihat', 'total_dilihat']),
                'total_klik' => $this->findColumn($header, ['Produk Diklik', 'Diklik', 'Klik', 'total_klik']),
                'total_pesanan' => $this->findColumn($header, ['Total Pesanan', 'Pesanan', 'total_pesanan']),
                // 🔥 FIELD BARU - LANGSUNG AMBIL DARI EXCEL
                'persentase_klik' => $this->findColumn($header, ['Persentase Klik', 'persentase_klik', 'CTR', 'Click Rate']),
                'tingkat_konversi' => $this->findColumn($header, ['Tingkat Konversi Pesanan', 'tingkat_konversi', 'Konversi', 'Conversion Rate', 'CR']),
            ];
            
            Log::info('Mapping kolom: ' . json_encode($mapping));
            
            if ($mapping['kode_produk'] === false) {
                throw new \Exception('Kolom "Kode Produk" tidak ditemukan. Header: ' . implode(', ', array_slice($header, 0, 8)));
            }
            
            if ($mapping['nama'] === false) {
                throw new \Exception('Kolom "Produk" tidak ditemukan. Header: ' . implode(', ', array_slice($header, 0, 8)));
            }
            
            foreach ($rows as $index => $row) {
                try {
                    // Skip baris kosong
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    $kodeProduk = trim($row[$mapping['kode_produk']] ?? '');
                    $nama = trim($row[$mapping['nama']] ?? '');
                    
                    if (empty($kodeProduk) || empty($nama)) {
                        $this->failedCount++;
                        $this->errors[] = "Baris " . ($index + 2) . ": Kode atau Nama kosong";
                        continue;
                    }
                    
                    // Ambil status dari Excel
                    $statusProduk = 'Normal';
                    if ($mapping['status_produk'] !== false && isset($row[$mapping['status_produk']])) {
                        $statusRaw = trim($row[$mapping['status_produk']]);
                        if (!empty($statusRaw)) {
                            $statusProduk = $statusRaw;
                        }
                    }
                    
                    // Tentukan kategori berdasarkan nama
                    $kategori = $this->determineCategory($nama);
                    
                    // 🔥 AMBIL LANGSUNG NILAI DARI EXCEL (TIDAK DIHITUNG)
                    $persentaseKlik = $this->parsePercentage($row, $mapping['persentase_klik']);
                    $tingkatKonversi = $this->parsePercentage($row, $mapping['tingkat_konversi']);
                    
                    // DATA LENGKAP
                    $data = [
                        'kode_produk' => $kodeProduk,
                        'nama' => $nama,
                        'status_produk' => $statusProduk,
                        'kategori' => $kategori,
                        'total_penjualan' => $this->parseNumber($row, $mapping['total_penjualan']),
                        'total_dilihat' => $this->parseNumber($row, $mapping['total_dilihat']),
                        'total_klik' => $this->parseNumber($row, $mapping['total_klik']),
                        'total_pesanan' => $this->parseNumber($row, $mapping['total_pesanan']),
                        'persentase_klik' => $persentaseKlik,
                        'tingkat_konversi' => $tingkatKonversi,
                        'tanggal_penjualan' => $this->tanggalPenjualan,
                        'stok' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    // INSERT DATA
                    Barang::create($data);
                    $this->successCount++;
                    
                    Log::info("✅ Import baris " . ($index + 2) . ": {$kodeProduk} - {$nama} - CTR: {$persentaseKlik}% - Konversi: {$tingkatKonversi}%");
                    
                } catch (\Exception $e) {
                    $this->failedCount++;
                    $this->errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                    Log::error("❌ Error baris " . ($index + 2) . ": " . $e->getMessage());
                }
            }
            
            Log::info("IMPORT SELESAI: Sukses={$this->successCount}, Gagal={$this->failedCount}");
            
        } catch (\Exception $e) {
            Log::error("Error import: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
    
    private function findColumn($header, $possibleNames)
    {
        foreach ($header as $index => $columnName) {
            $cleanColumn = trim($columnName);
            foreach ($possibleNames as $possibleName) {
                if (strcasecmp($cleanColumn, $possibleName) === 0) {
                    return $index;
                }
            }
        }
        return false;
    }
    
    private function parseNumber($row, $columnIndex)
    {
        if ($columnIndex === false || !isset($row[$columnIndex])) {
            return 0;
        }
        
        $value = trim($row[$columnIndex]);
        if (empty($value)) return 0;
        
        // Hapus format Rupiah (Rp, IDR, titik, koma)
        $value = str_replace(['Rp', 'IDR', 'Rp.', 'IDR.'], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '', $value);
        $value = preg_replace('/[^0-9\-]/', '', $value);
        
        return is_numeric($value) ? (int) $value : 0;
    }
    
    /**
     * 🔥 PARSING PERSENTASE DARI EXCEL
     * Contoh: "4,64%" atau "4.64%" atau "4.64" → 4.64
     */
    private function parsePercentage($row, $columnIndex)
    {
        if ($columnIndex === false || !isset($row[$columnIndex])) {
            return 0;
        }
        
        $value = trim($row[$columnIndex]);
        if (empty($value)) return 0;
        
        // Hapus tanda persen
        $value = str_replace('%', '', $value);
        
        // Ganti koma dengan titik (format Indonesia)
        $value = str_replace(',', '.', $value);
        
        // Hapus karakter selain angka, titik, minus
        $value = preg_replace('/[^0-9.\-]/', '', $value);
        
        return is_numeric($value) ? (float) $value : 0;
    }
    
    private function determineCategory($nama)
    {
        $namaLower = strtolower($nama);
        
        if (strpos($namaLower, 'pendek') !== false) {
            return 'Pendek';
        } elseif (strpos($namaLower, 'panjang') !== false) {
            return 'Panjang';
        } elseif (strpos($namaLower, 'denim') !== false) {
            return 'Denim';
        }
        
        return 'Sedang Diskon';
    }
    
    public function getSuccessCount()
    {
        return $this->successCount;
    }
    
    public function getFailedCount()
    {
        return $this->failedCount;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}