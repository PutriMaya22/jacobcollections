<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BarangImport;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();
        
        if ($request->filled('status_produk')) {
            $query->where('status_produk', $request->status_produk);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_produk', 'like', '%' . $request->search . '%')
                  ->orWhere('kategori', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        
        if ($request->filled('stok_status')) {
            if ($request->stok_status == 'habis') {
                $query->where('stok', '<=', 0);
            } elseif ($request->stok_status == 'menipis') {
                $query->where('stok', '>', 0)->where('stok', '<=', 10);
            } elseif ($request->stok_status == 'aman') {
                $query->where('stok', '>', 10);
            }
        }
        
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSort = ['nama', 'stok', 'kategori', 'total_penjualan', 'kode_produk'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'nama';
        }
        $query->orderBy($sortBy, $sortOrder);
        
        $perPage = $request->input('per_page', 20);
        $barang = $query->paginate($perPage);
        
        $kategoriList = Barang::select('kategori')->distinct()->whereNotNull('kategori')->pluck('kategori');
        $totalBarangNormal = Barang::where('status_produk', 'Normal')->count();
        $statusDiblokir = Barang::where('status_produk', 'Diblokir')->count();
        $statusDiarsipkan = Barang::where('status_produk', 'Diarsipkan')->count();
        $totalSemuaBarang = Barang::count();
        $totalStok = Barang::sum('stok') ?? 0;
        $barangHabis = Barang::where('stok', '<=', 0)->count();
        $barangMenipis = Barang::where('stok', '>', 0)->where('stok', '<=', 10)->count();
        $totalNilaiStok = 0;
        $totalPesanan = (int) DB::table('data_barang')->sum('total_pesanan');
        
        return view('data_barang.index', compact(
            'barang', 'kategoriList', 'totalBarangNormal', 'totalSemuaBarang',
            'totalStok', 'barangHabis', 'barangMenipis', 'totalNilaiStok',
            'totalPesanan', 'statusDiblokir', 'statusDiarsipkan'
        ));
    }

    public function create()
    {
        return view('data_barang.create');
    }

   public function store(Request $request)
{
    // Bersihkan total penjualan (hapus titik)
    $cleanTotalPenjualan = str_replace('.', '', $request->total_penjualan);
    
    // Data dasar
    $data = [
        'kode_produk' => $request->kode_produk,
        'nama' => $request->nama,
        'kategori' => $request->kategori,
        'status_produk' => $request->status_produk,
        'stok' => (int) $request->stok,
        'total_penjualan' => (int) $cleanTotalPenjualan,
        'total_dilihat' => (int) $request->total_dilihat ?: 0,
        'total_klik' => (int) $request->total_klik ?: 0,
        'total_pesanan' => (int) $request->total_pesanan ?: 0,
        'total_pembeli' => (int) $request->total_pembeli ?? 0,
        // 🔥🔥🔥 AMBIL DARI FORM (sudah terbukti ada nilainya 2 dan 2) 🔥🔥🔥
        'persentase_klik' => (float) ($request->persentase_klik ?: 0),
        'tingkat_konversi' => (float) ($request->tingkat_konversi ?: 0),
        'penjualan_per_pesanan' => 0,
        'rasio_penjualan' => 0,
    ];
    
    // Validasi
    $request->validate([
        'nama' => 'required',
        'kategori' => 'required',
        'stok' => 'required|integer|min:0',
    ]);
    
    // Simpan
    Barang::create($data);
    
    // Redirect dengan pesan sukses
    return redirect()->route('data_barang.index')
        ->with('success', "Produk ditambahkan! CTR: {$data['persentase_klik']}%, CR: {$data['tingkat_konversi']}%");
}

    public function show($id)
    {
        $barang = Barang::findOrFail($id);
        return view('data_barang.show', compact('barang'));
    }

    public function edit($id)
    {
        $data_barang = Barang::findOrFail($id);
        return view('data_barang.edit', compact('data_barang'));
    }

    // 🔥🔥🔥 UPDATE DENGAN INPUT MANUAL 🔥🔥🔥
    public function update(Request $request, $id)
    {
        $data_barang = Barang::findOrFail($id);
        
        $cleanTotalPenjualan = str_replace('.', '', $request->total_penjualan);
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'stok' => 'required|integer|min:0',
            'status_produk' => 'nullable|string|max:50',
        ]);
        
        $data_barang->update([
            'kode_produk' => $request->kode_produk,
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'stok' => (int) $request->stok ?: 0,
            'status_produk' => $request->status_produk,
            'total_penjualan' => (int) $cleanTotalPenjualan ?: 0,
            'total_dilihat' => 0,
            'total_klik' => 0,
            'total_pesanan' => (int) $request->total_pesanan ?: 0,
            'total_pembeli' => (int) $request->total_pembeli ?: 0,
            'persentase_klik' => (float) $request->persentase_klik ?: 0,
            'tingkat_konversi' => (float) $request->tingkat_konversi ?: 0,
        ]);
        
        return redirect()->route('data_barang.index')
            ->with('success', 'Barang berhasil diupdate!');
    }

    public function destroy($id)
    {
        try {
            $barang = Barang::findOrFail($id);
            $barang->delete();
            return redirect()->route('data_barang.index')->with('success', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2030',
        ]);

        try {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $tanggalPenjualan = date("$tahun-$bulan-01");
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