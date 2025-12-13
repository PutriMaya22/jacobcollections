@extends('layouts.app')

@section('title', 'Dashboard - JacobCollections')

@section('content')

<!-- ================= CARD STATISTIK ================= -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Total Barang</p>
        <h3 class="text-2xl font-bold">{{ $totalBarang }}</h3>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Total Kategori</p>
        <h3 class="text-2xl font-bold">{{ $totalKategori }}</h3>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Total Penjualan</p>
        <h3 class="text-2xl font-bold">
            Rp {{ number_format($totalPenjualan, 0, ',', '.') }}
        </h3>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <p class="text-gray-500">Total Pesanan</p>
        <h3 class="text-2xl font-bold">{{ $totalPesanan }}</h3>
    </div>

</div>

<!-- ================= GRAFIK ================= -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Barang per Kategori -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Jumlah Barang per Kategori</h3>
        <div class="h-72">
            <canvas id="barangKategoriChart"></canvas>
        </div>
    </div>

    <!-- Penjualan per Tanggal -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Total Penjualan per Tanggal</h3>
        <div class="h-72">
            <canvas id="penjualanTanggalChart"></canvas>
        </div>
    </div>

</div>

<!-- ================= TABEL PENJUALAN TERBARU ================= -->
<div class="bg-white rounded-xl shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Penjualan Terbaru</h3>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Total Penjualan</th>
                    <th class="pb-2">Total Pesanan</th>
                    <th class="pb-2">Penjualan / Pesanan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentPenjualan as $p)
                <tr>
                    <td class="py-2">{{ date('d M Y', strtotime($p->tanggal)) }}</td>
                    <td class="py-2">
                        Rp {{ number_format($p->total_penjualan, 0, ',', '.') }}
                    </td>
                    <td class="py-2">{{ $p->total_pesanan }}</td>
                    <td class="py-2">{{ $p->penjualan_perpesanan }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-gray-500 py-4">
                        Belum ada data penjualan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================= CHART JS ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Chart Barang per Kategori
    new Chart(document.getElementById('barangKategoriChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($kategoriLabels) !!},
            datasets: [{
                label: 'Jumlah Barang',
                data: {!! json_encode($barangPerKategori) !!},
                backgroundColor: '#4F46E5',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Chart Penjualan per Tanggal
    new Chart(document.getElementById('penjualanTanggalChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($tanggalLabels) !!},
            datasets: [{
                label: 'Total Penjualan',
                data: {!! json_encode($penjualanPerTanggal) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16,185,129,0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

});
</script>

@endsection
