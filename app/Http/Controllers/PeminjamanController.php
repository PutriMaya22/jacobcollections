<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjaman = Peminjaman::with(['barang', 'barang.ruangan'])->latest()->paginate(10);
        $ruanganOptions = Ruangan::all();
        
        // Data barang dikelompokkan berdasarkan ruangan untuk JavaScript
        $barangByRuangan = Barang::with('ruangan')
            ->where('status', 'tersedia')
            ->where('total', '>', 0) // Hanya barang dengan stok > 0
            ->get()
            ->groupBy('id_ruangan')
            ->map(function($barang) {
                return $barang->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'total' => $item->total,
                        'kategori' => $item->kategori,
                        'nomor_seri' => $item->nomor_seri
                    ];
                });
            });
        
        // Data untuk chart
        $chartData = Peminjaman::select(
            DB::raw('MONTH(tanggal_pinjam) as month'),
            DB::raw('YEAR(tanggal_pinjam) as year'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
        
        $months = [];
        $totals = [];
        
        foreach ($chartData as $data) {
            $months[] = date('M Y', mktime(0, 0, 0, $data->month, 1, $data->year));
            $totals[] = $data->total;
        }
        
        return view('operasional.peminjaman.index', compact(
            'peminjaman', 
            'ruanganOptions', 
            'barangByRuangan', 
            'months', 
            'totals'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ruangan' => 'required|exists:ruangans,id',
            'barang_id' => 'required|exists:barangs,id',
            'peminjam' => 'required',
            'tanggal_pinjam' => 'required|date',
            'batas_waktu' => 'nullable|date|after_or_equal:tanggal_pinjam',
            'jumlah' => 'required|integer|min:1',
            'keperluan' => 'required',
            'penanggung_jawab' => 'required'
        ]);

        // Validasi bahwa barang benar-benar ada di ruangan yang dipilih
        $barang = Barang::where('id', $request->barang_id)
                        ->where('id_ruangan', $request->id_ruangan)
                        ->first();

        if (!$barang) {
            return back()
                ->withInput()
                ->with('error', 'Barang tidak ditemukan di ruangan yang dipilih.');
        }

        // Cek stok cukup
        if ($barang->total < $request->jumlah) {
            return back()
                ->withInput()
                ->with('error', 'Stok barang tidak mencukupi. Stok tersedia: ' . $barang->total);
        }

        try {
            DB::beginTransaction();

            // Buat peminjaman
            Peminjaman::create($request->all());

            // Kurangi stok barang
            $barang->decrement('total', $request->jumlah);

            // Update status barang jika stok habis
            if ($barang->total == 0) {
                $barang->update(['status' => 'dipinjam']);
            }

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Peminjaman berhasil dicatat');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function kembali($id)
    {
        try {
            DB::beginTransaction();

            $peminjaman = Peminjaman::findOrFail($id);
            $barang = $peminjaman->barang;

            // Kembalikan stok barang
            $barang->increment('total', $peminjaman->jumlah);

            // Update status peminjaman
            $peminjaman->update([
                'tanggal_kembali' => now(),
                'status' => 'dikembalikan'
            ]);

            // Update status barang
            if ($barang->total > 0 && $barang->status == 'dipinjam') {
                $barang->update(['status' => 'tersedia']);
            }

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Barang berhasil dikembalikan');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Peminjaman $peminjaman)
    {
        try {
            DB::beginTransaction();

            // Kembalikan stok barang sebelum menghapus
            $barang = $peminjaman->barang;
            $barang->increment('total', $peminjaman->jumlah);

            // Update status barang jika perlu
            if ($barang->total > 0 && $barang->status == 'dipinjam') {
                $barang->update(['status' => 'tersedia']);
            }

            $peminjaman->delete();

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman berhasil dihapus');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Method untuk mengambil barang berdasarkan ruangan (AJAX)
    public function getBarangByRuangan($ruanganId)
    {
        $barang = Barang::where('id_ruangan', $ruanganId)
                        ->where('status', 'tersedia')
                        ->where('total', '>', 0)
                        ->get(['id', 'nama', 'total', 'kategori', 'nomor_seri']);
        
        return response()->json($barang);
    }
}