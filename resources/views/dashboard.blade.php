@extends('layouts.app')

@section('title', 'Dashboard - ASTERA')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card Statistik -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500">Total Barang</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalBarang ?? 0 }}</h3>
                <p class="text-sm text-gray-500 mt-1">Jumlah: {{ $totalQuantity ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-box-open text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-green-500 text-sm font-medium">
                <i class="fas fa-arrow-up"></i> 12% dari bulan lalu
            </span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500">Total Ruangan</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalRuangan ?? 0 }}</h3>
            </div>
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-door-open text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-green-500 text-sm font-medium">
                <i class="fas fa-arrow-up"></i> 5% dari bulan lalu
            </span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500">Barang Baik</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $barangBaik ?? 0 }}</h3>
                <p class="text-sm text-gray-500 mt-1">Jumlah: {{ $barangBaikQuantity ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-full bg-emerald-100 text-emerald-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-green-500 text-sm font-medium">
                {{ number_format(($persenBaik ?? 0), 1) }}% dari total
            </span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500">Barang Rusak</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $barangRusak ?? 0 }}</h3>
                <p class="text-sm text-gray-500 mt-1">Jumlah: {{ $barangRusakQuantity ?? 0 }}</p>
            </div>
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-red-500 text-sm font-medium">
                {{ number_format(($persenRusak ?? 0), 1) }}% dari total
            </span>
        </div>
    </div>
</div>

@if(($totalBarang ?? 0) == 0)
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
    <p class="font-bold">Tidak ada data barang</p>
    <p>Silahkan tambahkan data barang terlebih dahulu untuk melihat statistik.</p>
</div>
@endif

<!-- Grafik Utama -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Distribusi Barang per Ruangan</h3>
            <select class="border rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option>Bulan Ini</option>
                <option>3 Bulan Terakhir</option>
                <option>Tahun Ini</option>
            </select>
        </div>
        <div class="h-80">
            <canvas id="barangPerRuanganChart"></canvas>
        </div>
        <div class="mt-4">
            <h4 class="text-md font-semibold text-gray-700 mb-2">Jumlah Kuantitas per Ruangan</h4>
            <div class="h-80">
                <canvas id="quantityPerRuanganChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Barang</h3>
        <div class="h-80">
            <canvas id="statusBarangChart"></canvas>
        </div>
        <div class="mt-4">
            <h4 class="text-md font-semibold text-gray-700 mb-2">Kuantitas per Status</h4>
            <div class="h-80">
                <canvas id="statusQuantityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabel dan Grafik Tambahan -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="text-left border-b">
                        <th class="pb-3">Barang</th>
                        <th class="pb-3">Ruangan</th>
                        <th class="pb-3">Jumlah</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Kondisi</th>
                        <th class="pb-3">Terakhir Diupdate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentActivities ?? [] as $activity)
                    <tr>
                        <td class="py-3">{{ $activity->nama ?? 'N/A' }}</td>
                        <td class="py-3">{{ $activity->ruangan->nama ?? 'N/A' }}</td>
                        <td class="py-3">{{ $activity->total ?? 0 }}</td>
                        <td class="py-3">
                            @php
                                $status = $activity->status ?? '';
                                $statusClass = '';
                                if ($status == 'tersedia') {
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                } elseif ($status == 'dipinjam') {
                                    $statusClass = 'bg-purple-100 text-purple-800';
                                } else {
                                    $statusClass = 'bg-orange-100 text-orange-800';
                                }
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs {{ $statusClass }}">
                                {{ ucfirst($status ?: 'N/A') }}
                            </span>
                        </td>
                        <td class="py-3">
                            @php
                                $kondisi = $activity->kondisi ?? '';
                                $kondisiClass = '';
                                if ($kondisi == 'baik') {
                                    $kondisiClass = 'bg-green-100 text-green-800';
                                } elseif ($kondisi == 'rusak_ringan') {
                                    $kondisiClass = 'bg-yellow-100 text-yellow-800';
                                } else {
                                    $kondisiClass = 'bg-red-100 text-red-800';
                                }
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs {{ $kondisiClass }}">
                                {{ ucfirst(str_replace('_', ' ', $kondisi ?: 'N/A')) }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($activity->updated_at)
                                {{ $activity->updated_at->diffForHumans() }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-3 text-center text-gray-500">Tidak ada aktivitas terbaru</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Kondisi Barang</h3>
        <div class="h-80">
            <canvas id="kondisiBarangChart"></canvas>
        </div>
        <div class="mt-4">
            <h4 class="text-md font-semibold text-gray-700 mb-2">Kuantitas per Kondisi</h4>
            <div class="h-80">
                <canvas id="kondisiQuantityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Detail -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Status Barang</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Tersedia</span>
                <span class="font-semibold">{{ $statusBarang[0] ?? 0 }} barang</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Dipinjam</span>
                <span class="font-semibold">{{ $statusBarang[1] ?? 0 }} barang</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Perbaikan</span>
                <span class="font-semibold">{{ $statusBarang[2] ?? 0 }} barang</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Kondisi Barang</h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Baik</span>
                <span class="font-semibold">{{ $kondisiBarang[0] ?? 0 }} barang</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Rusak Ringan</span>
                <span class="font-semibold">{{ $kondisiBarang[1] ?? 0 }} barang</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Rusak Berat</span>
                <span class="font-semibold">{{ $kondisiBarang[2] ?? 0 }} barang</span>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart 1: Distribusi Barang per Ruangan (count)
        const ctx1 = document.getElementById('barangPerRuanganChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: {!! json_encode($ruanganLabels ?? []) !!},
                datasets: [{
                    label: 'Jumlah Barang',
                    data: {!! json_encode($barangPerRuangan ?? []) !!},
                    backgroundColor: '#4F46E5',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Chart 2: Quantity per Ruangan
        const ctx2 = document.getElementById('quantityPerRuanganChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! json_encode($ruanganLabels ?? []) !!},
                datasets: [{
                    label: 'Total Kuantitas',
                    data: {!! json_encode($barangQuantityPerRuangan ?? []) !!},
                    backgroundColor: '#10B981',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Chart 3: Status Barang (count)
        const ctx3 = document.getElementById('statusBarangChart').getContext('2d');
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: ['Tersedia', 'Dipinjam', 'Perbaikan'],
                datasets: [{
                    data: {!! json_encode($statusBarang ?? [0,0,0]) !!},
                    backgroundColor: [
                        '#3B82F6',
                        '#8B5CF6',
                        '#F59E0B'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 4: Status Barang (quantity)
        const ctx4 = document.getElementById('statusQuantityChart').getContext('2d');
        new Chart(ctx4, {
            type: 'doughnut',
            data: {
                labels: ['Tersedia', 'Dipinjam', 'Perbaikan'],
                datasets: [{
                    data: {!! json_encode($statusBarangQuantity ?? [0,0,0]) !!},
                    backgroundColor: [
                        '#3B82F6',
                        '#8B5CF6',
                        '#F59E0B'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 5: Kondisi Barang (count)
        const ctx5 = document.getElementById('kondisiBarangChart').getContext('2d');
        new Chart(ctx5, {
            type: 'pie',
            data: {
                labels: ['Baik', 'Rusak Ringan', 'Rusak Berat'],
                datasets: [{
                    data: {!! json_encode($kondisiBarang ?? [0,0,0]) !!},
                    backgroundColor: [
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Chart 6: Kondisi Barang (quantity)
        const ctx6 = document.getElementById('kondisiQuantityChart').getContext('2d');
        new Chart(ctx6, {
            type: 'pie',
            data: {
                labels: ['Baik', 'Rusak Ringan', 'Rusak Berat'],
                datasets: [{
                    data: {!! json_encode($kondisiBarangQuantity ?? [0,0,0]) !!},
                    backgroundColor: [
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endsection