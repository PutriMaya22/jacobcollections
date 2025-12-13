<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = 'dashboard_penjualan_' . (Penjualan::max('updated_at') ?? now());

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () {

            /* =====================
             * STATISTIK UTAMA
             * ===================== */
            $totalBarang = Barang::count();
            $totalKategori = Barang::distinct('kategori')->count('kategori');

            $totalPenjualan = Penjualan::sum('total_penjualan') ?? 0;
            $totalPesanan = Penjualan::sum('total_pesanan') ?? 0;

            /* =====================
             * CHART BARANG PER KATEGORI
             * ===================== */
            $kategoriLabels = Barang::select('kategori')
                ->groupBy('kategori')
                ->pluck('kategori')
                ->toArray();

            $barangPerKategori = Barang::selectRaw('kategori, COUNT(*) as total')
                ->groupBy('kategori')
                ->pluck('total')
                ->toArray();

            /* =====================
             * CHART PENJUALAN PER TANGGAL
             * ===================== */
            $tanggalLabels = Penjualan::orderBy('tanggal')
                ->pluck('tanggal')
                ->map(fn ($t) => date('d M Y', strtotime($t)))
                ->toArray();

            $penjualanPerTanggal = Penjualan::orderBy('tanggal')
                ->pluck('total_penjualan')
                ->toArray();

            /* =====================
             * AKTIVITAS TERBARU (PENJUALAN)
             * ===================== */
            $recentPenjualan = Penjualan::latest('tanggal')
                ->take(5)
                ->get();

            return [
                // Card
                'totalBarang' => $totalBarang,
                'totalKategori' => $totalKategori,
                'totalPenjualan' => $totalPenjualan,
                'totalPesanan' => $totalPesanan,

                // Chart Barang
                'kategoriLabels' => $kategoriLabels,
                'barangPerKategori' => $barangPerKategori,

                // Chart Penjualan
                'tanggalLabels' => $tanggalLabels,
                'penjualanPerTanggal' => $penjualanPerTanggal,

                // Table
                'recentPenjualan' => $recentPenjualan,
            ];
        });

        return view('dashboard', $data);
    }
}
