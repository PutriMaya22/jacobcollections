@extends('layouts.app')

@section('title', 'Dashboard - JacobCollections')

@section('content')
@php
    $isOwner = auth()->check() && auth()->user()->role === 'owner';
@endphp

<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .insight-card {
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    @keyframes modal-pop {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    .animate-modal-pop {
        animation: modal-pop 0.2s ease-out;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .auto-refresh-toggle {
        transition: all 0.3s ease;
    }
    .auto-refresh-toggle.active {
        background-color: #10B981;
        color: white;
    }
    .detail-summary-card {
        border: 1px solid #E5E7EB;
        background: #F9FAFB;
        border-radius: 0.75rem;
        padding: 1rem;
    }
    .detail-event-row:hover {
        background: #F9FAFB;
    }
    .detail-month-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 9999px;
        background: #EEF2FF;
        color: #4338CA;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .soft-card {
        border: 1px solid #E5E7EB;
        background: linear-gradient(180deg, #FFFFFF 0%, #F9FAFB 100%);
        border-radius: 1rem;
        padding: 1rem;
    }
    .chart-panel {
        border: 1px solid #E5E7EB;
        background: linear-gradient(180deg, #FFFFFF 0%, #FCFCFF 100%);
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.06);
    }
</style>

{{-- ================= CARD STATISTIK ================= --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-blue-100 text-xs uppercase tracking-wider mb-1">Jenis Item</p>
                <h3 class="text-3xl font-bold">{{ number_format($totalBarang) }}</h3>
                <p class="text-blue-100 text-sm mt-2">Total Stok: {{ number_format($totalStok) }} pcs</p>
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
    </div>

    <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-green-100 text-xs uppercase tracking-wider mb-1">Total Penjualan</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
    </div>

    <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-purple-100 text-xs uppercase tracking-wider mb-1">Penjualan Bulan Ini</p>
                <h3 class="text-2xl font-bold">Rp {{ number_format($penjualanBulanIni, 0, ',', '.') }}</h3>
                @if(isset($penjualanBulanIni) && $penjualanBulanIni > 0)
                    @if(isset($growthPenjualan) && $growthPenjualan != 0)
                        @if($growthPenjualan > 0)
                            <p class="text-green-200 text-sm mt-2">↑ {{ number_format($growthPenjualan, 1) }}% dari bulan lalu</p>
                        @elseif($growthPenjualan < 0)
                            <p class="text-red-200 text-sm mt-2">↓ {{ number_format(abs($growthPenjualan), 1) }}% dari bulan lalu</p>
                        @endif
                    @else
                        <p class="text-purple-100 text-sm mt-2">Data bulan lalu belum tersedia</p>
                    @endif
                @else
                    <p class="text-yellow-200 text-sm mt-2">Belum ada penjualan bulan ini</p>
                @endif
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
    </div>
</div>

{{-- ================= GRAFIK UTAMA ================= --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Tren 30 Hari --}}
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Tren Penjualan 30 Hari Terakhir</h3>
        <div class="h-80">
            <canvas id="penjualanTanggalChart"></canvas>
        </div>
    </div>

    {{-- Perbandingan Prediksi vs Aktual --}}
    <div class="bg-white rounded-xl shadow-lg p-6 chart-panel">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
            <div>
                <h3 class="text-lg font-semibold">Perbandingan Prediksi vs Aktual</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Prediksi ditampilkan sebagai garis tren, aktual sebagai batang penjualan harian.
                </p>
            </div>
            <input type="month" id="bulanFilter" value="{{ $currentMonth }}" class="border rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div id="prediksiContainer" class="h-80"></div>
    </div>
</div>

{{-- ================= GRAFIK PENJUALAN PER BULAN ================= --}}
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
        <h3 class="text-lg font-semibold">Penjualan per Bulan</h3>
        <select id="periodeFilter" class="border rounded-lg px-4 py-2 text-sm bg-white">
            <option value="6bulan">6 Bulan Terakhir</option>
            <option value="1tahun" selected>1 Tahun Terakhir</option>
            <option value="2tahun">2 Tahun Terakhir</option>
            <option value="3tahun">3 Tahun Terakhir</option>
            <option value="semua">Semua Data</option>
        </select>
    </div>

    <div class="h-80">
        <canvas id="penjualanBulanChart"></canvas>
    </div>

    <div class="mt-2 text-center text-sm text-gray-500" id="chartInfo"></div>

    <div id="detailBulanSection" class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="text-lg font-semibold">Detail Event JacobColections per Bulan</h4>
                <p class="text-sm text-gray-500">Klik batang pada grafik untuk melihat detail event khusus.</p>
            </div>
            <div id="detailBulanLabel" class="detail-month-chip">Belum dipilih</div>
        </div>

        <div id="detailSummaryCards" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-5 hidden"></div>

        <div id="pieChartContainer" class="hidden mb-6">
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
                <div class="xl:col-span-2 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 shadow-sm border border-gray-200">
                    <h5 class="font-bold text-gray-800 mb-4 text-center text-lg">Kontribusi Penjualan Event</h5>
                    <div class="w-full h-80">
                        <canvas id="eventPieChart"></canvas>
                    </div>
                    <div id="pieChartSummary" class="text-center text-sm text-gray-600 mt-4 font-medium bg-white rounded-lg p-3 border border-gray-200"></div>
                </div>
                <div id="eventInsightPanel" class="xl:col-span-3 soft-card">
                    <div class="text-gray-500 text-sm">Memuat insight event...</div>
                </div>
            </div>
        </div>

        <div id="detailBulanContent">
            <div class="text-center text-gray-500 py-8">Klik salah satu batang pada chart untuk melihat detail penjualan bulanan.</div>
        </div>
    </div>
</div>

{{-- ================= TABEL AKTIVITAS TERBARU ================= --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Penjualan Terbaru</h3>
            <a href="{{ route('data_penjualan.index') }}" class="text-indigo-600 text-sm">Lihat Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left pb-2">Tanggal</th>
                        <th class="text-right pb-2">Penjualan</th>
                        <th class="text-right pb-2">Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPenjualan as $p)
                        <tr class="border-b">
                            <td class="py-2">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($p->total_penjualan, 0, ',', '.') }}</td>
                            <td class="py-2 text-right">{{ number_format($p->total_pesanan) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Riwayat Prediksi Terbaru</h3>
            <a href="{{ route('prediksi') }}" class="text-indigo-600 text-sm">Lihat Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left pb-2">Tanggal</th>
                        <th class="text-right pb-2">Prediksi</th>
                        <th class="text-right pb-2">Aktual</th>
                        <th class="text-right pb-2">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPrediksi as $p)
                        <tr class="border-b">
                            <td class="py-2">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($p->hasil_prediksi, 0, ',', '.') }}</td>
                            <td class="py-2 text-right">
                                @if($p->penjualan_aktual !== null)
                                    Rp {{ number_format($p->penjualan_aktual, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                @if($p->error !== null)
                                    {{ $p->error >= 0 ? '+' : '-' }}Rp {{ number_format(abs($p->error), 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= LIVE TRACKING PREDIKSI ================= --}}
@if($isOwner)
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
        <div>
            <h3 class="text-xl font-bold">Tracking Realisasi Prediksi</h3>
            <p class="text-sm text-gray-500 mt-1">Update realisasi penjualan prediksi per hari dan lihat kekurangan pencapaian prediksi</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <input type="date" id="tanggalMulai" class="border rounded-lg px-3 py-2 text-sm bg-white">
            <input type="date" id="tanggalSelesai" class="border rounded-lg px-3 py-2 text-sm bg-white">
            <select id="trackingBulan" class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option value="">-- Semua Data --</option>
                @foreach($trackingMonthOptions as $bulanValue => $bulanLabel)
                    <option value="{{ $bulanValue }}" @selected($bulanValue === $currentMonth)>{{ $bulanLabel }}</option>
                @endforeach
            </select>
            <button onclick="refreshTracking()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">Refresh</button>
            <button onclick="resetDateFilter()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">Reset Filter</button>
            <button id="toggleAutoRefresh" class="auto-refresh-toggle active bg-green-600 text-white px-4 py-2 rounded-lg text-sm">⏱ Auto-refresh ON</button>
        </div>
    </div>

    <div id="trackingSummary" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-3 text-white text-center">
            <p class="text-xs">Prediksi</p>
            <p class="text-xl font-bold" id="totalTarget">Rp 0</p>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-3 text-white text-center">
            <p class="text-xs">Realisasi</p>
            <p class="text-xl font-bold" id="totalRealisasi">Rp 0</p>
        </div>
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-3 text-white text-center">
            <p class="text-xs">Pencapaian</p>
            <p class="text-xl font-bold" id="totalPencapaian">0%</p>
        </div>
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-3 text-white text-center">
            <p class="text-xs">Kekurangan</p>
            <p class="text-xl font-bold" id="kekurangan">Rp 0</p>
        </div>
    </div>

    <div class="mb-6">
        <div class="flex justify-between text-sm mb-1">
            <span>Progress Pencapaian Prediksi</span>
            <span id="progressPersen">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div id="progressBar" class="h-full rounded-full transition-all duration-500" style="width: 0%; background: linear-gradient(90deg, #10B981, #3B82F6, #8B5CF6);"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-2 flex-wrap gap-2">
            <span>Target Harian: <span id="targetHarian">Rp 0</span></span>
            <span>Rata-rata Harian: <span id="rataHarian">Rp 0</span></span>
            <span>Sisa Target: <span id="sisaTarget">Rp 0</span></span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-right">Prediksi (Rp)</th>
                    <th class="px-4 py-3 text-right">Realisasi (Rp)</th>
                    <th class="px-4 py-3 text-right">Pencapaian</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="trackingTableBody">
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                        <p class="mt-2">Memuat data tracking...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL UPDATE REALISASI --}}
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full animate-modal-pop">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Update Realisasi</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="mb-4">
                <p class="text-gray-600">Tanggal: <span id="modalTanggal" class="font-semibold"></span></p>
                <p class="text-gray-600">Prediksi: <span id="modalTarget" class="font-semibold text-blue-600"></span></p>
            </div>
            <form id="updateRealisasiForm">
                @csrf
                <input type="hidden" id="prediksiId">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Realisasi Penjualan (Rp)</label>
                    <input type="number" id="realisasiInput" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500" placeholder="Masukkan nominal realisasi">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">Simpan</button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ==================== VARIABLES ====================
const isOwner = @json($isOwner);
let bulanChart = null;
let penjualanBulanDetails = [];
let eventPieChart = null;
let prediksiChart = null;
let autoRefreshInterval = null;
let autoRefreshEnabled = true;

// DOM Elements
const periodeSelect = document.getElementById('periodeFilter');
const bulanCanvas = document.getElementById('penjualanBulanChart');
const chartInfo = document.getElementById('chartInfo');
const detailBulanLabel = document.getElementById('detailBulanLabel');
const detailBulanContent = document.getElementById('detailBulanContent');
const detailSummaryCards = document.getElementById('detailSummaryCards');
const eventInsightPanel = document.getElementById('eventInsightPanel');
const bulanFilter = document.getElementById('bulanFilter');
const prediksiContainer = document.getElementById('prediksiContainer');

// ==================== HELPER FUNCTIONS ====================
function formatRupiah(value) {
    const number = Number(value || 0);
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(number));
}

function formatCompactRupiah(value) {
    const number = Number(value || 0);
    if (number >= 1000000000) return 'Rp ' + (number / 1000000000).toFixed(1).replace('.0', '') + ' M';
    if (number >= 1000000) return 'Rp ' + (number / 1000000).toFixed(1).replace('.0', '') + ' Jt';
    if (number >= 1000) return 'Rp ' + (number / 1000).toFixed(0) + ' Rb';
    return formatRupiah(number);
}

function formatPersen(value) {
    return Number(value || 0).toFixed(1) + '%';
}

function formatBulanNama(bulan) {
    const date = new Date(bulan + '-01');
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
}

// ==================== CHART TREN PENJUALAN 30 HARI ====================
const ctxPenjualanTanggal = document.getElementById('penjualanTanggalChart');
if (ctxPenjualanTanggal) {
    new Chart(ctxPenjualanTanggal, {
        type: 'line',
        data: {
            labels: @json($tanggalLabels ?? []),
            datasets: [{
                label: 'Total Penjualan',
                data: @json($penjualanPerTanggal ?? []),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16,185,129,0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { tooltip: { callbacks: { label: ctx => 'Penjualan: ' + formatRupiah(ctx.raw) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: value => formatCompactRupiah(value) } } }
        }
    });
}

// ==================== DETAIL BULAN & EVENT ====================
function renderBulanDetail(detail) {
    if (!detailBulanContent || !detailBulanLabel) return;

    if (!detail) {
        detailBulanLabel.innerHTML = 'Belum dipilih';
        if (detailSummaryCards) detailSummaryCards.classList.add('hidden');
        detailBulanContent.innerHTML = `<div class="text-center text-gray-500 py-8">Klik salah satu batang pada chart untuk melihat detail penjualan bulanan.</div>`;
        document.getElementById('pieChartContainer')?.classList.add('hidden');
        return;
    }

    detailBulanLabel.innerHTML = detail.bulan_label || '-';
    const topSalesValue = detail.penjualan_tertinggi?.nilai || 0;
    const topSalesDate = detail.penjualan_tertinggi?.tanggal || '-';
    const eventSalesCount = Number(detail.event_dengan_penjualan || 0);

    if (detailSummaryCards) {
        detailSummaryCards.classList.remove('hidden');
        detailSummaryCards.innerHTML = `
            <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Total Penjualan</p><p class="text-2xl font-bold text-gray-900 mt-2">${formatRupiah(detail.total_penjualan || 0)}</p><p class="text-sm text-gray-500 mt-1">${detail.jumlah_hari_transaksi || 0} hari transaksi</p></div>
            <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Rata-rata Harian</p><p class="text-2xl font-bold text-indigo-700 mt-2">${formatRupiah(detail.rata_rata_harian || 0)}</p><p class="text-sm text-gray-500 mt-1">${detail.days_in_month || 0} hari di bulan ini</p></div>
            <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Penjualan Tertinggi</p><p class="text-xl font-bold text-emerald-700 mt-2">${formatRupiah(topSalesValue)}</p><p class="text-sm text-gray-500 mt-1">${topSalesDate}</p></div>
            <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Event Dengan Penjualan</p><p class="text-2xl font-bold text-orange-600 mt-2">${eventSalesCount}</p><p class="text-sm text-gray-500 mt-1">Kontribusi ${formatPersen(detail.kontribusi_event_khusus || 0)}</p></div>
        `;
    }

    const eventRows = (detail.event_khusus || []).map(event => {
        const amount = Number(event.penjualan || 0) > 0 ? `<span class="text-green-600 font-semibold">${formatRupiah(event.penjualan || 0)}</span>` : `<span class="text-gray-400 italic">0</span>`;
        return `<tr class="border-b detail-event-row"><td class="px-3 py-3 whitespace-nowrap font-medium">${event.icon} ${event.type}</td><td class="px-3 py-3 whitespace-nowrap">${event.tanggal}</td><td class="px-3 py-3">${event.description}</td><td class="px-3 py-3 text-right">${amount}</td></tr>`;
    }).join('');

    detailBulanContent.innerHTML = `
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5">
            <h5 class="font-semibold text-indigo-700 mb-2">Kalender Event Khusus Bulan Ini</h5>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div class="bg-white rounded-lg p-3 border border-indigo-100"><div class="font-semibold mb-1">Pesta Gajian</div><div class="text-gray-600">Setiap tanggal 25</div></div>
                <div class="bg-white rounded-lg p-3 border border-indigo-100"><div class="font-semibold mb-1">Promo Bulanan</div><div class="text-gray-600">Tanggal kembar sesuai bulan</div></div>
                <div class="bg-white rounded-lg p-3 border border-indigo-100"><div class="font-semibold mb-1">Promo Mingguan</div><div class="text-gray-600">Pertengahan bulan: 15 / 16 / 17</div></div>
            </div>
        </div>
        <div class="overflow-x-auto"><table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden"><thead class="bg-gray-100"><tr><th class="px-3 py-3 text-left">Event</th><th class="px-3 py-3 text-left">Tanggal</th><th class="px-3 py-3 text-left">Keterangan</th><th class="px-3 py-3 text-right">Penjualan</th></tr></thead><tbody>${eventRows || '<tr><td colspan="4" class="text-center py-6 text-gray-500">Tidak ada data event</td></tr>'}</tbody></table></div>
    `;
    renderEventPieChart(detail);
}

function renderEventPieChart(detail) {
    const pieContainer = document.getElementById('pieChartContainer');
    const pieSummary = document.getElementById('pieChartSummary');
    const pieCanvas = document.getElementById('eventPieChart');

    if (!pieContainer || !pieSummary || !pieCanvas) return;

    const events = detail.event_khusus || [];
    const totalPenjualan = Number(detail.total_penjualan || 0);
    const eventsWithSales = events.filter(e => e.ada_penjualan && Number(e.penjualan || 0) > 0);

    if (eventsWithSales.length === 0 || totalPenjualan === 0) {
        pieContainer.classList.add('hidden');
        if (eventPieChart) eventPieChart.destroy();
        if (eventInsightPanel) eventInsightPanel.innerHTML = `<div class="text-gray-500 text-sm">Belum ada penjualan pada tanggal event khusus bulan ini.</div>`;
        return;
    }

    pieContainer.classList.remove('hidden');
    const labels = eventsWithSales.map(e => `${e.icon} ${e.type}`);
    const data = eventsWithSales.map(e => Number(e.penjualan || 0));
    const totalEventSales = data.reduce((a, b) => a + b, 0);
    const nonEventSales = Math.max(0, totalPenjualan - totalEventSales);

    if (nonEventSales > 0) { labels.push('Non-Event'); data.push(nonEventSales); }

    pieSummary.innerHTML = `Total penjualan saat event: <strong>${formatRupiah(totalEventSales)}</strong> • Kontribusi <strong>${((totalEventSales / totalPenjualan) * 100).toFixed(1)}%</strong>`;

    if (eventPieChart) eventPieChart.destroy();
    eventPieChart = new Chart(pieCanvas, {
        type: 'pie',
        data: { labels: labels, datasets: [{ data: data, backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4'], borderWidth: 2, borderColor: '#fff' }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 12, weight: 'bold' }, padding: 15, usePointStyle: true, pointStyle: 'circle' } }, tooltip: { callbacks: { label: ctx => { const val = Number(ctx.raw || 0); const total = ctx.dataset.data.reduce((a, b) => a + b, 0); const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0; return `${ctx.label}: ${formatRupiah(val)} (${pct}%)`; } } } } }
    });

    if (eventInsightPanel) {
        const sortedEvents = [...eventsWithSales].sort((a, b) => Number(b.penjualan || 0) - Number(a.penjualan || 0));
        const topEvent = sortedEvents[0] || null;
        eventInsightPanel.innerHTML = `
            <div class="mb-4"><h5 class="font-bold text-gray-800 text-lg">Insight Event Bulanan</h5><p class="text-sm text-gray-500 mt-1">Ringkasan cepat kontribusi event dan penjualan non-event pada bulan ini.</p></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Penjualan Event</p><p class="text-2xl font-bold text-indigo-700 mt-2">${formatRupiah(totalEventSales)}</p><p class="text-sm text-gray-500 mt-1">${sortedEvents.length} event menghasilkan penjualan</p></div>
                <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Penjualan Non-Event</p><p class="text-2xl font-bold text-gray-900 mt-2">${formatRupiah(nonEventSales)}</p><p class="text-sm text-gray-500 mt-1">Sisa kontribusi di luar tanggal event khusus</p></div>
                <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Event Terkuat</p><p class="text-lg font-bold text-emerald-700 mt-2">${topEvent ? `${topEvent.icon} ${topEvent.type}` : '-'}</p><p class="text-sm text-gray-500 mt-1">${topEvent ? `${topEvent.tanggal} • ${formatRupiah(topEvent.penjualan || 0)}` : 'Belum ada event unggulan'}</p></div>
                <div class="detail-summary-card"><p class="text-xs uppercase tracking-wide text-gray-500">Kontribusi Event</p><p class="text-2xl font-bold text-orange-600 mt-2">${formatPersen(detail.kontribusi_event_khusus || 0)}</p><p class="text-sm text-gray-500 mt-1">Persentase dari total penjualan bulanan</p></div>
            </div>
            <div class="mt-4 bg-white border border-gray-200 rounded-xl p-4"><h6 class="font-semibold text-gray-800 mb-2">Kesimpulan Singkat</h6><p class="text-sm text-gray-600 leading-relaxed">${Number(detail.kontribusi_event_khusus || 0) >= 40 ? 'Event khusus memberi dampak besar terhadap performa bulan ini. Strategi promo di tanggal event layak dipertahankan atau diperkuat.' : (Number(detail.kontribusi_event_khusus || 0) >= 20 ? 'Event khusus cukup membantu penjualan, tetapi kontribusi non-event masih dominan. Ini bagus karena penjualan tidak hanya bergantung pada promo.' : 'Penjualan bulan ini lebih banyak datang dari hari biasa dibanding event khusus. Bisa dipertimbangkan evaluasi promo event agar dampaknya lebih terasa.')}</p></div>
        `;
    }
}

// ==================== PENJUALAN PER BULAN CHART ====================
function loadBulanChart() {
    if (!periodeSelect || !bulanCanvas) return;
    const periode = periodeSelect.value;

    fetch(`/dashboard/penjualan-periode?periode=${encodeURIComponent(periode)}`)
        .then(res => res.json())
        .then(data => {
            if (bulanChart) bulanChart.destroy();
            penjualanBulanDetails = data.details || [];
            bulanChart = new Chart(bulanCanvas, {
                type: 'bar',
                data: { labels: data.labels || [], datasets: [{ label: 'Total Penjualan', data: data.values || [], backgroundColor: '#8B5CF6', borderRadius: 8, maxBarThickness: 40 }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (evt, elements) => { if (elements.length) renderBulanDetail(penjualanBulanDetails[elements[0].index] || null); },
                    plugins: { tooltip: { callbacks: { label: ctx => formatRupiah(ctx.raw), afterBody: items => { const detail = penjualanBulanDetails[items?.[0]?.dataIndex]; return detail ? [`Hari transaksi: ${detail.jumlah_hari_transaksi || 0}`, `Event khusus: ${detail.event_khusus?.length || 0}`, `Kontribusi event: ${formatPersen(detail.kontribusi_event_khusus || 0)}`] : ''; } } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: value => formatCompactRupiah(value) } } }
                }
            });
            if (chartInfo) chartInfo.innerHTML = `Menampilkan <strong>${data.total_data ?? 0}</strong> periode data. Klik batang chart untuk melihat detail event bulanan.`;
            renderBulanDetail(penjualanBulanDetails.length ? penjualanBulanDetails[penjualanBulanDetails.length - 1] : null);
        })
        .catch(err => { console.error(err); if (chartInfo) chartInfo.innerHTML = 'Gagal memuat data chart'; renderBulanDetail(null); });
}

// ==================== PREDIKSI VS AKTUAL CHART ====================
function loadPrediksiChart() {
    if (!bulanFilter || !prediksiContainer) return;
    
    const bulan = bulanFilter.value;
    
    prediksiContainer.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div><p class="mt-4 text-gray-500">Memuat data prediksi...</p></div></div>`;

    fetch(`/dashboard/prediksi-data?bulan=${encodeURIComponent(bulan)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.has_data || data.jumlah_label === 0) {
                prediksiContainer.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center py-8"><div class="text-6xl mb-4"></div><h4 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Prediksi</h4><p class="text-gray-500 mb-4">Untuk bulan <strong class="text-indigo-600">${formatBulanNama(bulan)}</strong> belum tersedia data prediksi.</p><p class="text-sm text-gray-400">Silakan pilih bulan lain yang memiliki data prediksi, atau<br>tambahkan data prediksi terlebih dahulu melalui halaman Prediksi.</p><div class="mt-6"><a href="{{ route('prediksi') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"><span></span> Kelola Data Prediksi</a></div></div></div>`;
                if (prediksiChart) { prediksiChart.destroy(); prediksiChart = null; }
                return;
            }

            prediksiContainer.innerHTML = '<canvas id="prediksiCanvas"></canvas>';
            const canvas = document.getElementById('prediksiCanvas');
            if (!canvas) return;
            if (prediksiChart) prediksiChart.destroy();
            
            prediksiChart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { type: 'bar', label: 'Aktual', data: data.aktual, backgroundColor: 'rgba(16, 185, 129, 0.7)', borderColor: '#059669', borderWidth: 1, borderRadius: 6, order: 2 },
                        { type: 'line', label: 'Prediksi', data: data.prediksi, borderColor: '#3B82F6', backgroundColor: 'rgba(59, 130, 246, 0.1)', borderWidth: 3, fill: true, tension: 0.3, pointRadius: 4, pointBackgroundColor: '#3B82F6', pointBorderColor: '#fff', pointBorderWidth: 2, order: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.raw === null || ctx.raw === undefined ? `${ctx.dataset.label}: -` : `${ctx.dataset.label}: ${formatRupiah(ctx.raw)}`,
                                afterBody: items => {
                                    const aktualItem = items.find(i => i.dataset.label === 'Aktual');
                                    const prediksiItem = items.find(i => i.dataset.label === 'Prediksi');
                                    if (aktualItem && prediksiItem && aktualItem.raw !== null) {
                                        const selisih = aktualItem.raw - prediksiItem.raw;
                                        const sign = selisih >= 0 ? '+' : '-';
                                        return `Selisih: ${sign}${formatRupiah(Math.abs(selisih))}`;
                                    }
                                    return null;
                                }
                            }
                        },
                        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10 } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: value => formatCompactRupiah(value) }, title: { display: true, text: 'Nominal (Rp)' } },
                        x: { title: { display: true, text: 'Tanggal' } }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            prediksiContainer.innerHTML = `<div class="flex items-center justify-center h-full"><div class="text-center py-8"><div class="text-6xl mb-4">⚠️</div><h4 class="text-lg font-semibold text-gray-700 mb-2">Gagal Memuat Data</h4><p class="text-gray-500">Terjadi kesalahan: ${error.message}</p><button onclick="loadPrediksiChart()" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded-lg">Coba Lagi</button></div></div>`;
        });
}

// ==================== LIVE TRACKING ====================
function setTrackingLoading() {
    if (!isOwner) return;
    const tbody = document.getElementById('trackingTableBody');
    if (tbody) tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div><p class="mt-2">Memuat data tracking...</p></td></tr>`;
}

function updateTrackingTable(data) {
    if (!isOwner) return;
    const tbody = document.getElementById('trackingTableBody');
    if (!tbody) return;
    if (!data || !data.length) { tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500">📭 Belum ada data prediksi</td></tr>`; return; }
    tbody.innerHTML = data.map(item => `<tr class="border-b ${item.is_weekend ? 'bg-gray-50' : ''}"><td class="px-4 py-3">${item.tanggal}</td><td class="px-4 py-3 text-right text-blue-600 font-semibold">${formatRupiah(item.target)}</td><td class="px-4 py-3 text-right ${item.realisasi !== null ? 'text-green-600' : 'text-gray-400'}">${item.realisasi !== null ? formatRupiah(item.realisasi) : '-'}</td><td class="px-4 py-3 text-right">${item.realisasi !== null ? formatPersen(item.pencapaian) : '-'}</td><td class="px-4 py-3 text-center"><span class="status-badge ${item.status_badge_class}">${item.status_icon} ${item.status}</span></td><td class="px-4 py-3 text-center">${item.id ? `<button onclick="openUpdateModal(${item.id}, '${item.tanggal}', ${item.target}, ${item.realisasi ?? ''})" class="text-indigo-600 hover:text-indigo-800">✏️ Update</button>` : '<span class="text-gray-400">-</span>'}</td></tr>`).join('');
}

function updateTrackingSummary(summary) {
    if (!isOwner) return;
    document.getElementById('totalTarget').innerHTML = formatRupiah(summary.total_target || 0);
    document.getElementById('totalRealisasi').innerHTML = formatRupiah(summary.total_realisasi || 0);
    document.getElementById('totalPencapaian').innerHTML = formatPersen(summary.total_pencapaian || 0);
    document.getElementById('kekurangan').innerHTML = formatRupiah(summary.kekurangan || 0);
    document.getElementById('targetHarian').innerHTML = formatRupiah(summary.target_harian || 0);
    document.getElementById('rataHarian').innerHTML = formatRupiah(summary.rata_harian || 0);
    document.getElementById('sisaTarget').innerHTML = formatRupiah(summary.sisa_target || 0);
    const progressBar = document.getElementById('progressBar');
    const progressPersen = document.getElementById('progressPersen');
    if (progressBar && progressPersen) {
        const safeValue = Number(summary.total_pencapaian || 0);
        progressBar.style.width = Math.min(safeValue, 100) + '%';
        progressPersen.innerHTML = formatPersen(safeValue);
    }
}

function refreshTracking() {
    if (!isOwner) return;
    const tanggalMulai = document.getElementById('tanggalMulai')?.value || '';
    const tanggalSelesai = document.getElementById('tanggalSelesai')?.value || '';
    const bulan = document.getElementById('trackingBulan')?.value || '';
    const params = new URLSearchParams();
    if (tanggalMulai && tanggalSelesai) { params.append('tanggal_mulai', tanggalMulai); params.append('tanggal_selesai', tanggalSelesai); }
    else if (bulan) params.append('bulan', bulan);
    const url = params.toString() ? `/prediksi/live-tracking?${params.toString()}` : `/prediksi/live-tracking`;
    setTrackingLoading();
    fetch(url).then(res => { if (!res.ok) throw new Error('Akses ditolak'); return res.json(); }).then(res => { if (res.success) { updateTrackingTable(res.data || []); updateTrackingSummary(res.summary || {}); } else console.error(res.message); }).catch(err => console.error(err));
}

function openUpdateModal(id, tanggal, target, realisasi) {
    if (!isOwner) return;
    document.getElementById('prediksiId').value = id;
    document.getElementById('modalTanggal').innerText = tanggal;
    document.getElementById('modalTarget').innerHTML = formatRupiah(target);
    document.getElementById('realisasiInput').value = realisasi ?? '';
    document.getElementById('updateModal').classList.remove('hidden');
    document.getElementById('updateModal').classList.add('flex');
}

function closeModal() {
    if (!isOwner) return;
    const modal = document.getElementById('updateModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

function resetDateFilter() {
    if (!isOwner) return;
    const tanggalMulai = document.getElementById('tanggalMulai');
    const tanggalSelesai = document.getElementById('tanggalSelesai');
    const trackingBulan = document.getElementById('trackingBulan');
    if (tanggalMulai) tanggalMulai.value = '';
    if (tanggalSelesai) tanggalSelesai.value = '';
    if (trackingBulan) trackingBulan.value = '{{ $currentMonth }}';
    refreshTracking();
}

function startAutoRefresh() {
    if (!isOwner) return;
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshEnabled = true;
    autoRefreshInterval = setInterval(() => { if (autoRefreshEnabled && document.visibilityState === 'visible') { refreshTracking(); loadPrediksiChart(); } }, 300000);
}

function setupAutoRefresh() {
    if (!isOwner) return;
    const toggleBtn = document.getElementById('toggleAutoRefresh');
    if (!toggleBtn) return;
    startAutoRefresh();
    toggleBtn.onclick = function() {
        if (autoRefreshEnabled) {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshEnabled = false;
            this.innerHTML = 'Auto-refresh OFF';
            this.classList.remove('active', 'bg-green-600');
            this.classList.add('bg-gray-500');
        } else {
            startAutoRefresh();
            this.innerHTML = 'Auto-refresh ON';
            this.classList.remove('bg-gray-500');
            this.classList.add('active', 'bg-green-600');
        }
    };
}

// ==================== EVENT LISTENERS & INIT ====================
if (periodeSelect && bulanCanvas) {
    periodeSelect.addEventListener('change', loadBulanChart);
    loadBulanChart();
}

if (bulanFilter && prediksiContainer) {
    bulanFilter.addEventListener('change', loadPrediksiChart);
    loadPrediksiChart();
}

document.getElementById('updateRealisasiForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!isOwner) return;
    const id = document.getElementById('prediksiId').value;
    const realisasi = document.getElementById('realisasiInput').value;
    if (!realisasi || Number(realisasi) < 0) { alert('Masukkan nominal realisasi yang valid'); return; }
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Menyimpan...';
    submitBtn.disabled = true;
    try {
        const response = await fetch(`/prediksi/${id}/update-realisasi`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }, body: JSON.stringify({ realisasi: parseFloat(realisasi) }) });
        const result = await response.json();
        if (result.success) { alert('Realisasi berhasil diupdate'); closeModal(); refreshTracking(); loadPrediksiChart(); loadBulanChart(); }
        else alert('Gagal update: ' + (result.message || 'Terjadi kesalahan'));
    } catch (error) { console.error(error); alert('Terjadi kesalahan saat menyimpan data'); }
    finally { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
});

document.addEventListener('DOMContentLoaded', function() {
    if (isOwner) {
        const trackingBulan = document.getElementById('trackingBulan');
        const tanggalMulai = document.getElementById('tanggalMulai');
        const tanggalSelesai = document.getElementById('tanggalSelesai');
        if (trackingBulan) trackingBulan.addEventListener('change', function() { if (tanggalMulai) tanggalMulai.value = ''; if (tanggalSelesai) tanggalSelesai.value = ''; refreshTracking(); });
        if (tanggalMulai) tanggalMulai.addEventListener('change', refreshTracking);
        if (tanggalSelesai) tanggalSelesai.addEventListener('change', refreshTracking);
        refreshTracking();
        setupAutoRefresh();
    }
});
</script>
@endsection