<?php

namespace App\Http\Controllers;

use App\Models\Pemeliharaan;
use App\Models\Barang;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemeliharaanController extends Controller
{
    public function index()
    {
        $pemeliharaan = Pemeliharaan::with(['barang', 'barang.ruangan'])->latest()->paginate(10);
        $ruanganOptions = Ruangan::all();
        
        // Data barang dikelompokkan berdasarkan ruangan untuk JavaScript
        $barangByRuangan = Barang::with('ruangan')
            ->where('kondisi', '!=', 'rusak_berat')
            ->where('status', '!=', 'perbaikan') // Exclude barang yang sedang diperbaiki
            ->get()
            ->groupBy('id_ruangan')
            ->map(function($barang) {
                return $barang->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'total' => $item->total,
                        'kategori' => $item->kategori,
                        'kondisi' => $item->kondisi,
                        'nomor_seri' => $item->nomor_seri
                    ];
                });
            });
        
        // Data untuk chart
        $chartData = Pemeliharaan::select(
            DB::raw('MONTH(tanggal_mulai) as month'),
            DB::raw('YEAR(tanggal_mulai) as year'),
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
        
        return view('operasional.pemeliharaan.index', compact(
            'pemeliharaan', 
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
            'tanggal_mulai' => 'required|date',
            'jenis_pemeliharaan' => 'required|in:rutin,preventif,korektif',
            'biaya' => 'required|numeric|min:0',
            'keterangan' => 'required',
            'teknisi' => 'required',
            'status' => 'required|in:dijadwalkan,dalam_pengerjaan,selesai,ditunda'
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

        // Validasi barang tidak sedang dalam perbaikan
        if ($barang->status == 'perbaikan') {
            return back()
                ->withInput()
                ->with('error', 'Barang sedang dalam proses perbaikan.');
        }

        try {
            DB::beginTransaction();
            
            $pemeliharaan = Pemeliharaan::create($request->all());

            // Update status barang jika pemeliharaan sedang berlangsung
            if ($request->status == 'dalam_pengerjaan') {
                $barang->update(['status' => 'perbaikan']);
            }

            DB::commit();
            
            return redirect()->route('pemeliharaan.index')
                ->with('success', 'Jadwal pemeliharaan berhasil dibuat');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Pemeliharaan $pemeliharaan)
    {
        $request->validate([
            'id_ruangan' => 'required|exists:ruangans,id',
            'barang_id' => 'required|exists:barangs,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date',
            'jenis_pemeliharaan' => 'required|in:rutin,preventif,korektif',
            'biaya' => 'required|numeric|min:0',
            'keterangan' => 'required',
            'teknisi' => 'required',
            'status' => 'required|in:dijadwalkan,dalam_pengerjaan,selesai,ditunda'
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

        try {
            DB::beginTransaction();
            
            $oldStatus = $pemeliharaan->status;
            $pemeliharaan->update($request->all());

            // Update status barang berdasarkan status pemeliharaan
            if ($request->status == 'selesai') {
                $barang->update(['status' => 'tersedia', 'kondisi' => 'baik']);
            } elseif ($request->status == 'dalam_pengerjaan') {
                $barang->update(['status' => 'perbaikan']);
            } elseif ($request->status == 'dijadwalkan' || $request->status == 'ditunda') {
                // Jika status berubah dari dalam_pengerjaan ke dijadwalkan/ditunda
                if ($oldStatus == 'dalam_pengerjaan') {
                    $barang->update(['status' => 'tersedia']);
                }
            }

            DB::commit();
            
            return redirect()->route('pemeliharaan.index')
                ->with('success', 'Data pemeliharaan berhasil diupdate');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Pemeliharaan $pemeliharaan)
    {
        try {
            DB::beginTransaction();
            
            $barang = $pemeliharaan->barang;
            
            // Jika pemeliharaan sedang dalam pengerjaan, kembalikan status barang
            if ($pemeliharaan->status == 'dalam_pengerjaan') {
                $barang->update(['status' => 'tersedia']);
            }
            
            $pemeliharaan->delete();
            
            DB::commit();
            
            return redirect()->route('pemeliharaan.index')
                ->with('success', 'Data pemeliharaan berhasil dihapus');
                
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
                        ->where('kondisi', '!=', 'rusak_berat')
                        ->where('status', '!=', 'perbaikan')
                        ->get(['id', 'nama', 'total', 'kategori', 'kondisi', 'nomor_seri']);
        
        return response()->json($barang);
    }
}