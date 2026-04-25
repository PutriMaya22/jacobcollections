<?php

namespace App\Imports;

use App\Models\Barang;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class BarangImport
{
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $errors = [];
    
    // Daftar nilai ENUM yang valid untuk kategori
    protected $validKategori = ['Panjang', 'Pendek', 'Denim', 'Sedang Diskon'];
    
    // Mapping kategori dari berbagai format
    protected $kategoriMapping = [
        'denim' => 'Denim',
        'jeans' => 'Denim',
        'panjang' => 'Panjang',
        'long' => 'Panjang',
        'pendek' => 'Pendek',
        'short' => 'Pendek',
        'sedang diskon' => 'Sedang Diskon',
        'diskon' => 'Sedang Diskon',
        'promo' => 'Sedang Diskon',
        'discount' => 'Sedang Diskon',
        'umum' => 'Panjang',
        'normal' => 'Panjang',
        'cargo' => 'Panjang',
        'chino' => 'Panjang',
        'jeans pendek' => 'Pendek',
        'jeans panjang' => 'Panjang',
    ];

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
            
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Skip baris kosong
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // Ambil data
                    $kodeProduk = $this->cleanString($row[$columnMap['kode_produk']] ?? null);
                    $nama = $this->cleanString($row[$columnMap['nama']] ?? null);
                    $stok = $this->cleanNumber($row[$columnMap['stok']] ?? 0);
                    $kategori = $this->cleanString($row[$columnMap['kategori']] ?? null);
                    $statusProduk = $this->cleanString($row[$columnMap['status_produk']] ?? 'Normal');
                    
                    // Data numerik
                    $totalPenjualan = $this->cleanNumber($row[$columnMap['total_penjualan']] ?? 0);
                    $totalDilihat = $this->cleanNumber($row[$columnMap['total_dilihat']] ?? 0);
                    $totalKlik = $this->cleanNumber($row[$columnMap['total_klik']] ?? 0);
                    $totalPesanan = $this->cleanNumber($row[$columnMap['total_pesanan']] ?? 0);
                    $rasioPenjualan = $this->cleanPercent($row[$columnMap['rasio_penjualan']] ?? 0);
                    $persentaseKlik = $this->cleanPercent($row[$columnMap['persentase_klik']] ?? 0);
                    $tingkatKonversi = $this->cleanPercent($row[$columnMap['tingkat_konversi']] ?? 0);
                    $penjualanPerPesanan = $this->cleanNumber($row[$columnMap['penjualan_per_pesanan']] ?? 0);
                    $totalPembeli = $this->cleanNumber($row[$columnMap['total_pembeli']] ?? 0);
                    $produkUnikDilihat = $this->cleanNumber($row[$columnMap['produk_unik_dilihat']] ?? 0);
                    $produkUnikDiklik = $this->cleanNumber($row[$columnMap['produk_unik_diklik']] ?? 0);
                    
                    // Validasi data wajib
                    if (empty($kodeProduk)) {
                        $this->addError("Baris " . ($rowIndex + 2) . ": Kode produk kosong");
                        continue;
                    }
                    
                    if (empty($nama)) {
                        $this->addError("Baris " . ($rowIndex + 2) . ": Nama produk kosong");
                        continue;
                    }
                    
                    // Cek duplikasi kode produk
                    $existing = Barang::where('kode_produk', $kodeProduk)->first();
                    if ($existing) {
                        $this->addError("Baris " . ($rowIndex + 2) . ": Kode produk '" . $kodeProduk . "' sudah ada");
                        continue;
                    }
                    
                    // Normalisasi kategori dan status
                    $kategoriNormalized = $this->normalizeKategori($kategori);
                    $statusProdukNormalized = $this->normalizeStatusProduk($statusProduk);
                    
                    // Simpan ke database
                    Barang::create([
                        'kode_produk' => (string) $kodeProduk,
                        'nama' => (string) $nama,
                        'kategori' => $kategoriNormalized,
                        'status_produk' => $statusProdukNormalized,
                        'stok' => $stok,
                        'total_penjualan' => $totalPenjualan,
                        'total_dilihat' => $totalDilihat,
                        'total_klik' => $totalKlik,
                        'total_pesanan' => $totalPesanan,
                        'rasio_penjualan' => $rasioPenjualan,
                        'persentase_klik' => $persentaseKlik,
                        'tingkat_konversi' => $tingkatKonversi,
                        'penjualan_per_pesanan' => $penjualanPerPesanan,
                        'total_pembeli' => $totalPembeli,
                        'produk_unik_dilihat' => $produkUnikDilihat,
                        'produk_unik_diklik' => $produkUnikDiklik,
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
     * Normalisasi kategori agar sesuai dengan ENUM database
     */
    private function normalizeKategori($kategori)
    {
        if (empty($kategori)) {
            return 'Panjang';
        }
        
        $kategoriLower = strtolower(trim($kategori));
        
        // Cek mapping
        if (isset($this->kategoriMapping[$kategoriLower])) {
            return $this->kategoriMapping[$kategoriLower];
        }
        
        // Cek langsung apakah nilai valid
        if (in_array($kategori, $this->validKategori)) {
            return $kategori;
        }
        
        // Cek dengan case-insensitive
        foreach ($this->validKategori as $valid) {
            if (strtolower($valid) == $kategoriLower) {
                return $valid;
            }
        }
        
        // Default
        return 'Panjang';
    }
    
    /**
     * Normalisasi status produk
     */
    private function normalizeStatusProduk($status)
    {
        if (empty($status)) {
            return 'Normal';
        }
        
        $statusLower = strtolower(trim($status));
        
        $validStatus = ['Normal', 'Diskon', 'Habis'];
        
        foreach ($validStatus as $valid) {
            if (strtolower($valid) == $statusLower) {
                return $valid;
            }
        }
        
        if (str_contains($statusLower, 'diskon') || str_contains($statusLower, 'promo')) {
            return 'Diskon';
        }
        
        if (str_contains($statusLower, 'habis') || str_contains($statusLower, 'out') || $statusLower == '0') {
            return 'Habis';
        }
        
        return 'Normal';
    }
    
    /**
     * Bersihkan string
     */
    private function cleanString($value)
    {
        if (empty($value)) return null;
        return trim((string) $value);
    }
    
    /**
     * Bersihkan angka dari berbagai format
     */
    private function cleanNumber($value)
    {
        if (empty($value)) return 0;
        
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        $str = trim((string)$value);
        $cleaned = preg_replace('/[^0-9]/', '', $str);
        
        return empty($cleaned) ? 0 : (int) $cleaned;
    }
    
    /**
     * Bersihkan persentase
     */
    private function cleanPercent($value)
    {
        if (empty($value)) return 0;
        
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        $str = trim((string)$value);
        $str = str_replace('%', '', $str);
        $str = str_replace(',', '.', $str);
        $cleaned = preg_replace('/[^0-9\.]/', '', $str);
        
        return empty($cleaned) ? 0 : (float) $cleaned;
    }
    
    /**
     * Map kolom header ke index
     */
    private function mapColumns($header)
    {
        $map = [
            'kode_produk' => null,
            'nama' => null,
            'kategori' => null,
            'status_produk' => null,
            'stok' => null,
            'total_penjualan' => null,
            'total_dilihat' => null,
            'total_klik' => null,
            'total_pesanan' => null,
            'rasio_penjualan' => null,
            'persentase_klik' => null,
            'tingkat_konversi' => null,
            'penjualan_per_pesanan' => null,
            'total_pembeli' => null,
            'produk_unik_dilihat' => null,
            'produk_unik_diklik' => null,
        ];
        
        $keywords = [
            'kode_produk' => ['kode_produk', 'kode', 'product_code', 'code', 'sku', 'id_produk'],
            'nama' => ['nama', 'produk', 'product', 'name', 'product_name', 'item'],
            'kategori' => ['kategori', 'category', 'kate', 'jenis'],
            'status_produk' => ['status_produk', 'status', 'state', 'condition'],
            'stok' => ['stok', 'stock', 'qty', 'quantity', 'jumlah', 'stok_tersedia'],
            'total_penjualan' => ['total_penjualan', 'penjualan', 'sales', 'revenue', 'omzet', 'terjual'],
            'total_dilihat' => ['total_dilihat', 'dilihat', 'views', 'view', 'impressions'],
            'total_klik' => ['total_klik', 'klik', 'clicks', 'click'],
            'total_pesanan' => ['total_pesanan', 'pesanan', 'orders', 'order'],
            'rasio_penjualan' => ['rasio_penjualan', 'rasio', 'ratio', 'sales_ratio'],
            'persentase_klik' => ['persentase_klik', 'ctr', 'click_rate', 'clickrate', 'ctr_%'],
            'tingkat_konversi' => ['tingkat_konversi', 'cr', 'conversion', 'conversion_rate', 'cr_%'],
            'penjualan_per_pesanan' => ['penjualan_per_pesanan', 'avg_order', 'aov', 'order_value'],
            'total_pembeli' => ['total_pembeli', 'pembeli', 'buyers', 'customer', 'unique_buyers'],
            'produk_unik_dilihat' => ['produk_unik_dilihat', 'unique_views', 'unique_view'],
            'produk_unik_diklik' => ['produk_unik_diklik', 'unique_clicks', 'unique_click'],
        ];
        
        foreach ($header as $index => $column) {
            $columnLower = strtolower(trim((string)$column));
            
            foreach ($keywords as $field => $fieldKeywords) {
                if ($map[$field] === null) {
                    foreach ($fieldKeywords as $keyword) {
                        if (str_contains($columnLower, $keyword)) {
                            $map[$field] = $index;
                            break;
                        }
                    }
                }
            }
        }
        
        // Set default index untuk kolom wajib jika tidak ditemukan
        if ($map['kode_produk'] === null) $map['kode_produk'] = 0;
        if ($map['nama'] === null) $map['nama'] = 1;
        if ($map['stok'] === null) $map['stok'] = 2;
        
        return $map;
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
    
    public function getErrors()
    {
        return $this->errors;
    }
}