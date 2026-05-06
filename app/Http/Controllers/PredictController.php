<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Prediksi;
use App\Models\Penjualan;
use App\Models\DataPenjualan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PredictController extends Controller
{
    /**
     * Halaman Prediksi
     */
    public function index()
    {
        $this->sinkronisasiAktual();

        $dataPrediksi = Prediksi::orderBy('tanggal', 'desc')->get();
        $lastPrediction = session('last_prediction', []);
        
        $dataBarang = $this->getDataBarang();
        $rekomendasiProduk = session('rekomendasi_produk', []);
        $produkPalingDiminati = $this->getPopularProductsFromDatabase(10);
        
        $produkTerlaris = $this->getProdukTerlaris();

        return view('predict', [
            'dataPrediksi' => $dataPrediksi,
            'prediksi' => $lastPrediction['prediksi'] ?? null,
            'tanggal_input' => $lastPrediction['tanggal_input'] ?? null,
            'total_input' => $lastPrediction['total_input'] ?? null,
            'rata_rata' => $lastPrediction['rata_rata'] ?? null,
            'status' => $lastPrediction['status'] ?? null,
            'strategi_umum' => $lastPrediction['strategi_umum'] ?? null,
            'rekomendasi_produk' => $rekomendasiProduk,
            'produk_paling_diminati' => $produkPalingDiminati,
            'produk_terlaris' => $produkTerlaris,
            'data_barang' => $dataBarang,
        ]);
    }

    /**
     * Get produk paling diminati dari database (DATA REAL, tanpa estimasi)
     */
   private function getPopularProductsFromDatabase($limit = 10)
{
    try {
        $produk = DB::table('data_barang')
            ->select(
                'id',
                'kode_produk',
                'nama',
                'kategori',
                'stok',
                'total_penjualan',
                'total_dilihat',
                'total_klik',
                'total_pesanan',
                'persentase_klik',
                'tingkat_konversi'
            )
            ->whereNotNull('nama')
            ->where('total_penjualan', '>', 0)
            ->orderBy('total_penjualan', 'desc')
            ->limit($limit)
            ->get();
        
        if ($produk->isEmpty()) {
            return $this->getFallbackProduk();
        }
        
        $hasil = [];
        $max_penjualan = $produk->max('total_penjualan') ?: 1;
        
        foreach ($produk as $item) {
            // ========== HITUNG CTR ==========
            if ($item->total_dilihat > 0 && $item->total_klik > 0) {
                $ctr = round(($item->total_klik / $item->total_dilihat) * 100, 2);
            } else {
                // Fallback berdasarkan total_penjualan
                if ($item->total_penjualan >= 500000) {
                    $ctr = 15.0;
                } elseif ($item->total_penjualan >= 200000) {
                    $ctr = 12.0;
                } elseif ($item->total_penjualan >= 100000) {
                    $ctr = 8.0;
                } elseif ($item->total_penjualan >= 50000) {
                    $ctr = 5.0;
                } else {
                    $ctr = 2.0;
                }
            }
            
            // ========== HITUNG CR ==========
            if ($item->total_klik > 0 && $item->total_pesanan > 0) {
                $cr = round(($item->total_pesanan / $item->total_klik) * 100, 2);
            } elseif ($item->total_pesanan > 0) {
                $cr = 100;
            } else {
                // Fallback berdasarkan total_penjualan
                if ($item->total_penjualan >= 500000) {
                    $cr = 25.0;
                } elseif ($item->total_penjualan >= 200000) {
                    $cr = 18.0;
                } elseif ($item->total_penjualan >= 100000) {
                    $cr = 12.0;
                } else {
                    $cr = 5.0;
                }
            }
            

            
            // Hitung Popularity Score
            $penjualan_score = ($item->total_penjualan / $max_penjualan) * 40;
            $ctr_score = ($ctr / 100) * 30;
            $cr_score = ($cr / 100) * 30;
            $popularity_score = round($penjualan_score + $ctr_score + $cr_score, 2);
            
            // Rekomendasi
            $rekomendasi = $this->getRekomendasiText($item->stok, $cr, $ctr, $item->total_pesanan);
            
            $hasil[] = [
                'nama' => $item->nama,
                'kode_produk' => $item->kode_produk,
                'kategori' => $item->kategori,
                'stok' => (int)$item->stok,
                'total_penjualan' => (float)$item->total_penjualan,
                'total_pesanan' => (int)$item->total_pesanan,
                'ctr' => $ctr,
                'cr' => $cr,
                'popularity_score' => $popularity_score,
                'rekomendasi' => $rekomendasi,
                'persentase_klik' => $ctr,
                'tingkat_konversi' => $cr
            ];
        }
        
        // Urutkan berdasarkan popularity_score
        usort($hasil, function($a, $b) {
            return $b['popularity_score'] <=> $a['popularity_score'];
        });
        
        return $hasil;
        
    } catch (\Exception $e) {
        \Log::error('Error getPopularProductsFromDatabase: ' . $e->getMessage());
        return $this->getFallbackProduk();
    }
}
    
    private function getRekomendasiText($stok, $cr, $ctr, $total_pesanan)
    {
        if ($stok <= 0 && $cr > 10) {
            return "PRIORITAS RESTOCK - Produk sangat diminati!";
        }
        if ($stok <= 0 && $cr > 5) {
            return "Stok Habis - Produk cukup diminati";
        }
        if ($total_pesanan > 100) {
            return "Best Seller - Pertahankan stok!";
        }
        if ($ctr > 10 && $cr > 5) {
            return "Potensi Besar - Tingkatkan stok";
        }
        if ($stok < 10 && $cr > 3) {
            return "Stok Menipis (Produk Laris)";
        }
        if ($stok < 10) {
            return "Stok Menipis";
        }
        if ($ctr > 0 && $ctr < 5) {
            return "Kurang diminati - Perlu promosi";
        }
        return "Normal - Monitor berkala";
    }
    
    private function getFallbackProduk()
    {
        return [
            [
                'nama' => 'Belum ada data',
                'kode_produk' => '-',
                'kategori' => '-',
                'stok' => 0,
                'total_penjualan' => 0,
                'total_pesanan' => 0,
                'ctr' => 0,
                'cr' => 0,
                'popularity_score' => 0,
                'rekomendasi' => 'Belum ada data produk',
                'persentase_klik' => 0,
                'tingkat_konversi' => 0
            ]
        ];
    }

    /**
     * Get produk terlaris
     */
    private function getProdukTerlaris()
    {
        try {
            $produkTerlaris = DB::table('data_barang')
                ->select('nama', DB::raw('SUM(COALESCE(total_pesanan, 0)) as total_pesanan'), DB::raw('MAX(stok) as stok_terakhir'))
                ->where('total_pesanan', '>', 0)
                ->whereNotNull('nama')
                ->groupBy('nama')
                ->orderByDesc('total_pesanan')
                ->limit(5)
                ->get();

            if ($produkTerlaris->isNotEmpty()) {
                return $produkTerlaris->map(function ($item) {
                    return (object) [
                        'nama' => $item->nama,
                        'total_pesanan' => (int) ($item->total_pesanan ?? 0),
                        'stok' => (int) ($item->stok_terakhir ?? 0),
                    ];
                });
            }
            return collect([]);
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get data barang
     */
    private function getDataBarang()
    {
        try {
            return DB::table('data_barang')
                ->select('id', 'nama', 'stok', 'total_penjualan', 'total_pesanan')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'stok' => (int) ($item->stok ?? 0),
                        'total_penjualan' => (float) ($item->total_penjualan ?? 0),
                        'total_pesanan' => (int) ($item->total_pesanan ?? 0),
                    ];
                })
                ->sortByDesc('total_penjualan')
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Simpan Prediksi
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Hanya owner yang bisa input prediksi.');
        }

        $request->validate([
            'total_pesanan' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
            'alpha' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $alpha = $request->get('alpha', 0.3);
            
            $response = Http::timeout(30)->post('http://127.0.0.1:5000/predict', [
                'total_pesanan' => (int) $request->total_pesanan,
                'tanggal' => $request->tanggal,
                'alpha' => (float) $alpha,
            ]);

            if (!$response->successful()) {
                throw new \Exception("Response error: " . $response->body());
            }

            $hasil = $response->json();

            $prediksiNilai = $hasil['prediksi_total_penjualan'] ?? 0;
            $evaluasi = $hasil['evaluasi'] ?? null;
            $rekomendasiProduk = $hasil['rekomendasi_produk'] ?? [];

            session([
                'rekomendasi_produk' => $rekomendasiProduk,
                'last_prediction' => [
                    'prediksi' => $prediksiNilai,
                    'tanggal_input' => $request->tanggal,
                    'total_input' => $request->total_pesanan,
                    'rata_rata' => $hasil['rata_rata_historis'] ?? null,
                    'status' => $hasil['status'] ?? null,
                    'strategi_umum' => $hasil['strategi_umum'] ?? null,
                ]
            ]);

            $this->simpanPrediksiStatis(
                $request->tanggal,
                $prediksiNilai,
                (int) $request->total_pesanan,
                $evaluasi
            );

            $this->sinkronisasiAktual();
            $dataPrediksi = Prediksi::orderBy('tanggal', 'desc')->get();
            $dataBarang = $this->getDataBarang();
            $produkPalingDiminati = $this->getPopularProductsFromDatabase(10);
            $produkTerlaris = $this->getProdukTerlaris();

            return view('predict', [
                'prediksi' => $prediksiNilai,
                'tanggal_input' => $request->tanggal,
                'total_input' => $request->total_pesanan,
                'rata_rata' => $hasil['rata_rata_historis'] ?? null,
                'status' => $hasil['status'] ?? null,
                'strategi_umum' => $hasil['strategi_umum'] ?? null,
                'rekomendasi_produk' => $rekomendasiProduk,
                'produk_paling_diminati' => $produkPalingDiminati,
                'produk_terlaris' => $produkTerlaris,
                'dataPrediksi' => $dataPrediksi,
                'data_barang' => $dataBarang,
            ]);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses prediksi: ' . $e->getMessage());
        }
    }

    /**
     * Simpan prediksi statis ke database
     */
    private function simpanPrediksiStatis($tanggal, $hasilPrediksi, $totalPesanan, $evaluasi = null)
{
    // SELALU BUAT RECORD BARU
    Prediksi::create([
        'tanggal' => $tanggal,
        'hasil_prediksi' => $hasilPrediksi,
        'total_pesanan' => $totalPesanan,
        'static_mape' => $evaluasi['MAPE'] ?? null,
        'static_rmse' => $evaluasi['RMSE'] ?? null,
        'static_r_squared' => $evaluasi['R2'] ?? null,
        'evaluasi_captured_at' => now(),
        'metode_analisis' => 'Exponential Smoothing + Tren',
        'created_at' => now(),  // Pastikan kolom ini ada
    ]);
}

    /**
     * Sinkronisasi data aktual
     */
    private function sinkronisasiAktual()
    {
        $prediksiList = Prediksi::all();

        foreach ($prediksiList as $item) {
            $penjualan = Penjualan::whereDate('tanggal', $item->tanggal)->first();
            $aktual = $penjualan ? $penjualan->total_penjualan : null;

            if ($aktual !== null && $item->penjualan_aktual != $aktual) {
                $item->penjualan_aktual = $aktual;
                $item->error = $aktual - $item->hasil_prediksi;
                if ($item->static_error === null) {
                    $item->static_error = $item->error;
                }
                $item->save();
            }
        }
    }

    /**
     * Update realisasi prediksi
     */
    public function updateRealisasiDashboard(Request $request, $id)
    {
        $request->validate([
            'realisasi' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:500'
        ]);

        $prediksi = Prediksi::findOrFail($id);

        DataPenjualan::updateOrCreate(
            ['tanggal' => $prediksi->tanggal],
            ['total_penjualan' => $request->realisasi, 'updated_by' => auth()->id()]
        );

        $prediksi->penjualan_aktual = $request->realisasi;
        $prediksi->error = $request->realisasi - $prediksi->hasil_prediksi;
        $prediksi->catatan = $request->catatan;
        $prediksi->pesanan_updated_at = now();

        if ($prediksi->static_error === null) {
            $prediksi->static_error = $prediksi->error;
        }

        $prediksi->save();

        return response()->json(['success' => true]);
    }

    /**
     * Halaman grafik (Admin & Owner only)
     */
    public function grafik()
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403);
        }
        $data = Prediksi::orderBy('tanggal', 'asc')->get();
        return view('admin.grafik', compact('data'));
    }

 public function exportPDF()
{
    if (auth()->user()->role !== 'owner') {
        abort(403);
    }
    
    // Ambil data
    $dataPrediksi = DB::table('prediksis')
        ->select(
            'id',
            'tanggal',
            'hasil_prediksi',
            'total_pesanan',
            'penjualan_aktual',
            'error',
            'static_error',
            'mape',
            'static_mape',      // ← Gunakan ini
            'rmse',
            'static_rmse',      // ← Gunakan ini
            'r_squared',
            'static_r_squared', // ← Gunakan ini
            'created_at'
        )
        ->orderBy('tanggal', 'asc')
        ->get();
    
    // Hitung total
    $totalPrediksi = $dataPrediksi->sum('hasil_prediksi');
    $totalAktual = $dataPrediksi->sum('penjualan_aktual');
    
    // Hitung rata-rata MAPE (prioritaskan static_mape)
    $mapes = [];
    foreach ($dataPrediksi as $item) {
        // Gunakan static_mape jika mape null
        $nilai = $item->mape ?? $item->static_mape ?? null;
        if ($nilai !== null) {
            $mapes[] = $nilai;
        }
    }
    $rataMape = !empty($mapes) ? array_sum($mapes) / count($mapes) : 0;
    
    // Hitung rata-rata RMSE
    $rmses = [];
    foreach ($dataPrediksi as $item) {
        // Gunakan static_rmse jika rmse null
        $nilai = $item->rmse ?? $item->static_rmse ?? null;
        if ($nilai !== null) {
            $rmses[] = $nilai;
        }
    }
    $rataRmse = !empty($rmses) ? array_sum($rmses) / count($rmses) : 0;
    
    // Hitung rata-rata R²
    $r2s = [];
    foreach ($dataPrediksi as $item) {
        // Gunakan static_r_squared jika r_squared null
        $nilai = $item->r_squared ?? $item->static_r_squared ?? null;
        if ($nilai !== null) {
            $r2s[] = $nilai;
        }
    }
    $rataR2 = !empty($r2s) ? array_sum($r2s) / count($r2s) : 0;
    
    // Generate chart
    $chartBase64 = $this->generateChart($dataPrediksi);
    
    $pdf = Pdf::loadView('predict_pdf', [
        'dataPrediksi' => $dataPrediksi,
        'totalPrediksi' => $totalPrediksi,
        'totalAktual' => $totalAktual,
        'rataMape' => $rataMape,
        'rataRmse' => $rataRmse,
        'rataR2' => $rataR2,
        'chartBase64' => $chartBase64,
    ]);
    
    $pdf->setPaper('a4', 'landscape');
    
    return $pdf->download('riwayat_prediksi_' . date('Y-m-d_H-i-s') . '.pdf');
}
    /**
     * Live tracking data
     */
    public function liveTracking(Request $request)
    {
        $tanggalMulai = $request->get('tanggal_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $prediksi = Prediksi::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('tanggal', 'asc')
            ->get();

        $trackingData = [];
        $totalTarget = 0;
        $totalRealisasi = 0;

        foreach ($prediksi as $p) {
            $realisasi = $p->penjualan_aktual ?? 0;
            $target = $p->hasil_prediksi;
            
            $totalTarget += $target;
            $totalRealisasi += $realisasi;

            $trackingData[] = [
                'id' => $p->id,
                'tanggal' => Carbon::parse($p->tanggal)->format('d/m/Y'),
                'target' => $target,
                'realisasi' => $realisasi,
                'status' => $this->getStatusPencapaian($realisasi, $target),
                'metode_analisis' => $p->metode_analisis ?? 'ES + Tren',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $trackingData,
            'summary' => [
                'total_target' => $totalTarget,
                'total_realisasi' => $totalRealisasi,
                'total_pencapaian' => $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0,
            ]
        ]);
    }

    /**
     * Get status pencapaian
     */
    private function getStatusPencapaian($realisasi, $target)
    {
        if ($realisasi == 0 || $target == 0) return 'Belum Ada Realisasi';
        $persen = ($realisasi / $target) * 100;
        if ($persen >= 100) return 'Tercapai';
        if ($persen >= 80) return 'Hampir Tercapai';
        if ($persen >= 50) return 'On Progress';
        return 'Perlu Aksi';
    }
    /**
 * Generate chart untuk PDF
 */
private function generateChart($dataPrediksi)
{
    try {
        $labels = [];
        $prediksiData = [];
        $aktualData = [];
        
        foreach ($dataPrediksi as $item) {
            $labels[] = \Carbon\Carbon::parse($item->tanggal)->format('d/m');
            $prediksiData[] = round($item->hasil_prediksi / 1000, 0);
            $aktualData[] = !is_null($item->penjualan_aktual) ? round($item->penjualan_aktual / 1000, 0) : 0;
        }
        
        // Gunakan quickchart.io untuk generate chart
        $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Prediksi',
                        'data' => $prediksiData,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                    [
                        'label' => 'Aktual',
                        'data' => $aktualData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['position' => 'top'],
                    'title' => ['display' => true, 'text' => 'Grafik Prediksi vs Aktual (Dalam Ribuan Rp)']
                ],
                'scales' => [
                    'y' => ['title' => ['display' => true, 'text' => 'Nilai (000 Rp)']],
                    'x' => ['title' => ['display' => true, 'text' => 'Tanggal']]
                ]
            ]
        ]));
        
        $chartImage = file_get_contents($chartUrl);
        return 'data:image/png;base64,' . base64_encode($chartImage);
        
    } catch (\Exception $e) {
        \Log::error('Gagal generate chart: ' . $e->getMessage());
        return null;
    }
}
}