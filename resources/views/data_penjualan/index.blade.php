@extends('layouts.app')

@section('title', 'Data Penjualan - JacobCollections')

@section('content')

<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .sale-card {
        transition: all 0.2s ease;
    }
    .sale-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>

<div class="bg-gray-50 min-h-screen p-6">
    
    <!-- HEADER -->
    <div class="mb-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Data Penjualan</h1>
                <p class="text-gray-500 mt-1"></p>
            </div>
            
            @if(auth()->user()->role === 'owner')
            <div class="flex gap-3">
                <!-- Tombol Tambah -->
                <a href="{{ route('data_penjualan.create') }}" 
                   class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg shadow-md transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Penjualan
                </a>

                <!-- Tombol Import -->
                <form id="importForm"
                      action="{{ route('data_penjualan.import') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="flex gap-2">
                    @csrf
                    <input type="file" 
                           name="file"
                           id="fileInput"
                           accept=".xlsx,.csv"
                           required
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-2 rounded-lg shadow-md transition duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Import Excel
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== MESSAGES ==================== --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded mb-6">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- STATISTIK CARD -->
    @php
        $totalPenjualan = $penjualan->sum('total_penjualan');
        $totalPesanan = $penjualan->sum('total_pesanan');
        $rataRataPesanan = $totalPesanan > 0 ? $totalPenjualan / $totalPesanan : 0;
        $totalHari = $penjualan->count();
        $penjualanTertinggi = $penjualan->sortByDesc('total_penjualan')->first();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Penjualan</p>
                    <p class="text-2xl font-bold mt-2">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
                </div>
                <div class="text-4xl opacity-75"></div>
            </div>
            <div class="mt-3 text-blue-100 text-xs">
                Dari {{ $totalHari }} hari transaksi
            </div>
        </div>

        <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Rata-rata per Pesanan</p>
                    <p class="text-2xl font-bold mt-2">Rp {{ number_format($rataRataPesanan, 0, ',', '.') }}</p>
                </div>
                <div class="text-4xl opacity-75"></div>
            </div>
            <div class="mt-3 text-purple-100 text-xs">
                Nilai rata-rata setiap pesanan
            </div>
        </div>

        <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Penjualan Tertinggi</p>
                    <p class="text-2xl font-bold mt-2">Rp {{ number_format($penjualanTertinggi?->total_penjualan ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="text-4xl opacity-75"></div>
            </div>
            <div class="mt-3 text-orange-100 text-xs">
                {{ $penjualanTertinggi ? \Carbon\Carbon::parse($penjualanTertinggi->tanggal)->format('d M Y') : '-' }}
            </div>
        </div>
    </div>

    <!-- FILTER SECTION -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('data_penjualan.index') }}" class="space-y-4" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2"> Cari Data</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari: 22 Maret 2023, 2023-03-22, atau 5000000..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter Bulan</label>
                <select name="bulan" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" {{ request('bulan') == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-600">Tampilkan:</label>
                <select name="per_page" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 data</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 data</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 data</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 data</option>
                </select>
                <span class="text-sm text-gray-500">
                    Menampilkan {{ $penjualan->firstItem() ?? 0 }} - {{ $penjualan->lastItem() ?? 0 }} dari {{ $penjualan->total() }} data
                </span>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('data_penjualan.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

    <!-- TABLE PENJUALAN -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Penjualan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Pesanan</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Rata-rata per Pesanan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penjualan as $item)
                    @php
                        $rataPerPesanan = $item->total_pesanan > 0 ? $item->total_penjualan / $item->total_pesanan : 0;
                    @endphp
                    <tr class="sale-card hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $loop->iteration + ($penjualan->currentPage() - 1) * $penjualan->perPage() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d F Y') }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('l') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-green-600">
                                Rp {{ number_format($item->total_penjualan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge bg-blue-100 text-blue-700">
                                {{ number_format($item->total_pesanan) }} pesanan
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm text-gray-600">
                                Rp {{ number_format($rataPerPesanan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                @if(auth()->user()->role === 'owner')
                                    <a href="{{ route('data_penjualan.edit', $item->tanggal) }}" 
                                       class="p-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition"
                                       title="Edit">
                                        ✏️
                                    </a>
                                    
                                    <form action="{{ route('data_penjualan.destroy', $item->tanggal) }}" 
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus data penjualan tanggal {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition"
                                                title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-center">
                                    <div class="text-6xl mb-4"></div>
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Penjualan</h3>
                                    <p class="text-gray-500 mb-4">Mulai tambahkan data penjualan untuk memantau performa bisnis</p>
                                    @if(auth()->user()->role === 'owner')
                                    <a href="{{ route('data_penjualan.create') }}" 
                                       class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Tambah Data Pertama
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- PAGINATION LINKS -->
        @if($penjualan->total() > $penjualan->perPage())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $penjualan->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
    

@endsection