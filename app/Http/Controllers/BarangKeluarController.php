<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
    public function index()
    {
        $barangKeluar = BarangKeluar::with(['barang', 'barang.ruangan'])->latest()->paginate(10);
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
        $chartData = BarangKeluar::select(
            DB::raw('MONTH(tanggal_keluar) as month'),
            DB::raw('YEAR(tanggal_keluar) as year'),
            DB::raw('SUM(jumlah) as total')
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
        
        return view('operasional.barang_keluar.index', compact(
            'barangKeluar', 
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
            'id_barang' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal_keluar' => 'required|date',
            'tujuan' => 'required|string|max:255',
            'penanggung_jawab' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);

        // Validasi bahwa barang benar-benar ada di ruangan yang dipilih
        $barang = Barang::where('id', $request->id_barang)
                        ->where('id_ruangan', $request->id_ruangan)
                        ->first();

        if (!$barang) {
            return back()
                ->withInput()
                ->with('error', 'Barang tidak ditemukan di ruangan yang dipilih.');
        }
        
        // Validasi stok cukup dengan pesan error khusus
        if ($barang->total < $request->jumlah) {
            return back()
                ->withInput()
                ->with('error', 'Stok barang tidak mencukupi. Stok tersedia: ' . $barang->total);
        }
        
        try {
            DB::beginTransaction();
            
            // Tambahkan barang keluar
            $barangKeluar = BarangKeluar::create($request->all());
            
            // Kurangi stok barang
            $barang->decrement('total', $request->jumlah);
            
            // Update status barang jika stok habis
            if ($barang->total == 0) {
                $barang->update(['status' => 'habis']);
            }
            
            DB::commit();
            
            return redirect()->route('barang_keluar.index')
                ->with('success', 'Barang keluar berhasil dicatat');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $barangKeluar = BarangKeluar::findOrFail($id);
            $barang = $barangKeluar->barang;
            
            // Tambahkan kembali stok barang sebelum menghapus
            $barang->increment('total', $barangKeluar->jumlah);
            
            // Update status barang kembali ke tersedia jika sebelumnya habis
            if ($barang->status == 'habis' && $barang->total > 0) {
                $barang->update(['status' => 'tersedia']);
            }
            
            $barangKeluar->delete();
            
            DB::commit();
            
            return redirect()->route('barang_keluar.index')
                ->with('success', 'Catatan barang keluar berhasil dihapus');
                
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