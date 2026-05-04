<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Prediksi;
use App\Models\Penjualan;
use App\Models\DataPenjualan;
use App\Models\Barang;
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
        $namaBarangValid = collect($dataBarang)->pluck('nama')->toArray();
        
        $rekomendasiProduk = collect(session('rekomendasi_produk', []))
            ->filter(fn($item) => in_array($item['nama'] ?? $item['nama_produk'] ?? '', $namaBarangValid))
            ->values()
            ->toArray();
        
        $produkPalingDiminati = collect(session('produk_paling_diminati', []))
            ->filter(fn($item) => in_array($item['nama'] ?? '', $namaBarangValid))
            ->values()
            ->toArray();
        
        $ringkasanRestock = session('ringkasan_restock', []);
        if (isset($ringkasanRestock['prioritas_restock'])) {
            $ringkasanRestock['prioritas_restock'] = collect($ringkasanRestock['prioritas_restock'])
                ->filter(fn($item) => in_array($item['nama'] ?? '', $namaBarangValid))
                ->values()
                ->toArray();
        }
        
        $produkTerlaris = $this->getProdukTerlaris();
        
        if (empty($produkPalingDiminati)) {
            $produkPalingDiminati = $this->generateFallbackProdukPalingDiminati($dataBarang);
        }

        $ringkasanRestock = $this->normalizePrioritasRestock(
            $ringkasanRestock,
            $produkPalingDiminati,
            $dataBarang
        );
        
        $metodeAnalisis = session('metode_analisis', 'Exponential Smoothing + Tren Produk');

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
            'ringkasan_restock' => $ringkasanRestock,
            'data_barang' => $dataBarang,
            'produk_terjual_data' => $this->getProdukTerjualData(),
            'metode_analisis' => $metodeAnalisis,
        ]);
    }

    private function getProdukTerlaris()
    {
        try {
            $schema = DB::getSchemaBuilder();
            $kolomUtama = $schema->hasColumn('data_barang', 'total_pesanan')
                ? 'total_pesanan'
                : 'total_penjualan';

            $produkTerlaris = DB::table('data_barang')
                ->select(
                    'nama',
                    DB::raw("SUM(COALESCE($kolomUtama, 0)) as total_pesanan"),
                    DB::raw('MAX(stok) as stok_terakhir')
                )
                ->whereRaw("COALESCE($kolomUtama, 0) > 0")
                ->whereNotNull('nama')
                ->where('nama', '!=', '')
                ->groupBy('nama')
                ->orderByDesc('total_pesanan')
                ->get();

            if ($produkTerlaris->isNotEmpty()) {
                return $produkTerlaris->map(function ($item) {
                    return (object) [
                        'nama' => $item->nama,
                        'total_pesanan' => (int) ($item->total_pesanan ?? 0),
                        'total_penjualan' => (int) ($item->total_pesanan ?? 0),
                        'stok' => (int) ($item->stok_terakhir ?? 0),
                    ];
                });
            }
            return collect([]);
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    private function getDataBarang()
    {
        try {
            $barang = DB::table('data_barang')
                ->select('id', 'nama', 'stok', 'total_penjualan', 'total_pesanan')
                ->get()
                ->map(function ($item) {
                    $totalPesanan = (int) ($item->total_pesanan ?? 0);
                    $stok = (int) ($item->stok ?? 0);
                    $cr = $totalPesanan > 0 ? round(min(100, $totalPesanan), 1) : 0;
                    
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'stok' => $stok,
                        'total_penjualan' => (float) ($item->total_penjualan ?? 0),
                        'total_pesanan' => $totalPesanan,
                        'cr' => $cr,
                    ];
                })
                ->sortByDesc('total_penjualan')
                ->values()
                ->toArray();

            return empty($barang) ? $this->getDummyDataBarang() : $barang;
        } catch (\Exception $e) {
            return $this->getDummyDataBarang();
        }
    }

    private function getProdukTerjualData()
    {
        try {
            $barang = DB::table('data_barang')
                ->select('id', 'nama', 'total_penjualan', 'stok')
                ->get();
            
            $data = [];
            foreach ($barang as $item) {
                $data[$item->nama] = [
                    'terjual' => (float) ($item->total_penjualan ?? 0),
                    'stok' => (int) ($item->stok ?? 0),
                ];
            }
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getDummyDataBarang()
    {
        $barangFromDb = DB::table('data_barang')
            ->select('id', 'nama', 'stok', 'total_penjualan')
            ->get();
        
        if ($barangFromDb->isNotEmpty()) {
            return $barangFromDb->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'stok' => (int) ($item->stok ?? 0),
                    'total_penjualan' => (float) ($item->total_penjualan ?? 0),
                    'cr' => 0,
                ];
            })->toArray();
        }
        return [];
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
            $produkPalingDiminati = $hasil['produk_paling_diminati'] ?? [];
            $ringkasanRestock = $hasil['ringkasan_restock'] ?? [];
            $metodeAnalisis = $hasil['metode_analisis_produk'] ?? 'Exponential Smoothing + Tren Produk';
            $ringkasanTren = $hasil['ringkasan_tren'] ?? [];
            
            $dataBarang = $this->getDataBarang();
            $namaBarangValid = collect($dataBarang)->pluck('nama')->toArray();
            
            $rekomendasiProduk = collect($rekomendasiProduk)
                ->filter(fn($item) => in_array($item['nama'] ?? $item['nama_produk'] ?? '', $namaBarangValid))
                ->values()
                ->toArray();
            
            $produkPalingDiminati = collect($produkPalingDiminati)
                ->filter(fn($item) => in_array($item['nama'] ?? '', $namaBarangValid))
                ->values()
                ->toArray();
            
            if (isset($ringkasanRestock['prioritas_restock'])) {
                $ringkasanRestock['prioritas_restock'] = collect($ringkasanRestock['prioritas_restock'])
                    ->filter(fn($item) => in_array($item['nama'] ?? '', $namaBarangValid))
                    ->values()
                    ->toArray();
            }
            
            $produkTerlaris = $this->getProdukTerlaris();

            if (empty($produkPalingDiminati)) {
                $produkPalingDiminati = $this->generateFallbackProdukPalingDiminati($dataBarang);
            }

            $ringkasanRestock = $this->normalizePrioritasRestock(
                $ringkasanRestock,
                $produkPalingDiminati,
                $dataBarang
            );

            session([
                'rekomendasi_produk' => $rekomendasiProduk,
                'produk_paling_diminati' => $produkPalingDiminati,
                'ringkasan_restock' => $ringkasanRestock,
                'metode_analisis' => $metodeAnalisis,
                'alpha_used' => $alpha,
                'ringkasan_tren' => $ringkasanTren,
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
                $evaluasi,
                $metodeAnalisis
            );

            $this->sinkronisasiAktual();
            $dataPrediksi = Prediksi::orderBy('tanggal', 'desc')->get();

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
                'ringkasan_restock' => $ringkasanRestock,
                'metode_analisis' => $metodeAnalisis,
                'dataPrediksi' => $dataPrediksi,
                'data_barang' => $dataBarang,
                'produk_terjual_data' => $this->getProdukTerjualData(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses prediksi: ' . $e->getMessage());
        }
    }

    private function simpanPrediksiStatis($tanggal, $hasilPrediksi, $totalPesanan, $evaluasi = null, $metodeAnalisis = null)
    {
        $existing = Prediksi::where('tanggal', $tanggal)->first();

        $data = [
            'tanggal' => $tanggal,
            'hasil_prediksi' => $hasilPrediksi,
            'total_pesanan' => $totalPesanan,
            'static_mape' => $evaluasi['MAPE'] ?? null,
            'static_rmse' => $evaluasi['RMSE'] ?? null,
            'static_r_squared' => $evaluasi['R2'] ?? null,
            'evaluasi_captured_at' => now(),
            'metode_analisis' => $metodeAnalisis,
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            Prediksi::create($data);
        }
    }

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
        $dataPrediksi = Prediksi::orderBy('tanggal', 'asc')->get();
        $pdf = Pdf::loadView('predict_pdf', compact('dataPrediksi'));
        return $pdf->download('riwayat_prediksi_' . date('Y-m-d') . '.pdf');
    }

    public function exportRekomendasiPDF()
    {
        if (auth()->user()->role !== 'owner') {
            abort(403);
        }
        // similar to before but with ES data
        $pdf = Pdf::loadView('predict_rekomendasi_pdf', $this->getRekomendasiData());
        return $pdf->download('rekomendasi_restock_' . date('Y-m-d') . '.pdf');
    }

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

    private function getStatusPencapaian($realisasi, $target)
    {
        if ($realisasi == 0 || $target == 0) return 'Belum Ada Realisasi';
        $persen = ($realisasi / $target) * 100;
        if ($persen >= 100) return 'Tercapai';
        if ($persen >= 80) return 'Hampir Tercapai';
        if ($persen >= 50) return 'On Progress';
        return 'Perlu Aksi';
    }

    private function generateFallbackProdukPalingDiminati(array $dataBarang = [])
    {
        if (empty($dataBarang)) return [];
        
        return collect($dataBarang)
            ->sortByDesc('total_penjualan')
            ->take(10)
            ->map(function ($item) {
                return [
                    'nama' => $item['nama'],
                    'popularity_score' => 50,
                    'rekomendasi' => 'Produk dengan performa baik',
                    'cr' => $item['cr'] ?? 0,
                    'stok' => $item['stok'] ?? 0,
                ];
            })
            ->values()
            ->toArray();
    }

    private function normalizePrioritasRestock(array $ringkasanRestock = [], array $produkPalingDiminati = [], array $dataBarang = [])
    {
        $existing = collect($ringkasanRestock['prioritas_restock'] ?? []);
        $fromDiminati = collect($produkPalingDiminati)->map(fn($item) => $this->buildRestockCandidate($item));
        $fromBarang = collect($dataBarang)->map(fn($item) => $this->buildRestockCandidate($item));

        $final = $existing->merge($fromDiminati)->merge($fromBarang)
            ->unique('nama')
            ->sortBy(fn($item) => $item['urgensi'] === 'Sangat Tinggi' ? 1 : ($item['urgensi'] === 'Tinggi' ? 2 : 3))
            ->values()
            ->toArray();

        $ringkasanRestock['prioritas_restock'] = $final;
        return $ringkasanRestock;
    }

    // GANTI DENGAN INI:
private function buildRestockCandidate(array $item)
{
    $nama = $item['nama'] ?? $item['nama_barang'] ?? '-';
    $stok = (int) ($item['stok'] ?? 0);
    $cr_raw = (float) ($item['cr'] ?? 0);
    
    // BATASI CR MAKSIMAL 100%
    $cr = min(100, $cr_raw);
    
    $terjual = (float) ($item['terjual'] ?? $item['total_penjualan'] ?? 0);
    $estimasi_laku = (int) ($item['estimasi_laku'] ?? max(ceil($terjual / 150000), $stok <= 0 ? 20 : 10));

    // HITUNG REKOMENDASI RESTOCK
    if ($stok <= 0) {
        // Stok habis, minimal restock 25 pcs
        $rekomendasiRestock = max(25, $estimasi_laku);
    } else {
        $rekomendasiRestock = max(0, $estimasi_laku - $stok);
    }
    
    // TAMBAHKAN BUFFER BERDASARKAN CR
    if ($cr > 20) {
        $rekomendasiRestock = $rekomendasiRestock + 100;
        $bufferReason = 'CR > 20% → buffer 100 pcs';
    } elseif ($cr > 10) {
        $rekomendasiRestock = $rekomendasiRestock + 50;
        $bufferReason = 'CR > 10% → buffer 50 pcs';
    } elseif ($cr > 5) {
        $rekomendasiRestock = $rekomendasiRestock + 30;
        $bufferReason = 'CR > 5% → buffer 30 pcs';
    } else {
        $bufferReason = 'CR normal → buffer 15 pcs';
    }
    
    // DETERMINE URGENCY
    if ($stok <= 0) {
        $urgensi = 'Sangat Tinggi';
    } elseif ($stok <= 10 || $cr >= 15) {
        $urgensi = 'Tinggi';
    } elseif ($stok <= 25 || $cr >= 5) {
        $urgensi = 'Sedang';
    } else {
        $urgensi = 'Normal';
    }

    return [
        'nama' => $nama,
        'stok' => $stok,
        'cr' => round($cr, 1),
        'estimasi_laku' => $estimasi_laku,
        'rekomendasi_restock' => $rekomendasiRestock,
        'urgensi' => $urgensi,
        'terjual' => $terjual,
        'buffer_reason' => $bufferReason,
    ];
}
    private function getRekomendasiData()
    {
        return session()->all();
    }
    private function calculateRestockRecommendation($stok, $cr, $terjual)
{
    // BATASI CR MAKSIMAL 100%
    $cr = min(100, $cr);
    
    // Hitung buffer berdasarkan CR
    if ($cr > 20) {
        $buffer = 100;
    } elseif ($cr > 10) {
        $buffer = 50;
    } elseif ($cr > 5) {
        $buffer = 30;
    } else {
        $buffer = 15;
    }

    // Estimasi unit (jika terjual dalam Rupiah)
    $estimasiUnit = $terjual > 0 ? max(10, ceil($terjual / 150000)) : ($stok <= 0 ? 20 : 10);
    
    // Rekomendasi restock
    if ($stok <= 0) {
        return max(25, $estimasiUnit + $buffer);
    }
    
    return max(0, ($estimasiUnit + $buffer) - $stok);
}
}