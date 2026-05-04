<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BarangImport;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Barang::query();
        
        // Filter status (opsional)
        if ($request->filled('status_produk')) {
            $query->where('status_produk', $request->status_produk);
        }
        
        // Filter search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_produk', 'like', '%' . $request->search . '%')
                  ->orWhere('kategori', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        
        // Filter stok status
        if ($request->filled('stok_status')) {
            if ($request->stok_status == 'habis') {
                $query->where('stok', '<=', 0);
            } elseif ($request->stok_status == 'menipis') {
                $query->where('stok', '>', 0)->where('stok', '<=', 10);
            } elseif ($request->stok_status == 'aman') {
                $query->where('stok', '>', 10);
            }
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        
        $allowedSort = ['nama', 'stok', 'kategori', 'total_penjualan', 'kode_produk'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'nama';
        }
        $query->orderBy($sortBy, $sortOrder);
        
        $perPage = $request->input('per_page', 20);
        $barang = $query->paginate($perPage);
        
        // Ambil daftar kategori unik untuk filter
        $kategoriList = Barang::select('kategori')->distinct()->whereNotNull('kategori')->pluck('kategori');
        
        // HITUNG STATUS PRODUK
        $totalBarangNormal = Barang::where('status_produk', 'Normal')->count();
        $statusDiblokir = Barang::where('status_produk', 'Diblokir')->count();
        $statusDiarsipkan = Barang::where('status_produk', 'Diarsipkan')->count();
        
        // TOTAL SEMUA PRODUK (untuk keperluan lain jika perlu)
        $totalSemuaBarang = Barang::count();
        
        // Hitung stok (tetap dari semua produk)
        $totalStok = Barang::sum('stok') ?? 0;
        $barangHabis = Barang::where('stok', '<=', 0)->count();
        $barangMenipis = Barang::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $totalNilaiStok = 0; // Bisa diisi jika ada kolom harga
        $totalPesanan = (int) DB::table('data_barang')->sum('total_pesanan');
        
        return view('data_barang.index', compact(
            'barang', 
            'kategoriList', 
            'totalBarangNormal',
            'totalSemuaBarang',
            'totalStok',
            'barangHabis',
            'barangMenipis',
            'totalNilaiStok',
            'totalPesanan',
            'statusDiblokir',
            'statusDiarsipkan'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('data_barang.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|string|max:50|unique:data_barang,kode_produk',
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'stok' => 'required|integer|min:0',
            'status_produk' => 'nullable|string|max:50',
        ]);
        
        try {
            Barang::create([
                'kode_produk' => $request->kode_produk,
                'nama' => $request->nama,
                'kategori' => $request->kategori,
                'stok' => $request->stok,
                'status_produk' => $request->status_produk,
                'total_penjualan' => 0,
                'total_dilihat' => 0,
                'total_klik' => 0,
                'total_pesanan' => 0,
                'persentase_klik' => 0,
                'tingkat_konversi' => 0,
                'penjualan_per_pesanan' => 0,
            ]);
            
            return redirect()->route('data_barang.index')
                ->with('success', 'Produk berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $barang = Barang::findOrFail($id);
        return view('data_barang.show', compact('barang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data_barang = Barang::findOrFail($id);
        return view('data_barang.edit', compact('data_barang'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data_barang = Barang::findOrFail($id);
        
        $validated = $request->validate([
            'kode_produk' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'status_produk' => 'nullable|string|max:50',
            'total_penjualan' => 'nullable|integer',
            'total_dilihat' => 'nullable|integer',
            'total_klik' => 'nullable|integer',
            'total_pesanan' => 'nullable|integer',
            'total_pembeli' => 'nullable|integer',
            'stok' => 'required|integer|min:0',
        ]);
        
        // Bersihkan format rupiah jika ada (dari form input)
        if (isset($validated['total_penjualan']) && is_string($validated['total_penjualan'])) {
            $validated['total_penjualan'] = (int) str_replace('.', '', $validated['total_penjualan']);
        }
        
        // Hitung rasio penjualan, persentase klik, dan tingkat konversi
        $totalDilihat = $validated['total_dilihat'] ?? 0;
        $totalKlik = $validated['total_klik'] ?? 0;
        $totalPesanan = $validated['total_pesanan'] ?? 0;
        $totalPenjualan = $validated['total_penjualan'] ?? 0;
        
        $validated['persentase_klik'] = $totalDilihat > 0 ? ($totalKlik / $totalDilihat) * 100 : 0;
        $validated['tingkat_konversi'] = $totalKlik > 0 ? ($totalPesanan / $totalKlik) * 100 : 0;
        $validated['penjualan_per_pesanan'] = $totalPesanan > 0 ? $totalPenjualan / $totalPesanan : 0;
        $validated['rasio_penjualan'] = 0;
        
        $data_barang->update($validated);
        
        return redirect()->route('data_barang.index')->with('success', 'Barang berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $barang = Barang::findOrFail($id);
            $barang->delete();
            
            return redirect()->route('data_barang.index')
                ->with('success', 'Produk berhasil dihapus!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }
    
 /**
 * Import data from Excel
 */
public function import(Request $request)
{
    // Validasi input
    $request->validate([
        'file'  => 'required|mimes:xlsx,xls,csv|max:2048',
        'bulan' => 'required|integer|min:1|max:12',
        'tahun' => 'required|integer|min:2020|max:2030',
    ]);

    try {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $tanggalPenjualan = date("$tahun-$bulan-01");
        
        // 🔥 KIRIM TANGGAL KE IMPORT CLASS
        $import = new \App\Imports\BarangImport($tanggalPenjualan);
        
        $file = $request->file('file');
        $import->import($file->getPathName());
        
        $successCount = $import->getSuccessCount();
        $failedCount = $import->getFailedCount();
        
        $namaBulan = $this->getNamaBulan($bulan);
        $message = "✅ Berhasil import {$successCount} data untuk {$namaBulan} {$tahun}";
        
        if ($failedCount > 0) {
            $message .= " | ❌ Gagal: {$failedCount} data";
        }
        
        return redirect()->back()->with('success', $message);
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

// Helper function bulan
private function getNamaBulan($bulan)
{
    $nama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $nama[$bulan] ?? 'Unknown';
}
}