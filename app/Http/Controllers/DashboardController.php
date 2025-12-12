<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Ruangan;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = 'dashboard_stats_' . auth()->id() . '_' . (Barang::max('updated_at') ?? now());
        
        $data = Cache::remember($cacheKey, now()->addMinutes(15), function () {
            // Data dasar
            $totalBarang = Barang::count();
            $totalQuantity = Barang::sum('total') ?? 0;
            $totalRuangan = Ruangan::count();
            
            // Data kondisi barang
            $barangBaik = Barang::where('kondisi', 'baik')->count();
            $barangBaikQuantity = Barang::where('kondisi', 'baik')->sum('total') ?? 0;
            $barangRusak = Barang::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count();
            $barangRusakQuantity = Barang::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->sum('total') ?? 0;
            
            // Hitung persentase dengan pengecekan division by zero
            $persenBaik = $totalBarang > 0 ? ($barangBaik / $totalBarang) * 100 : 0;
            $persenRusak = $totalBarang > 0 ? ($barangRusak / $totalBarang) * 100 : 0;
            $persenBaikQuantity = $totalQuantity > 0 ? ($barangBaikQuantity / $totalQuantity) * 100 : 0;
            $persenRusakQuantity = $totalQuantity > 0 ? ($barangRusakQuantity / $totalQuantity) * 100 : 0;
            
            // Data untuk chart distribusi ruangan
            $ruanganLabels = Ruangan::pluck('nama')->toArray() ?? [];
            $barangPerRuangan = Ruangan::withCount('barangs')->pluck('barangs_count')->toArray() ?? [];
            $barangQuantityPerRuangan = Ruangan::withSum('barangs', 'total')->pluck('barangs_sum_total')->toArray() ?? [];
            
            // Data default jika tidak ada data
            $defaultChartData = [0, 0, 0];
            $defaultChartLabels = ['Tersedia', 'Dipinjam', 'Perbaikan'];
            $defaultKondisiLabels = ['Baik', 'Rusak Ringan', 'Rusak Berat'];
            
            // Data status barang
            $statusBarang = $totalBarang > 0 ? [
                Barang::where('status', 'tersedia')->count(),
                Barang::where('status', 'dipinjam')->count(),
                Barang::where('status', 'perbaikan')->count()
            ] : $defaultChartData;
            
            $statusBarangQuantity = $totalQuantity > 0 ? [
                Barang::where('status', 'tersedia')->sum('total') ?? 0,
                Barang::where('status', 'dipinjam')->sum('total') ?? 0,
                Barang::where('status', 'perbaikan')->sum('total') ?? 0
            ] : $defaultChartData;
            
            // Data kondisi barang
            $kondisiBarang = $totalBarang > 0 ? [
                Barang::where('kondisi', 'baik')->count(),
                Barang::where('kondisi', 'rusak_ringan')->count(),
                Barang::where('kondisi', 'rusak_berat')->count()
            ] : $defaultChartData;
            
            $kondisiBarangQuantity = $totalQuantity > 0 ? [
                Barang::where('kondisi', 'baik')->sum('total') ?? 0,
                Barang::where('kondisi', 'rusak_ringan')->sum('total') ?? 0,
                Barang::where('kondisi', 'rusak_berat')->sum('total') ?? 0
            ] : $defaultChartData;
            
            // Aktivitas terbaru
            $recentActivities = Barang::with('ruangan')
                ->latest()
                ->take(5)
                ->get();

            return [
                'totalBarang' => $totalBarang,
                'totalQuantity' => $totalQuantity,
                'totalRuangan' => $totalRuangan,
                'barangBaik' => $barangBaik,
                'barangBaikQuantity' => $barangBaikQuantity,
                'barangRusak' => $barangRusak,
                'barangRusakQuantity' => $barangRusakQuantity,
                'persenBaik' => $persenBaik,
                'persenRusak' => $persenRusak,
                'persenBaikQuantity' => $persenBaikQuantity,
                'persenRusakQuantity' => $persenRusakQuantity,
                'ruanganLabels' => $ruanganLabels,
                'barangPerRuangan' => $barangPerRuangan,
                'barangQuantityPerRuangan' => $barangQuantityPerRuangan,
                'statusBarang' => $statusBarang,
                'statusBarangQuantity' => $statusBarangQuantity,
                'kondisiBarang' => $kondisiBarang,
                'kondisiBarangQuantity' => $kondisiBarangQuantity,
                'recentActivities' => $recentActivities,
                'defaultChartLabels' => $defaultChartLabels,
                'defaultKondisiLabels' => $defaultKondisiLabels,
                'hasData' => $totalBarang > 0
            ];
        });

        return view('dashboard', $data);
    }
}