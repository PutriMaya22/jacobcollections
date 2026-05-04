<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Prediksi;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRole = $user->role ?? 'guest';
        $selectedMonth = $request->get('bulan_filter', now()->format('Y-m'));

        try {
            $selectedMonthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $selectedMonth = now()->format('Y-m');
            $selectedMonthCarbon = now()->copy()->startOfMonth();
        }

        // STATISTIK UTAMA
// =====================
// Dari data_barang (produk)
$totalBarang = (int) DB::table('data_barang')->count();
$totalStok = (int) DB::table('data_barang')->sum('stok');
$barangHabis = (int) DB::table('data_barang')->where('stok', '<=', 0)->count();
$barangMenipis = (int) DB::table('data_barang')->where('stok', '>', 0)->where('stok', '<=', 10)->count();

// Dari data_penjualan (transaksi)
$totalPenjualan = (float) DB::table('data_penjualan')->sum('total_penjualan');
$totalPesanan = (int) DB::table('data_barang')->sum('total_pesanan');

// Perhitungan
$rataRataPesanan = $totalPesanan > 0
    ? (float) ($totalPenjualan / $totalPesanan)
    : 0;

        $penjualanBulanIniCard = (float) (
            Penjualan::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)
                ->sum('total_penjualan') ?? 0
        );

        $bulanLalu = now()->copy()->subMonth();

        $penjualanBulanLalu = (float) (
            Penjualan::whereYear('tanggal', $bulanLalu->year)
                ->whereMonth('tanggal', $bulanLalu->month)
                ->sum('total_penjualan') ?? 0
        );

        $growthPenjualan = $penjualanBulanLalu > 0
            ? (float) ((($penjualanBulanIniCard - $penjualanBulanLalu) / $penjualanBulanLalu) * 100)
            : 0;

        // versi cache dinaikkan agar data lama tidak ikut terbaca
        $cacheKey = 'dashboard_complete_v3_' . now()->format('Y-m-d-H') . '_' . $userRole . '_' . $selectedMonth;

        $data = Cache::remember(
            $cacheKey,
            now()->addMinutes(15),
            function () use (
                $userRole,
                $selectedMonth,
                $selectedMonthCarbon,
                $totalBarang,
                $totalStok,
                $barangHabis,
                $barangMenipis,
                $totalPenjualan,
                $totalPesanan,
                $rataRataPesanan,
                $penjualanBulanIniCard,
                $growthPenjualan
            ) {
                // =====================
                // TREN PENJUALAN 30 HARI TERAKHIR
                // =====================
                $start30Hari = now()->copy()->subDays(29)->startOfDay();
                $end30Hari = now()->copy()->endOfDay();

                $penjualan30Hari = Penjualan::whereBetween('tanggal', [
                        $start30Hari->toDateString(),
                        $end30Hari->toDateString(),
                    ])
                    ->orderBy('tanggal', 'asc')
                    ->get()
                    ->keyBy(function ($item) {
                        return Carbon::parse($item->tanggal)->format('Y-m-d');
                    });

                $tanggalLabels = [];
                $penjualanPerTanggal = [];
                $pesananPerTanggal = [];

                $loopTanggal = $start30Hari->copy();
                while ($loopTanggal->lte($end30Hari)) {
                    $key = $loopTanggal->format('Y-m-d');
                    $record = $penjualan30Hari->get($key);

                    $tanggalLabels[] = $loopTanggal->translatedFormat('d M');
                    $penjualanPerTanggal[] = (float) ($record->total_penjualan ?? 0);
                    $pesananPerTanggal[] = (int) ($record->total_pesanan ?? 0);

                    $loopTanggal->addDay();
                }

                // =====================
                // PENJUALAN PER BULAN (12 BULAN TERAKHIR)
                // FIX:
                // - selalu bentuk 12 bulan penuh
                // - bulan tanpa data tetap muncul dengan nilai 0
                // - tidak lagi raw subYear() yang bisa bikin periode terasa 13 bulan
                // =====================
                $monthlySeries12 = $this->buildMonthlySalesSeries(
                    now()->copy()->startOfMonth()->subMonths(11),
                    now()->copy()->startOfMonth()
                );

                $bulanLabels12 = $monthlySeries12->pluck('bulan')->toArray();
                $bulanPenjualan12 = $monthlySeries12->pluck('total')->toArray();

                // =====================
                // AKTIVITAS TERBARU
                // =====================
                $recentPenjualan = Penjualan::latest('tanggal')->take(5)->get();
                $recentPrediksi = Prediksi::latest('tanggal')->take(5)->get();

                foreach ($recentPrediksi as $p) {
                    if ($p->penjualan_aktual !== null && $p->hasil_prediksi !== null) {
                        $error = (float) $p->penjualan_aktual - (float) $p->hasil_prediksi;
                        $p->error = $error;
                        $p->error_sign = $error >= 0 ? '+' : '-';
                        $p->error_class = $error >= 0 ? 'text-green-600' : 'text-red-600';
                        $p->formatted_error = number_format(abs($error), 0, ',', '.');
                        $p->static_mape_display = $p->static_mape !== null
                            ? number_format($p->static_mape, 2) . '%'
                            : '-';
                    } else {
                        $p->error = null;
                        $p->error_sign = '';
                        $p->error_class = '';
                        $p->formatted_error = '';
                        $p->static_mape_display = '-';
                    }
                }

                // =====================
                // INSIGHTS
                // =====================
                $insights = $this->generateInsights(
                    $barangMenipis,
                    $barangHabis,
                    $growthPenjualan,
                    0,
                    0
                );

                return [
                    'userRole' => $userRole,
                    'selectedMonth' => $selectedMonth,

                    'totalBarang' => $totalBarang,
                    'totalStok' => $totalStok,
                    'barangHabis' => $barangHabis,
                    'barangMenipis' => $barangMenipis,

                    'totalPenjualan' => $totalPenjualan,
                    'totalPesanan' => $totalPesanan,
                    'rataRataPesanan' => $rataRataPesanan,

                    'penjualanBulanIni' => $penjualanBulanIniCard,
                    'growthPenjualan' => $growthPenjualan,

                    'tanggalLabels' => $tanggalLabels,
                    'penjualanPerTanggal' => $penjualanPerTanggal,
                    'pesananPerTanggal' => $pesananPerTanggal,

                    'bulanLabels12' => $bulanLabels12,
                    'bulanPenjualan12' => $bulanPenjualan12,

                    'recentPenjualan' => $recentPenjualan,
                    'recentPrediksi' => $recentPrediksi,

                    'insights' => $insights,
                ];
            }
        );

        // =====================
        // DAFTAR BULAN TERSEDIA
        // =====================
        $availableMonths = Prediksi::select(DB::raw('DISTINCT DATE_FORMAT(tanggal, "%Y-%m") as bulan'))
            ->orderBy('bulan', 'desc')
            ->pluck('bulan')
            ->filter()
            ->values()
            ->toArray();

        if (empty($availableMonths)) {
            $availableMonths = [now()->format('Y-m')];
        }

        $monthOptions = [];
        foreach ($availableMonths as $month) {
            try {
                $monthOptions[$month] = Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
            } catch (\Exception $e) {
                $monthOptions[$month] = $month;
            }
        }

        $viewData = array_merge($data, [
            'availableMonths' => $availableMonths,
            'monthOptions' => $monthOptions,
            'trackingMonthOptions' => $monthOptions,
            'currentMonth' => $selectedMonth,
        ]);

        return view('dashboard', $viewData);
    }

    // =====================
    // HELPER INSIGHTS
    // =====================
    private function generateInsights(
        int $barangMenipis,
        int $barangHabis,
        float $growthPenjualan,
        float $totalRealisasiBulan = 0,
        float $totalTargetBulan = 0
    ): array {
        $insights = [];

        if ($barangMenipis > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'message' => "Terdapat {$barangMenipis} produk dengan stok menipis",
                'action' => 'Segera restock',
            ];
        }

        if ($barangHabis > 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => '❌',
                'message' => "{$barangHabis} produk sudah habis terjual",
                'action' => 'Restock segera',
            ];
        }

        if ($growthPenjualan > 10) {
            $insights[] = [
                'type' => 'success',
                'icon' => '',
                'message' => "Penjualan meningkat " . number_format($growthPenjualan, 1) . "%",
                'action' => 'Pertahankan strategi',
            ];
        } elseif ($growthPenjualan < -10) {
            $insights[] = [
                'type' => 'danger',
                'icon' => '',
                'message' => "Penjualan menurun " . number_format(abs($growthPenjualan), 1) . "%",
                'action' => 'Evaluasi strategi',
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'type' => 'info',
                'icon' => '',
                'message' => 'Semua metrik dalam kondisi normal',
                'action' => 'Monitor terus performa bisnis',
            ];
        }

        return $insights;
    }

    // =====================
    // API PENJUALAN PER PERIODE
    // =====================
    public function getPenjualanPerPeriode(Request $request)
    {
        try {
            $periode = $request->get('periode', '1tahun');

            [$startMonth, $endMonth] = $this->resolvePeriodRange($periode);

            $penjualanGrouped = $this->buildMonthlySalesSeries($startMonth, $endMonth);

            $labels = $penjualanGrouped->pluck('bulan')->toArray();
            $values = $penjualanGrouped->pluck('total')->toArray();
            $details = $penjualanGrouped->pluck('detail')->toArray();

            if (empty($labels)) {
                return response()->json([
                    'labels' => ['Tidak Ada Data'],
                    'values' => [0],
                    'details' => [],
                    'periode' => $periode,
                    'title' => 'Data Penjualan',
                    'total_data' => 0,
                ]);
            }

            return response()->json([
                'labels' => $labels,
                'values' => $values,
                'details' => $details,
                'periode' => $periode,
                'title' => 'Data Penjualan',
                'total_data' => count($labels),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getPenjualanPerPeriode: ' . $e->getMessage());

            return response()->json([
                'labels' => ['Error'],
                'values' => [0],
                'details' => [],
                'periode' => $request->get('periode', '1tahun'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    // =====================
    // HELPER RANGE PERIODE BULANAN
    // FIX UTAMA:
    // - 6 bulan = current month + 5 bulan ke belakang
    // - 1 tahun = current month + 11 bulan ke belakang
    // - 2 tahun = current month + 23 bulan ke belakang
    // - 3 tahun = current month + 35 bulan ke belakang
    // Ini mencegah jumlah bulan jadi berlebih / terlihat dobel
    // =====================
    private function resolvePeriodRange(string $periode): array
    {
        $endMonth = now()->copy()->startOfMonth();

        switch ($periode) {
            case '6bulan':
                $startMonth = $endMonth->copy()->subMonths(5);
                break;

            case '2tahun':
                $startMonth = $endMonth->copy()->subMonths(23);
                break;

            case '3tahun':
                $startMonth = $endMonth->copy()->subMonths(35);
                break;

            case 'semua':
                $minTanggal = Penjualan::min('tanggal');

                if ($minTanggal) {
                    $startMonth = Carbon::parse($minTanggal)->startOfMonth();
                } else {
                    $startMonth = $endMonth->copy();
                }
                break;

            case '1tahun':
            default:
                $startMonth = $endMonth->copy()->subMonths(11);
                break;
        }

        return [$startMonth, $endMonth];
    }

    // =====================
    // HELPER PEMBENTUK SERIES BULANAN LENGKAP
    // FIX UTAMA:
    // - semua bulan di rentang selalu dibuat
    // - bulan kosong tetap ada dengan nilai 0
    // - urutan bulan stabil
    // =====================
    private function buildMonthlySalesSeries(Carbon $startMonth, Carbon $endMonth): Collection
    {
        $queryStart = $startMonth->copy()->startOfMonth()->toDateString();
        $queryEnd = $endMonth->copy()->endOfMonth()->toDateString();

        $penjualan = Penjualan::whereBetween('tanggal', [$queryStart, $queryEnd])
            ->orderBy('tanggal', 'asc')
            ->get();

        $groupedByMonth = $penjualan->groupBy(function ($item) {
            return Carbon::parse($item->tanggal)->format('Y-m');
        });

        $result = collect();
        $cursor = $startMonth->copy()->startOfMonth();
        $lastMonth = $endMonth->copy()->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $ym = $cursor->format('Y-m');
            $group = $groupedByMonth->get($ym, collect());

            $detail = $this->buildMonthlyDetail($ym, $group);

            $result->push([
                'bulan' => $cursor->translatedFormat('F Y'),
                'total' => (float) $detail['total_penjualan'],
                'sort_key' => $ym,
                'detail' => $detail,
            ]);

            $cursor->addMonthNoOverflow();
        }

        return $result;
    }

    // =====================
    // HELPER DETAIL PENJUALAN BULANAN
    // =====================
    private function buildMonthlyDetail(string $yearMonth, Collection $group): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $penjualanByDate = $group->keyBy(function ($item) {
            return Carbon::parse($item->tanggal)->format('Y-m-d');
        });

        $totalPenjualan = (float) $group->sum('total_penjualan');

        $jumlahHariTransaksi = (int) $group->filter(function ($item) {
            return (float) ($item->total_penjualan ?? 0) > 0;
        })->count();

        $rataRataHarian = $monthStart->daysInMonth > 0
            ? $totalPenjualan / $monthStart->daysInMonth
            : 0;

        $topSale = $group->sortByDesc(function ($item) {
            return (float) ($item->total_penjualan ?? 0);
        })->first();

        $penjualanTertinggi = [
            'tanggal' => $topSale
                ? Carbon::parse($topSale->tanggal)->translatedFormat('d M Y')
                : '-',
            'nilai' => $topSale
                ? (float) ($topSale->total_penjualan ?? 0)
                : 0,
        ];

        $eventKhusus = [];
        foreach ($this->getSpecialEventDates($monthStart) as $eventRule) {
            $eventKhusus[] = $this->buildEventDetail(
                $penjualanByDate,
                $eventRule['date'],
                $eventRule['type'],
                $eventRule['description'],
                $eventRule['icon']
            );
        }

        $totalEventKhusus = collect($eventKhusus)->sum(function ($item) {
            return (float) ($item['penjualan'] ?? 0);
        });

        $kontribusiEventKhusus = $totalPenjualan > 0
            ? ($totalEventKhusus / $totalPenjualan) * 100
            : 0;

        $eventDenganPenjualan = (int) collect($eventKhusus)
            ->filter(function ($item) {
                return ($item['ada_penjualan'] ?? false) && ((float) ($item['penjualan'] ?? 0) > 0);
            })
            ->count();

        $nonEventPenjualan = max(0, $totalPenjualan - $totalEventKhusus);

        return [
            'bulan_key' => $yearMonth,
            'bulan_label' => $monthStart->translatedFormat('F Y'),
            'start_date' => $monthStart->toDateString(),
            'end_date' => $monthEnd->toDateString(),
            'days_in_month' => $monthStart->daysInMonth,
            'total_penjualan' => round($totalPenjualan, 2),
            'rata_rata_harian' => round($rataRataHarian, 2),
            'jumlah_hari_transaksi' => $jumlahHariTransaksi,
            'penjualan_tertinggi' => $penjualanTertinggi,
            'total_event_khusus' => round($totalEventKhusus, 2),
            'kontribusi_event_khusus' => round($kontribusiEventKhusus, 2),
            'event_dengan_penjualan' => $eventDenganPenjualan,
            'non_event_penjualan' => round($nonEventPenjualan, 2),
            'event_khusus' => $eventKhusus,
        ];
    }

    private function getSpecialEventDates(Carbon $monthStart): array
    {
        $rules = [];

        // Pesta Gajian tiap tanggal 25
        if (25 <= $monthStart->daysInMonth) {
            $rules[] = [
                'type' => 'Pesta Gajian',
                'description' => 'Event gajian bulanan setiap tanggal 25',
                'icon' => '',
                'date' => $monthStart->copy()->day(25),
            ];
        }

        // Promo bulanan tanggal kembar sesuai bulan: 2.2, 3.3, 4.4, dst
        $monthNumber = (int) $monthStart->format('n');
        if ($monthNumber <= $monthStart->daysInMonth) {
            $rules[] = [
                'type' => 'Promo Bulanan',
                'description' => "Promo tanggal kembar {$monthNumber}.{$monthNumber}",
                'icon' => '',
                'date' => $monthStart->copy()->day($monthNumber),
            ];
        }

        // Promo mingguan pertengahan bulan
        foreach ([15, 16, 17] as $day) {
            if ($day <= $monthStart->daysInMonth) {
                $rules[] = [
                    'type' => 'Promo Mingguan',
                    'description' => "Promo pertengahan bulan tanggal {$day}",
                    'icon' => '',
                    'date' => $monthStart->copy()->day($day),
                ];
            }
        }

        return $rules;
    }

    private function buildEventDetail(
        Collection $penjualanByDate,
        Carbon $eventDate,
        string $type,
        string $description,
        string $icon
    ): array {
        $record = $penjualanByDate->get($eventDate->format('Y-m-d'));
        $penjualan = (float) ($record->total_penjualan ?? 0);

        return [
            'type' => $type,
            'description' => $description,
            'icon' => $icon,
            'tanggal' => $eventDate->translatedFormat('d M Y'),
            'tanggal_raw' => $eventDate->toDateString(),
            'hari' => $eventDate->translatedFormat('l'),
            'penjualan' => round($penjualan, 2),
            'ada_penjualan' => $record !== null,
        ];
    }

    // =====================
    // API LIVE TRACKING PREDIKSI
    // =====================
    public function getLiveTracking(Request $request)
    {
        try {
            if ((auth()->user()->role ?? null) !== 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Owner yang dapat melihat tracking realisasi.',
                ], 403);
            }

            $tanggalMulai = $request->get('tanggal_mulai');
            $tanggalSelesai = $request->get('tanggal_selesai');
            $bulan = $request->get('bulan');

            if (!empty($tanggalMulai) && !empty($tanggalSelesai)) {
                $startDate = Carbon::parse($tanggalMulai)->startOfDay();
                $endDate = Carbon::parse($tanggalSelesai)->endOfDay();
            } elseif (!empty($bulan)) {
                $parsedMonth = Carbon::createFromFormat('Y-m', $bulan);
                $startDate = $parsedMonth->copy()->startOfMonth();
                $endDate = $parsedMonth->copy()->endOfMonth();
            } else {
                $startDate = null;
                $endDate = null;
            }

            $query = Prediksi::orderBy('tanggal', 'asc');

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal', [
                    $startDate->toDateString(),
                    $endDate->toDateString(),
                ]);
            }

            $prediksiData = $query->get();

            $data = [];
            $totalTarget = 0;
            $totalRealisasi = 0;
            $hariBerlalu = 0;

            foreach ($prediksiData as $prediksi) {
                $target = (float) ($prediksi->hasil_prediksi ?? 0);
                $realisasi = $prediksi->penjualan_aktual !== null ? (float) $prediksi->penjualan_aktual : null;
                $pencapaian = ($target > 0 && $realisasi !== null) ? (($realisasi / $target) * 100) : 0;

                if ($realisasi !== null) {
                    $hariBerlalu++;
                }

                if ($realisasi === null) {
                    $status = 'Belum Update';
                    $statusIcon = '';
                    $statusBadgeClass = 'bg-gray-100 text-gray-700';
                } elseif ($pencapaian >= 100) {
                    $status = 'Tercapai';
                    $statusIcon = '';
                    $statusBadgeClass = 'bg-green-100 text-green-700';
                } elseif ($pencapaian >= 80) {
                    $status = 'Mendekati';
                    $statusIcon = '';
                    $statusBadgeClass = 'bg-yellow-100 text-yellow-700';
                } elseif ($pencapaian >= 50) {
                    $status = 'Progress';
                    $statusIcon = '';
                    $statusBadgeClass = 'bg-blue-100 text-blue-700';
                } else {
                    $status = 'Kurang';
                    $statusIcon = '';
                    $statusBadgeClass = 'bg-red-100 text-red-700';
                }

                $data[] = [
                    'id' => $prediksi->id,
                    'tanggal' => Carbon::parse($prediksi->tanggal)->translatedFormat('d M Y'),
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'pencapaian' => round($pencapaian, 1),
                    'status' => $status,
                    'status_icon' => $statusIcon,
                    'status_badge_class' => $statusBadgeClass,
                    'last_update' => $prediksi->updated_at ? $prediksi->updated_at->format('H:i') : null,
                    'is_weekend' => Carbon::parse($prediksi->tanggal)->isWeekend(),
                ];

                $totalTarget += $target;
                $totalRealisasi += ($realisasi ?? 0);
            }

            $totalHari = count($data);
            $hariTersisa = max(0, $totalHari - $hariBerlalu);
            $totalPencapaian = $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0;

            $kekurangan = max(0, $totalTarget - $totalRealisasi);

            $targetHarian = $totalHari > 0 ? ($totalTarget / $totalHari) : 0;
            $rataHarian = $hariBerlalu > 0 ? ($totalRealisasi / $hariBerlalu) : 0;
            $sisaTarget = max(0, $totalTarget - $totalRealisasi);

            return response()->json([
                'success' => true,
                'data' => $data,
                'summary' => [
                    'total_target' => round($totalTarget, 2),
                    'total_realisasi' => round($totalRealisasi, 2),
                    'total_pencapaian' => round($totalPencapaian, 1),
                    'kekurangan' => round($kekurangan, 2),
                    'target_harian' => round($targetHarian, 2),
                    'rata_harian' => round($rataHarian, 2),
                    'sisa_target' => round($sisaTarget, 2),
                    'hari_berlalu' => $hariBerlalu,
                    'hari_tersisa' => $hariTersisa,
                    'total_data' => $totalHari,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getLiveTracking: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'summary' => [
                    'total_target' => 0,
                    'total_realisasi' => 0,
                    'total_pencapaian' => 0,
                    'kekurangan' => 0,
                    'target_harian' => 0,
                    'rata_harian' => 0,
                    'sisa_target' => 0,
                    'hari_berlalu' => 0,
                    'hari_tersisa' => 0,
                    'total_data' => 0,
                ],
            ], 500);
        }
    }

    // =====================
    // UPDATE REALISASI
    // =====================
    public function updateRealisasi(Request $request, $id)
    {
        try {
            if ((auth()->user()->role ?? null) !== 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Owner yang dapat mengupdate realisasi prediksi.',
                ], 403);
            }

            $request->validate([
                'realisasi' => 'required|numeric|min:0',
                'catatan' => 'nullable|string|max:500',
            ]);

            $prediksi = Prediksi::findOrFail($id);

            Penjualan::updateOrCreate(
                ['tanggal' => $prediksi->tanggal],
                [
                    'total_penjualan' => $request->realisasi,
                    'total_pesanan' => $prediksi->total_pesanan ?? 0,
                ]
            );

            $tempError = (float) $request->realisasi - (float) $prediksi->hasil_prediksi;

            $prediksi->penjualan_aktual = $request->realisasi;
            $prediksi->error = $tempError;
            $prediksi->catatan = $request->catatan;
            $prediksi->pesanan_updated_at = now();

            if ($prediksi->static_error === null) {
                $prediksi->static_error = $tempError;
            }

            $prediksi->save();

            $this->clearDashboardCache();

            return response()->json([
                'success' => true,
                'message' => 'Realisasi berhasil diupdate',
                'data' => [
                    'id' => $prediksi->id,
                    'target' => $prediksi->hasil_prediksi,
                    'realisasi' => (float) $request->realisasi,
                    'static_mape' => $prediksi->static_mape,
                    'static_rmse' => $prediksi->static_rmse,
                    'static_r_squared' => $prediksi->static_r_squared,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updateRealisasi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal update: ' . $e->getMessage(),
            ], 500);
        }
    }

   // =====================
// =====================
// API DATA PREDIKSI
// =====================
public function getPrediksiData(Request $request)
{
    try {
        $bulan = $request->get('bulan', now()->format('Y-m'));

        try {
            $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
        } catch (\Exception $e) {
            $startDate = now()->copy()->startOfMonth();
            $endDate = now()->copy()->endOfMonth();
            $bulan = now()->format('Y-m');
        }

        $prediksiData = Prediksi::whereBetween('tanggal', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderBy('tanggal', 'asc')
            ->get();

        // CEK APAKAH ADA DATA
        if ($prediksiData->isEmpty()) {
            return response()->json([
                'success' => true,
                'has_data' => false,
                'message' => 'Tidak ada data prediksi untuk bulan ini',
                'labels' => [],
                'prediksi' => [],
                'aktual' => [],
                'jumlah_label' => 0,
                'source_mode' => 'empty',
                'selected_bulan' => $bulan
            ]);
        }

        // Proses data jika ada
        $labels = [];
        $prediksiValues = [];
        $aktualValues = [];
        $totalPrediksi = 0;
        $totalAktual = 0;
        $coverageAktual = 0;

        foreach ($prediksiData as $data) {
            $tanggal = Carbon::parse($data->tanggal);
            $nilaiPrediksi = (float) ($data->hasil_prediksi ?? 0);
            $nilaiAktual = $data->penjualan_aktual !== null ? (float) $data->penjualan_aktual : null;

            $labels[] = $tanggal->translatedFormat('d M');
            $prediksiValues[] = $nilaiPrediksi;
            $aktualValues[] = $nilaiAktual;

            $totalPrediksi += $nilaiPrediksi;
            if ($nilaiAktual !== null) {
                $totalAktual += $nilaiAktual;
                $coverageAktual++;
            }
        }

        $selisih = $totalAktual - $totalPrediksi;
        $akurasi = $totalPrediksi > 0 ? round(($totalAktual / $totalPrediksi) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'has_data' => true,
            'labels' => $labels,
            'prediksi' => $prediksiValues,
            'aktual' => $aktualValues,
            'total_prediksi' => round($totalPrediksi, 2),
            'total_aktual' => round($totalAktual, 2),
            'selisih' => round($selisih, 2),
            'akurasi' => $akurasi,
            'jumlah_label' => count($labels),
            'coverage_aktual' => $coverageAktual,
            'source_mode' => 'selected_month',
            'selected_bulan' => $bulan,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error getPrediksiData: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'has_data' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'labels' => [],
            'prediksi' => [],
            'aktual' => [],
            'jumlah_label' => 0,
            'source_mode' => 'error',
        ]);
    }
}
    // =====================
    // CACHE HELPERS
    // =====================
    private static function getDashboardCacheKeys(): array
    {
        $hours = [
            now()->format('Y-m-d-H'),
            now()->copy()->subHour()->format('Y-m-d-H'),
        ];

        $roles = ['owner', 'admin', 'guest'];

        $months = Prediksi::select(DB::raw('DISTINCT DATE_FORMAT(tanggal, "%Y-%m") as bulan'))
            ->pluck('bulan')
            ->filter()
            ->values()
            ->toArray();

        $currentMonth = now()->format('Y-m');
        if (!in_array($currentMonth, $months)) {
            $months[] = $currentMonth;
        }

        $keys = [];
        foreach ($hours as $hour) {
            foreach ($roles as $role) {
                foreach ($months as $month) {
                    $keys[] = 'dashboard_complete_v3_' . $hour . '_' . $role . '_' . $month;
                }
            }
        }

        return array_unique($keys);
    }

    private function clearDashboardCache(): void
    {
        foreach (self::getDashboardCacheKeys() as $key) {
            Cache::forget($key);
        }
    }

    public static function invalidateDashboardCache(): bool
    {
        foreach (self::getDashboardCacheKeys() as $key) {
            Cache::forget($key);
        }

        return true;
    }
}
