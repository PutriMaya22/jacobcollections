<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Prediksi;
use App\Models\Penjualan;
use PDF;

class PredictController extends Controller
{
    // =========================
    // HALAMAN PREDIKSI
    // =========================
    public function index()
    {
        $this->sinkronisasiAktual();

        $dataPrediksi = Prediksi::orderBy('tanggal', 'desc')->get();

        return view('predict', compact('dataPrediksi'));
    }

    // =========================
    // SIMPAN PREDIKSI (OWNER ONLY)
    // =========================
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Hanya owner yang bisa input prediksi.');
        }

        $request->validate([
            'total_pesanan' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        try {

            // Kirim ke Python
            $response = Http::post('http://127.0.0.1:5000/predict', [
                'total_pesanan' => (int) $request->total_pesanan,
                'tanggal' => $request->tanggal,
            ]);

            if (!$response->successful()) {
                throw new \Exception("Response error from Python API");
            }

            $hasil = $response->json();
            

            $prediksi = $hasil['prediksi_total_penjualan'] ?? 0;
            $evaluasi = $hasil['evaluasi_testing'] ?? null;

            // Simpan ke database (dengan evaluasi dari Flask)
            $this->simpanPrediksi(
                $request->tanggal,
                $prediksi,
                null,
                $evaluasi
            );

            // Update penjualan aktual & error saja
            $this->sinkronisasiAktual();

            $dataPrediksi = Prediksi::orderBy('tanggal', 'desc')->get();

            return view('predict', [
                'prediksi' => $prediksi,
                'rata_rata' => $hasil['rata_rata_historis'] ?? null,
                'status' => $hasil['status'] ?? null,
                'rekomendasi' => $hasil['rekomendasi'] ?? null,
                'rekomendasi_kategori_stok' => $hasil['rekomendasi_kategori_stok'] ?? null,
                'tanggal_input' => $request->tanggal,
                'total_input' => $request->total_pesanan,
                'dataPrediksi' => $dataPrediksi
                
            ]);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal terhubung ke server Python atau terjadi kesalahan.');
        }
    }

    // =========================
    // SIMPAN PREDIKSI KE DB
    // =========================
    private function simpanPrediksi($tanggal, $hasil, $aktual = null, $evaluasi = null)
    {
        $error = null;

        if ($aktual !== null) {
            $error = abs($aktual - $hasil);
        }

        Prediksi::create([
            'tanggal' => $tanggal,
            'hasil_prediksi' => $hasil,
            'penjualan_aktual' => $aktual,
            'error' => $error,
            'rmse' => $evaluasi['RMSE'] ?? null,
            'mape' => $evaluasi['MAPE'] ?? null,
            'r_squared' => $evaluasi['R2'] ?? null,
        ]);
    }

    // =========================
    // UPDATE PENJUALAN AKTUAL SAJA
    // =========================
    private function sinkronisasiAktual()
    {
        $prediksiList = Prediksi::all();

        foreach ($prediksiList as $item) {

            $penjualan = Penjualan::whereDate('tanggal', $item->tanggal)->first();
            $aktual = $penjualan ? $penjualan->total_penjualan : null;

            if ($aktual !== null) {
                $item->penjualan_aktual = $aktual;
                $item->error = abs($aktual - $item->hasil_prediksi);
                $item->save();
            }
        }
    }

    // =========================
    // HALAMAN GRAFIK (OWNER + ADMIN)
    // =========================
    public function grafik()
    {
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403);
        }

        $data = Prediksi::orderBy('tanggal', 'asc')->get();
        return view('admin.grafik', compact('data'));
    }

    // =========================
    // EXPORT PDF (OWNER ONLY)
    // =========================
    public function exportPDF()
{
    if (auth()->user()->role !== 'owner') {
        abort(403, 'Hanya owner yang bisa export PDF.');
    }

    $dataPrediksi = Prediksi::orderBy('tanggal', 'asc')->get();

    // Siapkan data untuk grafik
    $labels = $dataPrediksi->pluck('tanggal');
    $prediksi = $dataPrediksi->pluck('hasil_prediksi');
    $aktual = $dataPrediksi->pluck('penjualan_aktual');

    // Format ke JSON untuk chart
    $chartConfig = [
        "type" => "line",
        "data" => [
            "labels" => $labels,
            "datasets" => [
                [
                    "label" => "Prediksi",
                    "data" => $prediksi,
                    "borderColor" => "blue",
                    "fill" => false
                ],
                [
                    "label" => "Aktual",
                    "data" => $aktual,
                    "borderColor" => "red",
                    "fill" => false
                ]
            ]
        ]
    ];

    $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));

    $pdf = PDF::loadView('predict_pdf', compact('dataPrediksi', 'chartUrl'))
          ->setOptions([
              'isRemoteEnabled' => true
          ]);


return $pdf->download('riwayat_prediksi.pdf');
}
}