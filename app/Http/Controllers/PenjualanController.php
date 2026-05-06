<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Imports\PenjualanImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    /**
     * Helper function to parse Indonesian date format
     * TAMBAHAN BARU: untuk parsing tanggal seperti "22 Maret 2023"
     */
    private function parseIndonesianDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        
        // If already in YYYY-MM-DD format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // Indonesian month mapping
        $months = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12'
        ];
        
        $lowerInput = strtolower(trim($dateString));
        
        // Try to match patterns like "22 Maret 2023" or "22-Maret-2023"
        foreach ($months as $monthName => $monthNum) {
            if (str_contains($lowerInput, $monthName)) {
                // Extract day and year
                $pattern = '/(\d{1,2})[\s-]+' . $monthName . '[\s-]+(\d{4})/i';
                if (preg_match($pattern, $dateString, $matches)) {
                    $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $year = $matches[2];
                    return "{$year}-{$monthNum}-{$day}";
                }
            }
        }
        
        // Try standard date parsing as fallback
        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function index(Request $request)
    {
        $query = Penjualan::query();

        // 🔥 DIPERBAIKI: Search dengan dukungan format tanggal Indonesia
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            
            // Coba parsing sebagai tanggal Indonesia
            $parsedDate = $this->parseIndonesianDate($searchTerm);
            
            if ($parsedDate) {
                // Jika berhasil diparse sebagai tanggal, cari berdasarkan tanggal
                $query->whereDate('tanggal', $parsedDate);
            } else {
                // Hapus karakter non-numeric untuk pencarian nominal
                $numericSearch = preg_replace('/[^0-9]/', '', $searchTerm);
                
                if (is_numeric($numericSearch) && $numericSearch > 0) {
                    // Cari berdasarkan nominal
                    $query->where(function($q) use ($searchTerm, $numericSearch) {
                        $q->where('tanggal', 'like', '%' . $searchTerm . '%')
                          ->orWhere('total_penjualan', 'like', '%' . $numericSearch . '%');
                    });
                } else {
                    // Fallback: cari berdasarkan teks biasa
                    $query->where(function($q) use ($request) {
                        $q->where('tanggal', 'like', '%' . $request->search . '%')
                          ->orWhere('total_penjualan', 'like', '%' . $request->search . '%');
                    });
                }
            }
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $perPage = $request->input('per_page', 10);
        $allowedPerPage = [10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $penjualan = $query->orderBy('tanggal', 'desc')->paginate($perPage);

        return view('data_penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        return view('data_penjualan.create');
    }

   public function store(Request $request)
{
    $request->validate([
        'tanggal' => 'required|string',
        'total_penjualan' => 'required|string', // biarkan string dulu
        'total_pesanan' => 'required|integer|min:0',
    ]);

    // Parse tanggal Indonesia
    $tanggalParsed = $this->parseIndonesianDate($request->tanggal);
    if (!$tanggalParsed) {
        return back()->withInput()->with('error', 'Format tanggal tidak valid (contoh: 22 Maret 2026)');
    }

    // Cek unique
    $existing = Penjualan::whereDate('tanggal', $tanggalParsed)->first();
    if ($existing) {
        return back()->withInput()->with('error', 'Data untuk tanggal ' . $request->tanggal . ' sudah ada');
    }

    // Bersihkan nominal
    $totalPenjualanBersih = $this->cleanNumber($request->total_penjualan);

    $tanggal = Carbon::parse($tanggalParsed);

    Penjualan::create([
        'tanggal' => $tanggalParsed,
        'total_penjualan' => $totalPenjualanBersih,
        'total_pesanan' => $request->total_pesanan,
        'hari_dalam_minggu' => $tanggal->dayOfWeek,
        'weekend' => $tanggal->isWeekend() ? 1 : 0,
        'bulan' => $tanggal->month,
        'tahun' => $tanggal->year,
    ]);

    return redirect()->route('data_penjualan.index')->with('success', 'Data berhasil ditambahkan');
}

    public function edit($tanggal)
    {
        $data_penjualan = Penjualan::where('tanggal', $tanggal)->firstOrFail();
        return view('data_penjualan.edit', compact('data_penjualan'));
    }

    public function update(Request $request, $tanggal)
    {
        $data_penjualan = Penjualan::where('tanggal', $tanggal)->firstOrFail();
        
        $request->validate([
            'tanggal' => 'required|date|unique:data_penjualan,tanggal,' . $tanggal . ',tanggal',
            'total_penjualan' => 'required|string',
            'total_pesanan' => 'required|integer|min:0',
        ]);

        $existing = Penjualan::whereDate('tanggal', $request->tanggal)
            ->where('tanggal', '!=', $tanggal)
            ->first();
        
        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Data penjualan untuk tanggal ' . $request->tanggal . ' sudah ada!');
        }

        $newTanggal = Carbon::parse($request->tanggal);

        try {
            $data_penjualan->update([
                'tanggal' => $request->tanggal,
                'total_penjualan' => $this->cleanNumber($request->total_penjualan),
                'total_pesanan' => (int) $request->total_pesanan,
                'hari_dalam_minggu' => $newTanggal->dayOfWeek,
                'weekend' => $newTanggal->isWeekend() ? 1 : 0,
                'bulan' => $newTanggal->month,
                'tahun' => $newTanggal->year,
            ]);

            return redirect()->route('data_penjualan.index')
                ->with('success', 'Data berhasil diperbarui');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($tanggal)
    {
        try {
            $data_penjualan = Penjualan::where('tanggal', $tanggal)->firstOrFail();
            $data_penjualan->delete();
            return redirect()
                ->route('data_penjualan.index')
                ->with('success', 'Data penjualan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()
                ->route('data_penjualan.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'tanggal_list' => 'required|array',
            'tanggal_list.*' => 'date'
        ]);
        
        try {
            $deleted = Penjualan::whereIn('tanggal', $request->tanggal_list)->delete();
            
            return response()->json([
                'success' => true, 
                'message' => "Berhasil menghapus {$deleted} data penjualan"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $import = new \App\Imports\PenjualanImport();
            $import->import($request->file('file'));
            
            $successCount = $import->getSuccessCount();
            $failedCount = $import->getFailedCount();
            $zeroValueCount = $import->getZeroValueCount();
            $errors = $import->getErrors();
            
            if ($successCount > 0 && $failedCount == 0) {
                $message = "✅ Berhasil mengimport {$successCount} data penjualan!";
                if ($zeroValueCount > 0) {
                    $message .= " ({$zeroValueCount} data dengan nilai 0 berhasil dimasukkan)";
                }
                return redirect()->route('data_penjualan.index')
                    ->with('success', $message);
            } 
            elseif ($successCount > 0 && $failedCount > 0) {
                $errorMsg = "⚠️ Berhasil import {$successCount} data, gagal {$failedCount} data.";
                if ($zeroValueCount > 0) {
                    $errorMsg .= " ({$zeroValueCount} data bernilai 0 berhasil dimasukkan)";
                }
                if (count($errors) > 0) {
                    $errorMsg .= " Detail: " . implode(", ", array_slice($errors, 0, 5));
                }
                return redirect()->route('data_penjualan.index')
                    ->with('warning', $errorMsg);
            } 
            else {
                $errorMsg = "❌ Gagal import data. ";
                if (count($errors) > 0) {
                    $errorMsg .= implode(", ", array_slice($errors, 0, 5));
                } else {
                    $errorMsg .= "Tidak ada data yang valid. Pastikan format file sesuai.";
                }
                return redirect()->route('data_penjualan.index')
                    ->with('error', $errorMsg);
            }
            
        } catch (\Exception $e) {
            return redirect()->route('data_penjualan.index')
                ->with('error', '❌ Gagal import: ' . $e->getMessage());
        }
    }

   private function cleanNumber($number)
{
    // Hanya ambil digit 0-9, buang semua karakter lain
    return (int) preg_replace('/[^0-9]/', '', $number);
}
}