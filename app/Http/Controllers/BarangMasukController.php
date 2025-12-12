<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    public function index()
    {
        $barangMasuk = BarangMasuk::with('barang')->latest()->paginate(10);
        $barangOptions = Barang::all();
        
        // Data untuk chart
        $chartData = BarangMasuk::select(
            DB::raw('MONTH(tanggal_masuk) as month'),
            DB::raw('YEAR(tanggal_masuk) as year'),
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
        
        return view('operasional.barang_masuk.index', compact('barangMasuk', 'barangOptions', 'months', 'totals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barangs,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal_masuk' => 'required|date',
            'supplier' => 'required|string|max:255',
            'penerima' => 'required|string|max:255',
            'keterangan' => 'nullable|string'
        ]);
        
        // Tambahkan barang masuk
        $barangMasuk = BarangMasuk::create($request->all());
        
        // Update stok barang
        $barang = Barang::find($request->id_barang);
        $barang->increment('total', $request->jumlah);
        
        return redirect()->route('barang_masuk.index')->with('success', 'Barang masuk berhasil dicatat');
    }

    public function destroy($id)
    {
        $barangMasuk = BarangMasuk::findOrFail($id);
        $barang = $barangMasuk->barang;
        
        // Kurangi stok barang sebelum menghapus
        $barang->decrement('total', $barangMasuk->jumlah);
        
        $barangMasuk->delete();
        
        return redirect()->route('barang_masuk.index')->with('success', 'Catatan barang masuk berhasil dihapus');
    }
}