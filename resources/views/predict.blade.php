@extends('layouts.app')

@section('title', 'Prediksi Penjualan - JacobCollections')

@section('content')
<style>
    .restock-card { transition: all 0.3s ease; }
    .restock-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    .priority-critical { background: linear-gradient(135deg,#fee2e2 0%,#ffcccc 100%); border-left:4px solid #dc2626; }
    .priority-high     { background: linear-gradient(135deg,#fff3e0 0%,#ffe6cc 100%); border-left:4px solid #f97316; }
    .priority-medium   { background: linear-gradient(135deg,#fef9e3 0%,#fff3cc 100%); border-left:4px solid #eab308; }
    .badge-restock     { display:inline-flex; align-items:center; padding:.25rem .75rem; border-radius:9999px; font-size:.75rem; font-weight:600; }
    .status-danger  { background-color:#fee2e2; color:#dc2626; }
    .status-warning { background-color:#fef3c7; color:#d97706; }
    .status-success { background-color:#d1fae5; color:#059669; }
    .status-info    { background-color:#dbeafe; color:#2563eb; }
    .hide-scrollbar::-webkit-scrollbar { display:none; }
    .hide-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }
</style>

<div class="bg-white rounded-lg shadow-sm p-6">

    {{-- JUDUL --}}
    <div class="mb-6 pb-3 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-800">Prediksi Penjualan</h2>
        <p class="text-gray-500 text-sm mt-1">Prediksi penjualan berdasarkan historis data + Rekomendasi Restock Otomatis</p>
    </div>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM PREDIKSI (HANYA OWNER) --}}
    @auth
        @if(auth()->user()->role === 'owner')
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-blue-200">Form Prediksi Penjualan</h3>
                <form action="{{ route('prediksi.store') }}" method="POST" id="predictionForm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 text-sm">
                                Total Pesanan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_pesanan" id="total_pesanan" value="{{ old('total_pesanan') }}" required
                                   class="border border-gray-300 rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Contoh: 13">
                            <p class="text-xs text-gray-500 mt-1">Jumlah pesanan yang diprediksi</p>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 text-sm">
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" required
                                   class="border border-gray-300 rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Tanggal prediksi</p>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" id="submitBtn"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg transition duration-200 w-full font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Prediksi Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    @endauth

    {{-- HASIL PREDIKSI --}}
    @isset($prediksi)
        <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl shadow-md p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-green-200">Hasil Prediksi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-500 text-sm">Tanggal Prediksi</p>
                        <p class="text-xl font-semibold text-gray-800">{{ $tanggal_input }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-500 text-sm">Total Pesanan (Input)</p>
                        <p class="text-xl font-semibold text-blue-600">{{ number_format($total_input, 0, ',', '.') }} pesanan</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-500 text-sm">Rata-rata Historis</p>
                        <p class="text-xl font-semibold text-gray-800">Rp {{ number_format($rata_rata ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 shadow-sm border border-blue-200">
                        <p class="text-gray-600 text-sm">Prediksi Total Penjualan</p>
                        <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($prediksi, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-500 text-sm">Status Penjualan</p>
                        <p class="text-xl font-semibold
                            @if(($status ?? '') == 'Meningkat') text-green-600
                            @elseif(($status ?? '') == 'Menurun') text-red-600
                            @else text-yellow-600 @endif">
                            {{ $status ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endisset

    {{-- REKOMENDASI TERPADU --}}
    @isset($prediksi)
        @php
            $rata_rata_val   = $rata_rata ?? 0;
            $selisih         = $prediksi - $rata_rata_val;
            $persen_selisih  = ($rata_rata_val > 0) ? ($selisih / $rata_rata_val) * 100 : 0;

            if ($selisih > 0 && $persen_selisih > 20) {
                $kategori        = 'melonjak';
                $kategori_label  = 'Prediksi Penjualan Melonjak Tinggi';
                $kategori_warna  = 'bg-red-100 text-red-700';
                $kategori_bg     = 'from-red-50 to-pink-50';
            } elseif ($selisih > 0) {
                $kategori        = 'meningkat';
                $kategori_label  = 'Prediksi Penjualan Meningkat';
                $kategori_warna  = 'bg-green-100 text-green-700';
                $kategori_bg     = 'from-green-50 to-teal-50';
            } elseif ($selisih < 0 && abs($persen_selisih) > 20) {
                $kategori        = 'turun_drastis';
                $kategori_label  = 'Prediksi Penjualan Turun Drastis';
                $kategori_warna  = 'bg-red-100 text-red-700';
                $kategori_bg     = 'from-red-50 to-orange-50';
            } elseif ($selisih < 0) {
                $kategori        = 'menurun';
                $kategori_label  = 'Prediksi Penjualan Menurun';
                $kategori_warna  = 'bg-yellow-100 text-yellow-700';
                $kategori_bg     = 'from-yellow-50 to-orange-50';
            } else {
                $kategori        = 'stabil';
                $kategori_label  = 'Prediksi Penjualan Stabil';
                $kategori_warna  = 'bg-blue-100 text-blue-700';
                $kategori_bg     = 'from-blue-50 to-indigo-50';
            }
        @endphp

        <div class="bg-gradient-to-r {{ $kategori_bg }} rounded-xl shadow-lg p-6 mb-8 border-2 border-purple-200">
            <div class="flex items-center gap-3 mb-4 pb-3 flex-wrap border-b border-purple-200">
                <h3 class="text-2xl font-bold text-gray-800">Rekomendasi</h3>
                <span class="{{ $kategori_warna }} px-4 py-2 rounded-full text-sm font-semibold">{{ $kategori_label }}</span>
                @if($selisih != 0)
                    <span class="text-sm text-gray-500">
                        ({{ $selisih > 0 ? '+' : '' }}{{ number_format($persen_selisih, 1) }}% dari rata-rata)
                    </span>
                @endif
            </div>

            {{-- AKSI --}}
            <div class="bg-white rounded-xl p-5 mb-4 shadow-sm">
                <h4 class="font-bold text-gray-800 mb-3">Aksi yang Harus Dilakukan</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($kategori == 'melonjak')
                        <div class="bg-red-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-red-700">Strategi Agresif</p>
                            <p class="text-xs text-gray-600 mt-1">Iklan massal & diskon besar</p>
                        </div>
                        <div class="bg-orange-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-orange-700">Stok 2-3x Lipat</p>
                            <p class="text-xs text-gray-600 mt-1">Produk CR >10%</p>
                        </div>
                        <div class="bg-purple-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-purple-700">Flash Sale</p>
                            <p class="text-xs text-gray-600 mt-1">Waktu terbatas</p>
                        </div>
                    @elseif($kategori == 'meningkat')
                        <div class="bg-green-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-green-700">Optimasi Iklan</p>
                            <p class="text-xs text-gray-600 mt-1">Targeting lebih tepat</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-blue-700">Tingkatkan Stok</p>
                            <p class="text-xs text-gray-600 mt-1">Produk terlaris</p>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-yellow-700">Bundling Produk</p>
                            <p class="text-xs text-gray-600 mt-1">Naikkan nilai transaksi</p>
                        </div>
                    @elseif($kategori == 'turun_drastis')
                        <div class="bg-red-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-red-700">Evaluasi Total</p>
                            <p class="text-xs text-gray-600 mt-1">Review seluruh strategi</p>
                        </div>
                        <div class="bg-orange-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-orange-700">Promo Besar</p>
                            <p class="text-xs text-gray-600 mt-1">Diskon 20-30%</p>
                        </div>
                        <div class="bg-purple-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-purple-700">Survei Pelanggan</p>
                            <p class="text-xs text-gray-600 mt-1">Cari penyebab penurunan</p>
                        </div>
                    @elseif($kategori == 'menurun')
                        <div class="bg-yellow-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-yellow-700">Promo Menarik</p>
                            <p class="text-xs text-gray-600 mt-1">Diskon & cashback</p>
                        </div>
                        <div class="bg-orange-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-orange-700">Restock Kritis</p>
                            <p class="text-xs text-gray-600 mt-1">Prioritas CR tinggi</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-blue-700">Optimasi Konversi</p>
                            <p class="text-xs text-gray-600 mt-1">Foto & deskripsi</p>
                        </div>
                    @else
                        <div class="bg-blue-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-blue-700">Monitor Berkala</p>
                            <p class="text-xs text-gray-600 mt-1">Pantau performa rutin</p>
                        </div>
                        <div class="bg-green-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-green-700">Pertahankan Strategi</p>
                            <p class="text-xs text-gray-600 mt-1">Strategi yang berjalan</p>
                        </div>
                        <div class="bg-purple-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-purple-700">Optimasi Bertahap</p>
                            <p class="text-xs text-gray-600 mt-1">A/B testing berkala</p>
                        </div>
                    @endif
                </div>
            </div>

           {{-- 3 KOLOM: Produk Terlaris | Produk Diminati | Restock --}}
@php
    $produkTerlarisList = collect($produk_terlaris ?? []);
    $produkDiminatiList = $produk_paling_diminati ?? [];

    // AMBIL DATA RESTOCK DARI API VIA CONTROLLER
    $rawRestock = $rekomendasi_produk ?? [];
    if ($rawRestock instanceof \Illuminate\Support\Collection) {
        $produkRestockList = $rawRestock;
    } else {
        $produkRestockList = collect((array) $rawRestock);
    }

    // Normalisasi setiap item
    $produkRestockList = $produkRestockList->map(function ($item) {
        $item = is_object($item) ? (array) $item : (array) $item;
        return [
            'kode_produk'        => $item['kode_produk'] ?? '-',
            'nama_produk'        => $item['nama_produk'] ?? '-',
            'stok'               => (int) ($item['stok'] ?? 0),
            'total_terjual'      => (int) ($item['total_terjual'] ?? 0),
            'jumlah_restock'     => (int) ($item['jumlah_restock'] ?? 0),
            'rekomendasi_sistem' => $item['rekomendasi_sistem'] ?? '-',
            'prioritas'          => (int) ($item['prioritas'] ?? 4),
            'tren_penjualan'     => $item['tren_penjualan'] ?? 'stabil',
            'persen_tren'        => (float) ($item['persen_tren'] ?? 0),
            'hari_terakhir'      => $item['hari_terakhir'] ?? 999,
            'terakhir_jual'      => $item['terakhir_jual'] ?? '-',
        ];
    })->values();

    $daruratCount = $produkRestockList->where('prioritas', 1)->count();
    $besarCount   = $produkRestockList->where('prioritas', 2)->count();
    $totalRestock = $produkRestockList->sum('jumlah_restock');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- ==================== 1. PRODUK TERLARIS ==================== --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border-l-4 border-blue-500 shadow-sm">
        <div class="mb-4">
            <h4 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                <span></span> Produk Terlaris
            </h4>
            @if($produkTerlarisList->count() > 0)
                <p class="text-xs text-gray-500 mt-1">Total {{ $produkTerlarisList->count() }} produk</p>
            @endif
        </div>

        @if($produkTerlarisList->count() > 0)
            <div class="overflow-y-auto pr-2 space-y-3 max-h-[500px]">
                @foreach($produkTerlarisList as $produk)
                    <div class="bg-white/80 rounded-lg p-3 hover:shadow-md transition">
                        <div class="flex items-start gap-3">
                            <span class="shrink-0 text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full min-w-[28px] text-center">
                                {{ $loop->iteration }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 text-sm line-clamp-2">
                                    {{ $produk->nama ?? $produk['nama'] ?? '-' }}
                                </div>
                                <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                    <span class="text-blue-600 font-medium">
                                         {{ number_format($produk->total_pesanan ?? 0, 0, ',', '.') }} pesanan
                                    </span>
                                    <span class="text-gray-500">
                                        Stok: 
                                        <span class="font-semibold
                                            @if(($produk->stok ?? 0) <= 0) text-red-600
                                            @elseif(($produk->stok ?? 0) <= 10) text-orange-600
                                            @else text-gray-700 @endif">
                                            {{ number_format($produk->stok ?? 0) }} pcs
                                            @if(($produk->stok ?? 0) <= 0) (HABIS) @endif
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-[400px]">
                <p class="text-sm text-gray-500 text-center">Belum ada data produk terlaris</p>
            </div>
        @endif
    </div>

    {{-- ==================== 2. PRODUK PALING DIMINATI ==================== --}}
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-5 border-l-4 border-purple-500 shadow-sm">
        <div class="mb-4">
            <h4 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                <span></span> 10 Produk Paling Diminati
            </h4>
            @if(count($produkDiminatiList) > 0)
                <p class="text-xs text-gray-500 mt-1">Total {{ count($produkDiminatiList) }} produk</p>
            @endif
        </div>

        @if(count($produkDiminatiList) > 0)
            <div class="overflow-y-auto rounded-lg max-h-[500px]">
                <table class="min-w-full text-sm">
                    <thead class="bg-purple-100 sticky top-0 z-10">
                        <tr>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700 w-12">#</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Nama Produk</th>
                            <th class="text-center py-3 px-2 font-semibold text-gray-700 w-16">CTR</th>
                            <th class="text-center py-3 px-2 font-semibold text-gray-700 w-16">CR</th>
                            <th class="text-center py-3 px-2 font-semibold text-gray-700 w-20">Stok</th>
                            <th class="text-center py-3 px-2 font-semibold text-gray-700 w-16">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produkDiminatiList as $produk)
                            @php
                                $ctr   = $produk['ctr'] ?? $produk['persentase_klik'] ?? 0;
                                $cr    = $produk['cr'] ?? $produk['tingkat_konversi'] ?? 0;
                                $stok  = $produk['stok'] ?? 0;
                                $nama  = $produk['nama'] ?? '-';
                                $rekomendasi = $produk['rekomendasi'] ?? '-';
                                $score = $produk['popularity_score'] ?? 0;
                                $scoreColor = $score >= 80 ? 'bg-green-100 text-green-700' 
                                            : ($score >= 50 ? 'bg-yellow-100 text-yellow-700' 
                                            : 'bg-purple-100 text-purple-700');
                                $crDisplay = min(100, $cr);
                            @endphp
                            <tr class="border-b border-purple-100 hover:bg-purple-50 transition">
                                <td class="py-3 px-2 font-semibold text-purple-600 text-center">{{ $loop->iteration }}</td>
                                <td class="py-3 px-2 min-w-[180px]">
                                    <div class="font-semibold text-gray-800 text-sm line-clamp-2">{{ $nama }}</div>
                                    <div class="text-xs text-gray-400 mt-1 line-clamp-1">{{ $rekomendasi }}</div>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    @if($ctr > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-semibold">
                                            {{ number_format($ctr, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center">
                                    @if($cr > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-semibold">
                                            {{ number_format($crDisplay, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="font-semibold text-sm {{ $stok <= 0 ? 'text-red-600' : ($stok <= 10 ? 'text-orange-600' : 'text-gray-700') }}">
                                        {{ number_format($stok) }}
                                        @if($stok <= 0) <span class="text-red-500 text-xs">(0)</span>
                                        @elseif($stok <= 10) <span class="text-orange-500 text-xs">!</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="inline-flex items-center px-2 py-1 {{ $scoreColor }} rounded-full text-xs font-bold">
                                        {{ number_format($score, 0) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 pt-3 border-t border-purple-200">
                <p class="text-xs text-gray-500">Popularity Score = 40% Penjualan + 30% CTR + 30% CR</p>
            </div>
        @else
            <div class="flex items-center justify-center h-[400px]">
                <p class="text-sm text-gray-500 text-center">Belum ada data produk diminati</p>
            </div>
        @endif
    </div>

   {{-- ==================== 3. RESTOCK PRODUK ==================== --}}
<div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-5 border-l-4 border-green-500 shadow-sm">
    <div class="mb-4">
        <h4 class="font-bold text-lg text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Restock Produk
        </h4>
        <p class="text-xs text-gray-500 mt-1"> Berdasarkan data historis 90 hari terakhir</p>
    </div>

    @if($produkRestockList->count() > 0)
        <div class="overflow-y-auto pr-2 space-y-3 max-h-[500px]">
            @foreach($produkRestockList as $index => $produk)
                @php
                    $priorityColor = match ((int) $produk['prioritas']) {
                        1 => 'bg-red-50 border-red-500',
                        2 => 'bg-orange-50 border-orange-500',
                        3 => 'bg-yellow-50 border-yellow-500',
                        4 => 'bg-blue-50 border-blue-500',
                        default => 'bg-white/70 border-gray-300'
                    };

                    $priorityBadge = match ((int) $produk['prioritas']) {
                        1 => '<span class="text-[10px] bg-red-500 text-white px-2 py-0.5 rounded-full">Darurat</span>',
                        2 => '<span class="text-[10px] bg-orange-500 text-white px-2 py-0.5 rounded-full">Besar</span>',
                        3 => '<span class="text-[10px] bg-yellow-500 text-white px-2 py-0.5 rounded-full">Normal</span>',
                        4 => '<span class="text-[10px] bg-blue-500 text-white px-2 py-0.5 rounded-full">Rendah</span>',
                        default => '<span class="text-[10px] bg-gray-400 text-white px-2 py-0.5 rounded-full">Info</span>'
                    };

                    $rekomendasiUpper = strtoupper((string) ($produk['rekomendasi_sistem'] ?? '-'));
                    $restockClass = match (true) {
                        str_contains($rekomendasiUpper, 'DARURAT') => 'bg-red-100 text-red-700 border-red-200',
                        str_contains($rekomendasiUpper, 'BESAR')   => 'bg-orange-100 text-orange-700 border-orange-200',
                        str_contains($rekomendasiUpper, 'NORMAL')  => 'bg-green-100 text-green-700 border-green-200',
                        str_contains($rekomendasiUpper, 'CUKUP')   => 'bg-gray-100 text-gray-700 border-gray-200',
                        default => 'bg-yellow-100 text-yellow-700 border-yellow-200'
                    };

                    $trenArah   = strtolower($produk['tren_penjualan'] ?? 'stabil');
                    $persenTren = (float) ($produk['persen_tren'] ?? 0);
                    $persenText = number_format($persenTren, 0, ',', '.');

                    $trenText = match ($trenArah) {
                        'naik'  => "📈 Naik {$persenText}%",
                        'turun' => "📉 Turun {$persenText}%",
                        default => "➡️ Stabil",
                    };
                @endphp

                <div class="rounded-xl p-4 hover:shadow-md transition-all border-l-4 {{ $priorityColor }} bg-white/50">
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 text-xs font-bold text-green-700 bg-green-100 px-2 py-1 rounded-full min-w-[32px] text-center">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 flex-wrap">
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-gray-800 text-sm line-clamp-2">
                                        {{ $produk['nama_produk'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span class="font-mono">#{{ $produk['kode_produk'] }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0">{!! $priorityBadge !!}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                                <div class="flex items-center gap-2 bg-white/80 rounded-lg px-3 py-2">
                                    <span></span>
                                    <span>Terjual: <strong class="text-blue-600">{{ number_format($produk['total_terjual'], 0, ',', '.') }}</strong> pcs</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white/80 rounded-lg px-3 py-2">
                                    <span></span>
                                    <span>Restock: <strong class="text-purple-600">{{ number_format($produk['jumlah_restock'], 0, ',', '.') }}</strong> pcs</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white/80 rounded-lg px-3 py-2">
                                    <span></span>
                                    <span>Stok: <strong class="{{ $produk['stok'] <= 10 ? 'text-red-600' : 'text-gray-700' }}">{{ number_format($produk['stok'], 0, ',', '.') }}</strong> pcs</span>
                                </div>
                                <div class="flex items-center gap-2 bg-white/80 rounded-lg px-3 py-2">
                                    <span></span>
                                    <span>Terakhir: <strong>{{ $produk['terakhir_jual'] ?? '-' }}</strong></span>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium border {{ $restockClass }}">
                                        {{ $produk['rekomendasi_sistem'] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="text-gray-500">Tren:</span>
                                    <span class="font-semibold {{ $trenArah == 'naik' ? 'text-green-600' : ($trenArah == 'turun' ? 'text-red-600' : 'text-yellow-600') }}">
                                        {{ $trenText }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 pt-3 border-t border-green-200">
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex gap-4">
                    @if($daruratCount > 0)
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            Darurat: {{ $daruratCount }}
                        </span>
                    @endif
                    @if($besarCount > 0)
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                            Besar: {{ $besarCount }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Total Restock: <strong>{{ number_format($totalRestock, 0, ',', '.') }} pcs</strong>
                    </span>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">
                
            </p>
        </div>
    @else
        <div class="flex flex-col items-center justify-center text-center h-[400px]">
            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada data restock.</p>
            <p class="text-gray-400 text-xs mt-2">
                Pastikan Flask API berjalan di 
                <code class="bg-gray-100 px-1 rounded">http://localhost:5000</code>
            </p>
        </div>
    @endif
</div>

</div>{{-- end grid 3 kolom --}}

            {{-- KESIMPULAN --}}
            <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">💡</div>
                    <div>
                        <h4 class="font-bold text-purple-800 text-lg mb-1">Kesimpulan Strategi</h4>
                        @php
                            $totalProdukTerlaris = $produkTerlarisList->count();
                            $totalProdukDiminati = count($produkDiminatiList);
                            $totalProdukKritis   = $produkRestockList->where('prioritas', 1)->count();
                        @endphp
                        @if($kategori == 'melonjak')
                            <p class="text-sm text-purple-700">
                                Untuk mencapai prediksi <strong>Rp {{ number_format($prediksi, 0, ',', '.') }}</strong> yang <strong>melonjak {{ number_format($persen_selisih, 1) }}%</strong>,
                                diperlukan strategi agresif: fokus pada <strong>{{ $totalProdukTerlaris }} produk terlaris</strong> dengan stok 2-3x lipat,
                                optimasi <strong>{{ $totalProdukDiminati }} produk diminati</strong>, dan restock darurat <strong>{{ $totalProdukKritis }} produk kritis</strong>.
                            </p>
                        @elseif($kategori == 'meningkat')
                            <p class="text-sm text-purple-700">
                                Untuk mencapai prediksi <strong>Rp {{ number_format($prediksi, 0, ',', '.') }}</strong> yang <strong>meningkat {{ number_format($persen_selisih, 1) }}%</strong>,
                                fokus pada <strong>{{ $totalProdukTerlaris }} produk terlaris</strong>, optimasi <strong>{{ $totalProdukDiminati }} produk diminati</strong>,
                                dan segera restock <strong>{{ $totalProdukKritis }} produk kritis</strong>.
                            </p>
                        @elseif($kategori == 'turun_drastis')
                            <p class="text-sm text-purple-700">
                                Prediksi <strong>Rp {{ number_format($prediksi, 0, ',', '.') }}</strong> <strong>turun drastis {{ number_format(abs($persen_selisih), 1) }}%</strong>.
                                Perlu evaluasi menyeluruh, promo besar-besaran, restock produk CR tertinggi, dan survei pelanggan.
                            </p>
                        @elseif($kategori == 'menurun')
                            <p class="text-sm text-purple-700">
                                Untuk mengatasi prediksi <strong>Rp {{ number_format($prediksi, 0, ',', '.') }}</strong> yang <strong>turun {{ number_format(abs($persen_selisih), 1) }}%</strong>,
                                tingkatkan promosi <strong>{{ $totalProdukTerlaris }} produk terlaris</strong>, optimasi konversi <strong>{{ $totalProdukDiminati }} produk diminati</strong>,
                                dan restock segera <strong>{{ $totalProdukKritis }} produk kritis</strong>.
                            </p>
                        @else
                            <p class="text-sm text-purple-700">
                                Untuk mempertahankan prediksi <strong>Rp {{ number_format($prediksi, 0, ',', '.') }}</strong> yang stabil,
                                pertahankan strategi <strong>{{ $totalProdukTerlaris }} produk terlaris</strong>, monitor <strong>{{ $totalProdukDiminati }} produk diminati</strong>,
                                dan pastikan stok <strong>{{ $totalProdukKritis }} produk kritis</strong> tersedia.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- end rekomendasi terpadu --}}
    @endisset

    {{-- RIWAYAT PREDIKSI --}}
    @auth
        @if(isset($dataPrediksi) && $dataPrediksi->count() > 0)
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200 flex-wrap gap-3">
                    <h3 class="text-xl font-semibold text-gray-800">Riwayat Prediksi Penjualan</h3>
                    <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full">Total: {{ $dataPrediksi->count() }} prediksi</span>
                </div>

                @if(auth()->user()->role === 'owner')
                    <div class="flex justify-end mb-4 gap-2">
                        <a href="{{ route('prediksi.export.pdf') }}"
                           class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export PDF Riwayat
                        </a>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-center text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 border font-semibold">No</th>
                                <th class="px-3 py-2 border font-semibold">Tanggal</th>
                                <th class="px-3 py-2 border font-semibold">Pesanan</th>
                                <th class="px-3 py-2 border font-semibold">Prediksi</th>
                                <th class="px-3 py-2 border font-semibold">Aktual</th>
                                <th class="px-3 py-2 border font-semibold">Error</th>
                                <th class="px-3 py-2 border font-semibold">MAPE</th>
                                <th class="px-3 py-2 border font-semibold">RMSE</th>
                                <th class="px-3 py-2 border font-semibold">R²</th>
                                <th class="px-3 py-2 border font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataPrediksi as $item)
                                @php
                                    $tempError   = null;
                                    $errorSign   = '';
                                    $errorClass  = '';
                                    $status      = 'Belum Ada Realisasi';
                                    $statusClass = 'text-gray-400';

                                    if(isset($item->penjualan_aktual) && $item->penjualan_aktual > 0) {
                                        $tempError  = $item->penjualan_aktual - $item->hasil_prediksi;
                                        $errorSign  = $tempError >= 0 ? '+' : '-';
                                        $errorClass = $tempError >= 0 ? 'text-green-600' : 'text-red-600';

                                        $pencapaian = $item->hasil_prediksi > 0
                                            ? ($item->penjualan_aktual / $item->hasil_prediksi) * 100
                                            : 0;

                                        if ($pencapaian >= 100)     { $status = 'Tercapai';   $statusClass = 'text-green-600'; }
                                        elseif ($pencapaian >= 80)  { $status = 'Mendekati';  $statusClass = 'text-yellow-600'; }
                                        elseif ($pencapaian >= 50)  { $status = 'On Progress'; $statusClass = 'text-blue-600'; }
                                        else                        { $status = 'Perlu Aksi'; $statusClass = 'text-red-600'; }
                                    }

                                    $staticMape     = $item->static_mape;
                                    $staticRmse     = $item->static_rmse;
                                    $staticRSquared = $item->static_r_squared;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2 border">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 border">{{ number_format($item->total_pesanan ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 border text-blue-600">Rp {{ number_format($item->hasil_prediksi, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 border">
                                        @if(isset($item->penjualan_aktual) && $item->penjualan_aktual)
                                            <span class="text-green-600">Rp {{ number_format($item->penjualan_aktual, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 border">
                                        @if($tempError !== null)
                                            <span class="{{ $errorClass }}">{{ $errorSign }} Rp {{ number_format(abs($tempError), 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 border">
                                        @if($staticMape)
                                            <span class="font-semibold
                                                @if($staticMape < 10) text-green-600
                                                @elseif($staticMape < 20) text-yellow-600
                                                @else text-red-600 @endif">
                                                {{ number_format($staticMape, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 border">
                                        @if($staticRmse)
                                            <span class="font-semibold
                                                @if($staticRmse < 1000000) text-green-600
                                                @elseif($staticRmse < 5000000) text-yellow-600
                                                @else text-red-600 @endif">
                                                Rp {{ number_format($staticRmse, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 border">
                                        @if($staticRSquared)
                                            <span class="font-semibold
                                                @if($staticRSquared > 0.7) text-green-600
                                                @elseif($staticRSquared > 0.5) text-yellow-600
                                                @else text-red-600 @endif">
                                                {{ number_format($staticRSquared, 4) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 border">
                                        <span class="{{ $statusClass }} font-semibold">{{ $status }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600">
                        <strong>Total Prediksi:</strong> {{ $dataPrediksi->count() }} kali |
                        <strong>Terakhir update:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        <strong>Keterangan:</strong> MAPE (semakin kecil semakin baik), RMSE (semakin kecil semakin baik), R² (semakin mendekati 1 semakin baik)
                    </p>
                </div>
            </div>
        @endif
    @endauth

<script>
    // Loading effect saat submit form
    document.getElementById('predictionForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            `;
        }
    });
</script>
@endsection