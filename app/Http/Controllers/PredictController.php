<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PredictController extends Controller
{
    /**
     * Tampilkan Halaman Form
     * Route: GET /prediksi (prediksi.index)
     */
    public function index()
    {
        return view('prediksi.index');
    }

    /**
     * Proses Prediksi dengan Python
     * Route: POST /prediksi (prediksi.proses)
     */
    public function proses(Request $request)
    {
        // Validasi input
        $request->validate([
            'target_date' => 'required|date'
        ]);

        $tanggal = $request->input('target_date');

        // Path ke Python script
        $pythonScript = base_path('python/predict.py');

        if (!file_exists($pythonScript)) {
            return back()->withErrors("File predict.py tidak ditemukan di folder python/");
        }
        $pythonScript = base_path('python/predict.py');
        $command = escapeshellcmd("python \"$pythonScript\" $tanggal");
    $output = shell_exec($command . ' 2>&1');  // tangkap error
dd($output); // sementara untuk debug


        // Jalankan Python script
        $command = escapeshellcmd("python \"$pythonScript\" $tanggal");

        // Tangkap output dan error
        $output = shell_exec($command . ' 2>&1');

        // DEBUG sementara, bisa dihapus nanti
        // dd($output);

        // Decode JSON dari Python
        $result = json_decode($output, true);

        if (!$result) {
            return back()->withErrors("Gagal decode JSON dari Python. Output mentah: " . $output);
        }

        return view('prediksi.index', [
            'result' => $result,
            'tanggal' => $tanggal
        ]);
    }
}
