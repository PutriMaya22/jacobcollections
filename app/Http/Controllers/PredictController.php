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

        $dataPrediksi         = Prediksi::orderBy('tanggal', 'desc')->get();
        $lastPrediction       = session('last_prediction', []);
        $dataBarang           = $this->getDataBarang();
        
        // Ambil dari API Flask
        $rekomendasiProduk    = $this->getRekomendasiRestockFromAPI(15);
        
        $produkPalingDiminati = $this->getPopularProductsFromDatabase(10);
        $produkTerlaris       = $this->getProdukTerlaris();

        \Log::info('rekomendasiProduk count: ' . $rekomendasiProduk->count());

        return view('predict', [
            'dataPrediksi'           => $dataPrediksi,
            'prediksi'               => $lastPrediction['prediksi'] ?? null,
            'tanggal_input'          => $lastPrediction['tanggal_input'] ?? null,
            'total_input'            => $lastPrediction['total_input'] ?? null,
            'rata_rata'              => $lastPrediction['rata_rata'] ?? null,
            'status'                 => $lastPrediction['status'] ?? null,
            'strategi_umum'          => $lastPrediction['strategi_umum'] ?? null,
            'rekomendasi_produk'     => $rekomendasiProduk,
            'produk_paling_diminati' => $produkPalingDiminati,
            'produk_terlaris'        => $produkTerlaris,
            'data_barang'            => $dataBarang,
        ]);
    }

    /**
     * Ambil rekomendasi restock dari Flask API
     * Endpoint: GET /restock
     */
    private function getRekomendasiRestockFromAPI($limit = 15)
    {
        try {
            // Panggil API Flask
            $response = Http::timeout(10)->get('http://127.0.0.1:5000/restock');
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success' && isset($data['rekomendasi_restock'])) {
                    $rekomendasi = collect($data['rekomendasi_restock'])->map(function ($item) {
                        // Parse jumlah_restock dari string "X pcs" ke integer
                        $jumlahRestock = (int) preg_replace('/[^0-9]/', '', $item['jumlah_restock'] ?? '0');
                        $totalTerjual = (int) preg_replace('/[^0-9]/', '', $item['total_terjual'] ?? '0');
                        $stok = (int) preg_replace('/[^0-9]/', '', $item['stok'] ?? '0');
                        
                        // Parse persen tren dari string tren
                        $trenText = $item['tren'] ?? '➡️ Stabil';
                        $trenArah = 'stabil';
                        $persenTren = 0;
                        
                        if (str_contains($trenText, '📈')) {
                            $trenArah = 'naik';
                            preg_match('/(\d+(?:\.\d+)?)%/', $trenText, $matches);
                            $persenTren = $matches[1] ?? 10;
                        } elseif (str_contains($trenText, '📉')) {
                            $trenArah = 'turun';
                            preg_match('/(\d+(?:\.\d+)?)%/', $trenText, $matches);
                            $persenTren = $matches[1] ?? -30;
                        }
                        
                        return (object) [  // Return sebagai object agar konsisten dengan view
                            'kode_produk' => $item['kode_produk'] ?? '-',
                            'nama_produk' => $item['nama_produk'] ?? '-',
                            'stok' => $stok,
                            'total_terjual' => $totalTerjual,
                            'jumlah_restock' => $jumlahRestock,
                            'rekomendasi_sistem' => $item['rekomendasi'] ?? '➡️ Stok Cukup',
                            'prioritas' => $item['prioritas'] ?? 4,
                            'tren_penjualan' => $trenArah,
                            'persen_tren' => abs($persenTren),
                            'hari_terakhir' => $item['hari_terakhir'] ?? 999,
                            'terakhir_jual' => $item['terakhir_jual'] ?? '-',
                        ];
                    });
                    
                    // Batasi jumlah
                    return $rekomendasi->take($limit);
                }
            }
            
            // Fallback ke database jika API tidak tersedia
            \Log::warning('Flask API tidak tersedia, fallback ke database');
            return $this->getRekomendasiRestockFromDatabase($limit);
            
        } catch (\Exception $e) {
            \Log::error('Error getRekomendasiRestockFromAPI: ' . $e->getMessage());
            // Fallback ke database
            return $this->getRekomendasiRestockFromDatabase($limit);
        }
    }

    /**
     * FALLBACK: Ambil rekomendasi restock dari database (jika API Flask mati)
     * Method ini menggantikan getRekomendasiProdukFromDataBarang yang hilang
     */
    private function getRekomendasiRestockFromDatabase($limit = 15)
    {
        try {
            $produk = DB::table('data_barang')
                ->select(
                    'kode_produk',
                    'nama',
                    'stok',
                    'total_pesanan as total_terjual',
                    'tanggal_penjualan as terakhir_jual'
                )
                ->whereNotNull('nama')
                ->where('total_pesanan', '>', 0)
                ->orderBy('total_pesanan', 'desc')
                ->limit($limit)
                ->get();
            
            if ($produk->isEmpty()) {
                return collect([(object) [
                    'kode_produk' => '-',
                    'nama_produk' => 'Belum ada data produk',
                    'stok' => 0,
                    'total_terjual' => 0,
                    'jumlah_restock' => 0,
                    'rekomendasi_sistem' => 'Belum ada data',
                    'prioritas' => 4,
                    'tren_penjualan' => 'stabil',
                    'persen_tren' => 0,
                    'hari_terakhir' => 999,
                    'terakhir_jual' => '-',
                ]]);
            }
            
            $today = Carbon::today();
            $hasil = [];
            
            foreach ($produk as $item) {
                $totalTerjual = (int) ($item->total_terjual ?? 0);
                $stok = (int) ($item->stok ?? 0);
                
                // Hitung hari terakhir terjual
                $hariTerakhir = 999;
                if ($item->terakhir_jual) {
                    $tglTerakhir = Carbon::parse($item->terakhir_jual);
                    $hariTerakhir = $today->diffInDays($tglTerakhir);
                }
                
                // Hitung restock (metode sederhana)
                $rataHarian = $totalTerjual / 90; // asumsi 90 hari
                $restockRecommended = max(0, (int) ($rataHarian * 14 - $stok));
                
                // Tentukan prioritas
                if ($stok <= 0 && $hariTerakhir <= 7) {
                    $prioritas = 1;
                    $rekomendasi = "🚨 RESTOCK DARURAT - Stok Habis, Masih Laku!";
                } elseif ($stok <= 0) {
                    $prioritas = 2;
                    $rekomendasi = "⚠️ Stok Habis - Perlu Restock";
                } elseif ($restockRecommended > 50) {
                    $prioritas = 2;
                    $rekomendasi = "✅ Restock Besar";
                } elseif ($restockRecommended > 0) {
                    $prioritas = 3;
                    $rekomendasi = "📦 Restock Normal";
                } elseif ($hariTerakhir > 30) {
                    $prioritas = 4;
                    $rekomendasi = "⚠️ Tidak Laku >30 hari - Evaluasi";
                } else {
                    $prioritas = 4;
                    $rekomendasi = "➡️ Stok Cukup";
                }
                
                // Tentukan tren
                if ($hariTerakhir <= 7) {
                    $trenArah = 'naik';
                    $persenTren = 10;
                } elseif ($hariTerakhir <= 30) {
                    $trenArah = 'stabil';
                    $persenTran = 0;
                } elseif ($hariTerakhir <= 90) {
                    $trenArah = 'turun';
                    $persenTren = 30;
                } else {
                    $trenArah = 'turun';
                    $persenTren = 100;
                }
                
                $hasil[] = (object) [
                    'kode_produk' => $item->kode_produk ?? '-',
                    'nama_produk' => $item->nama ?? '-',
                    'stok' => $stok,
                    'total_terjual' => $totalTerjual,
                    'jumlah_restock' => $restockRecommended,
                    'rekomendasi_sistem' => $rekomendasi,
                    'prioritas' => $prioritas,
                    'tren_penjualan' => $trenArah,
                    'persen_tren' => $persenTren,
                    'hari_terakhir' => $hariTerakhir,
                    'terakhir_jual' => $item->terakhir_jual ?? '-',
                ];
            }
            
            // Urutkan berdasarkan prioritas
            usort($hasil, function($a, $b) {
                if ($a->prioritas != $b->prioritas) {
                    return $a->prioritas <=> $b->prioritas;
                }
                return $a->stok <=> $b->stok;
            });
            
            return collect($hasil)->take($limit);
            
        } catch (\Exception $e) {
            \Log::error('getRekomendasiRestockFromDatabase ERROR: ' . $e->getMessage());
            return collect([(object) [
                'kode_produk' => '-',
                'nama_produk' => 'Error loading data',
                'stok' => 0,
                'total_terjual' => 0,
                'jumlah_restock' => 0,
                'rekomendasi_sistem' => 'Database error',
                'prioritas' => 4,
                'tren_penjualan' => 'stabil',
                'persen_tren' => 0,
                'hari_terakhir' => 999,
                'terakhir_jual' => '-',
            ]]);
        }
    }

    /**
     * Get produk paling diminati dari database
     */
    private function getPopularProductsFromDatabase($limit = 10)
    {
        try {
            $produk = DB::table('data_barang')
                ->select(
                    'id', 'kode_produk', 'nama', 'kategori', 'stok',
                    'total_penjualan', 'total_dilihat', 'total_klik',
                    'total_pesanan', 'persentase_klik', 'tingkat_konversi'
                )
                ->whereNotNull('nama')
                ->where('total_penjualan', '>', 0)
                ->orderBy('total_penjualan', 'desc')
                ->limit($limit)
                ->get();

            if ($produk->isEmpty()) {
                return $this->getFallbackProduk();
            }

            $hasil         = [];
            $max_penjualan = $produk->max('total_penjualan') ?: 1;

            foreach ($produk as $item) {
                $ctr = (!empty($item->total_dilihat) && !empty($item->total_klik))
                    ? round(($item->total_klik / $item->total_dilihat) * 100, 2)
                    : (float) ($item->persentase_klik ?? 0);

                $cr = (!empty($item->total_klik) && !empty($item->total_pesanan))
                    ? round(($item->total_pesanan / $item->total_klik) * 100, 2)
                    : (float) ($item->tingkat_konversi ?? 0);

                $popularity_score = round(
                    ($item->total_penjualan / $max_penjualan) * 40
                    + ($ctr / 100) * 30
                    + ($cr / 100) * 30,
                    2
                );

                $hasil[] = [
                    'nama'             => $item->nama,
                    'kode_produk'      => $item->kode_produk,
                    'kategori'         => $item->kategori,
                    'stok'             => (int) $item->stok,
                    'total_penjualan'  => (float) $item->total_penjualan,
                    'total_pesanan'    => (int) $item->total_pesanan,
                    'ctr'              => $ctr,
                    'cr'               => $cr,
                    'popularity_score' => $popularity_score,
                    'rekomendasi'      => $this->getRekomendasiText($item->stok, $cr, $ctr, $item->total_pesanan),
                    'persentase_klik'  => $ctr,
                    'tingkat_konversi' => $cr,
                ];
            }

            usort($hasil, fn($a, $b) => $b['popularity_score'] <=> $a['popularity_score']);

            return $hasil;

        } catch (\Exception $e) {
            \Log::error('getPopularProductsFromDatabase ERROR: ' . $e->getMessage());
            return $this->getFallbackProduk();
        }
    }

    private function getRekomendasiText($stok, $cr, $ctr, $total_pesanan)
    {
        if ($stok <= 0 && $cr > 10) return "PRIORITAS RESTOCK - Produk sangat diminati!";
        if ($stok <= 0 && $cr > 5)  return "Stok Habis - Produk cukup diminati";
        if ($total_pesanan > 100)   return "Best Seller - Pertahankan stok!";
        if ($ctr > 10 && $cr > 5)   return "Potensi Besar - Tingkatkan stok";
        if ($stok < 10 && $cr > 3)  return "Stok Menipis (Produk Laris)";
        if ($stok < 10)             return "Stok Menipis";
        if ($ctr > 0 && $ctr < 5)   return "Kurang diminati - Perlu promosi";
        return "Normal - Monitor berkala";
    }

    private function getFallbackProduk()
    {
        return [[
            'nama'             => 'Belum ada data',
            'kode_produk'      => '-',
            'kategori'         => '-',
            'stok'             => 0,
            'total_penjualan'  => 0,
            'total_pesanan'    => 0,
            'ctr'              => 0,
            'cr'               => 0,
            'popularity_score' => 0,
            'rekomendasi'      => 'Belum ada data produk',
            'persentase_klik'  => 0,
            'tingkat_konversi' => 0,
        ]];
    }

    /**
     * Get produk terlaris
     */
    private function getProdukTerlaris()
    {
        try {
            $produkTerlaris = DB::table('data_barang')
                ->select(
                    'nama',
                    DB::raw('SUM(COALESCE(total_pesanan, 0)) as total_pesanan'),
                    DB::raw('MAX(stok) as stok_terakhir')
                )
                ->where('total_pesanan', '>', 0)
                ->whereNotNull('nama')
                ->groupBy('nama')
                ->orderByDesc('total_pesanan')
                ->limit(5)
                ->get();

            if ($produkTerlaris->isNotEmpty()) {
                return $produkTerlaris->map(fn($item) => (object) [
                    'nama'          => $item->nama,
                    'total_pesanan' => (int) ($item->total_pesanan ?? 0),
                    'stok'          => (int) ($item->stok_terakhir ?? 0),
                ]);
            }

            return collect([]);
        } catch (\Exception $e) {
            \Log::error('getProdukTerlaris ERROR: ' . $e->getMessage());
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
                ->map(fn($item) => [
                    'id'              => $item->id,
                    'nama'            => $item->nama,
                    'stok'            => (int) ($item->stok ?? 0),
                    'total_penjualan' => (float) ($item->total_penjualan ?? 0),
                    'total_pesanan'   => (int) ($item->total_pesanan ?? 0),
                ])
                ->sortByDesc('total_penjualan')
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('getDataBarang ERROR: ' . $e->getMessage());
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
            'tanggal'       => 'required|date',
            'alpha'         => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $alpha = $request->get('alpha', 0.3);

            $response = Http::timeout(30)->post('http://127.0.0.1:5000/predict', [
                'total_pesanan' => (int) $request->total_pesanan,
                'tanggal'       => $request->tanggal,
                'alpha'         => (float) $alpha,
            ]);

            if (!$response->successful()) {
                throw new \Exception("Response error: " . $response->body());
            }

            $hasil         = $response->json();
            $prediksiNilai = $hasil['prediksi_total_penjualan'] ?? 0;
            $evaluasi      = $hasil['evaluasi'] ?? null;
            
            $rekomendasiProdukFromApi = $hasil['rekomendasi_produk'] ?? [];

            session([
                'last_prediction' => [
                    'prediksi'      => $prediksiNilai,
                    'tanggal_input' => $request->tanggal,
                    'total_input'   => $request->total_pesanan,
                    'rata_rata'     => $hasil['rata_rata_historis'] ?? null,
                    'status'        => $hasil['status'] ?? null,
                    'strategi_umum' => $hasil['strategi_umum'] ?? null,
                ],
                'rekomendasi_produk' => $rekomendasiProdukFromApi
            ]);

            $this->simpanPrediksiStatis(
                $request->tanggal,
                $prediksiNilai,
                (int) $request->total_pesanan,
                $evaluasi
            );

            $this->sinkronisasiAktual();

            return redirect()->route('prediksi')->with('success', 'Prediksi berhasil dilakukan!');

        } catch (\Exception $e) {
            \Log::error('store prediksi ERROR: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses prediksi: ' . $e->getMessage());
        }
    }

    /**
     * Simpan prediksi statis ke database
     */
    private function simpanPrediksiStatis($tanggal, $hasilPrediksi, $totalPesanan, $evaluasi = null)
    {
        Prediksi::create([
            'tanggal'              => $tanggal,
            'hasil_prediksi'       => $hasilPrediksi,
            'total_pesanan'        => $totalPesanan,
            'static_mape'          => $evaluasi['MAPE'] ?? null,
            'static_rmse'          => $evaluasi['RMSE'] ?? null,
            'static_r_squared'     => $evaluasi['R2'] ?? null,
            'evaluasi_captured_at' => now(),
            'metode_analisis'      => 'Exponential Smoothing + Tren',
            'created_at'           => now(),
        ]);
    }

    /**
     * Sinkronisasi data aktual
     */
    private function sinkronisasiAktual()
    {
        try {
            $prediksiList = Prediksi::all();
            foreach ($prediksiList as $item) {
                $penjualan = Penjualan::whereDate('tanggal', $item->tanggal)->first();
                $aktual    = $penjualan ? $penjualan->total_penjualan : null;

                if ($aktual !== null && $item->penjualan_aktual != $aktual) {
                    $item->penjualan_aktual = $aktual;
                    $item->error            = $aktual - $item->hasil_prediksi;
                    if ($item->static_error === null) {
                        $item->static_error = $item->error;
                    }
                    $item->save();
                }
            }
        } catch (\Exception $e) {
            \Log::error('sinkronisasiAktual ERROR: ' . $e->getMessage());
        }
    }

    /**
     * Update realisasi prediksi
     */
    public function updateRealisasiDashboard(Request $request, $id)
    {
        $request->validate([
            'realisasi' => 'required|numeric|min:0',
            'catatan'   => 'nullable|string|max:500',
        ]);

        $prediksi = Prediksi::findOrFail($id);

        DataPenjualan::updateOrCreate(
            ['tanggal' => $prediksi->tanggal],
            ['total_penjualan' => $request->realisasi, 'updated_by' => auth()->id()]
        );

        $prediksi->penjualan_aktual   = $request->realisasi;
        $prediksi->error              = $request->realisasi - $prediksi->hasil_prediksi;
        $prediksi->catatan            = $request->catatan;
        $prediksi->pesanan_updated_at = now();

        if ($prediksi->static_error === null) {
            $prediksi->static_error = $prediksi->error;
        }

        $prediksi->save();

        return response()->json(['success' => true]);
    }

    /**
     * Halaman grafik
     */
    public function grafik()
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403);
        }
        $data = Prediksi::orderBy('tanggal', 'asc')->get();
        return view('admin.grafik', compact('data'));
    }

    /**
     * Export PDF
     */
    public function exportPDF()
    {
        if (auth()->user()->role !== 'owner') {
            abort(403);
        }

        $dataPrediksi = DB::table('prediksis')
            ->select(
                'id', 'tanggal', 'hasil_prediksi', 'total_pesanan',
                'penjualan_aktual', 'error', 'static_error',
                'mape', 'static_mape', 'rmse', 'static_rmse',
                'r_squared', 'static_r_squared', 'created_at'
            )
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalPrediksi = $dataPrediksi->sum('hasil_prediksi');
        $totalAktual   = $dataPrediksi->sum('penjualan_aktual');

        $mapes = $dataPrediksi->map(fn($i) => $i->mape ?? $i->static_mape ?? null)->filter()->values();
        $rmses = $dataPrediksi->map(fn($i) => $i->rmse ?? $i->static_rmse ?? null)->filter()->values();
        $r2s   = $dataPrediksi->map(fn($i) => $i->r_squared ?? $i->static_r_squared ?? null)->filter()->values();

        $rataMape = $mapes->count() ? $mapes->avg() : 0;
        $rataRmse = $rmses->count() ? $rmses->avg() : 0;
        $rataR2   = $r2s->count()   ? $r2s->avg()   : 0;

        $chartBase64 = $this->generateChart($dataPrediksi);

        $pdf = Pdf::loadView('predict_pdf', [
            'dataPrediksi'  => $dataPrediksi,
            'totalPrediksi' => $totalPrediksi,
            'totalAktual'   => $totalAktual,
            'rataMape'      => $rataMape,
            'rataRmse'      => $rataRmse,
            'rataR2'        => $rataR2,
            'chartBase64'   => $chartBase64,
        ]);

        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('riwayat_prediksi_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Live tracking data
     */
    public function liveTracking(Request $request)
    {
        $tanggalMulai   = $request->get('tanggal_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->get('tanggal_selesai', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $prediksi = Prediksi::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('tanggal', 'asc')
            ->get();

        $trackingData   = [];
        $totalTarget    = 0;
        $totalRealisasi = 0;

        foreach ($prediksi as $p) {
            $realisasi       = $p->penjualan_aktual ?? 0;
            $target          = $p->hasil_prediksi;
            $totalTarget    += $target;
            $totalRealisasi += $realisasi;

            $trackingData[] = [
                'id'              => $p->id,
                'tanggal'         => Carbon::parse($p->tanggal)->format('d/m/Y'),
                'target'          => $target,
                'realisasi'       => $realisasi,
                'status'          => $this->getStatusPencapaian($realisasi, $target),
                'metode_analisis' => $p->metode_analisis ?? 'ES + Tren',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $trackingData,
            'summary' => [
                'total_target'     => $totalTarget,
                'total_realisasi'  => $totalRealisasi,
                'total_pencapaian' => $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0,
            ],
        ]);
    }

    private function getStatusPencapaian($realisasi, $target)
    {
        if ($realisasi == 0 || $target == 0) return 'Belum Ada Realisasi';
        $persen = ($realisasi / $target) * 100;
        if ($persen >= 100) return 'Tercapai';
        if ($persen >= 80)  return 'Hampir Tercapai';
        if ($persen >= 50)  return 'On Progress';
        return 'Perlu Aksi';
    }

    /**
     * Generate chart untuk PDF
     */
    private function generateChart($dataPrediksi)
    {
        try {
            $labels = $prediksiData = $aktualData = [];

            foreach ($dataPrediksi as $item) {
                $labels[]       = Carbon::parse($item->tanggal)->format('d/m');
                $prediksiData[] = round($item->hasil_prediksi / 1000, 0);
                $aktualData[]   = !is_null($item->penjualan_aktual)
                    ? round($item->penjualan_aktual / 1000, 0)
                    : 0;
            }

            $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode([
                'type' => 'line',
                'data' => [
                    'labels'   => $labels,
                    'datasets' => [
                        ['label' => 'Prediksi', 'data' => $prediksiData, 'borderColor' => '#3b82f6', 'backgroundColor' => 'rgba(59,130,246,0.1)', 'fill' => true, 'tension' => 0.3],
                        ['label' => 'Aktual',   'data' => $aktualData,   'borderColor' => '#10b981', 'backgroundColor' => 'rgba(16,185,129,0.1)', 'fill' => true, 'tension' => 0.3],
                    ],
                ],
                'options' => [
                    'plugins' => ['title' => ['display' => true, 'text' => 'Grafik Prediksi vs Aktual (Ribuan Rp)']],
                ],
            ]));

            $chartImage = @file_get_contents($chartUrl);
            return $chartImage ? 'data:image/png;base64,' . base64_encode($chartImage) : null;

        } catch (\Exception $e) {
            \Log::error('generateChart ERROR: ' . $e->getMessage());
            return null;
        }
    }
}