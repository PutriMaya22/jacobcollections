@extends('layouts.app')

@section('title', 'Prediksi Penjualan - JacobCollections')

@section('content')
<style>
    .restock-card {
        transition: all 0.3s ease;
    }
    .restock-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .priority-critical {
        background: linear-gradient(135deg, #fee2e2 0%, #ffcccc 100%);
        border-left: 4px solid #dc2626;
    }
    .priority-high {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe6cc 100%);
        border-left: 4px solid #f97316;
    }
    .priority-medium {
        background: linear-gradient(135deg, #fef9e3 0%, #fff3cc 100%);
        border-left: 4px solid #eab308;
    }
    .badge-restock {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-danger { background-color: #fee2e2; color: #dc2626; }
    .status-warning { background-color: #fef3c7; color: #d97706; }
    .status-success { background-color: #d1fae5; color: #059669; }
    .status-info { background-color: #dbeafe; color: #2563eb; }
    
    /* Sembunyikan scrollbar tapi tetap bisa scroll */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="bg-white rounded-lg shadow-sm p-6">
    {{-- JUDUL --}}
    <div class="mb-6 pb-3 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-800">
            Prediksi Penjualan 
        </h2>
        <p class="text-gray-500 text-sm mt-1">Prediksi penjualan berdasarkan historis data</p>
        @isset($metode_analisis)
            <span class="inline-block mt-2 text-xs bg-purple-100 text-purple-700 px-3 py-1 rounded-full">
            </span>
        @endisset
    </div>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
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
                <form action="{{ route('prediksi.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 text-sm">
                                Total Pesanan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="total_pesanan" value="{{ old('total_pesanan') }}" required 
                                   class="border border-gray-300 rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Contoh: 13">
                            <p class="text-xs text-gray-500 mt-1">Jumlah pesanan yang diprediksi</p>
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700 text-sm">
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal" value="{{ old('tanggal') }}" required 
                                   class="border border-gray-300 rounded-lg px-4 py-2.5 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Tanggal prediksi</p>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg transition duration-200 w-full font-semibold shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
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
                        <p class="text-xl font-semibold text-gray-800">Rp {{ number_format($rata_rata, 0, ',', '.') }}</p>
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
                            @if($status == 'Meningkat') text-green-600
                            @elseif($status == 'Menurun') text-red-600
                            @else text-yellow-600 
                            @endif">
                            {{ $status }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-gray-500 text-sm">Strategi Rekomendasi</p>
                        <p class="text-md font-medium text-blue-600">{{ $strategi_umum ?? 'Tidak ada strategi' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endisset

    {{-- REKOMENDASI TERPADU --}}
    @isset($prediksi)
        @php
            $selisih = $prediksi - ($rata_rata ?? 0);
            $persen_selisih = ($rata_rata > 0) ? ($selisih / $rata_rata) * 100 : 0;
            
            if ($selisih > 0 && $persen_selisih > 20) {
                $kategori = 'melonjak';
                $kategori_label = 'Prediksi Penjualan Melonjak Tinggi';
                $kategori_warna = 'bg-red-100 text-red-700';
                $kategori_border = 'border-red-500';
                $kategori_bg = 'from-red-50 to-pink-50';
            } elseif ($selisih > 0) {
                $kategori = 'meningkat';
                $kategori_label = 'Prediksi Penjualan Meningkat';
                $kategori_warna = 'bg-green-100 text-green-700';
                $kategori_border = 'border-green-500';
                $kategori_bg = 'from-green-50 to-teal-50';
            } elseif ($selisih < 0 && abs($persen_selisih) > 20) {
                $kategori = 'turun_drastis';
                $kategori_label = 'Prediksi Penjualan Turun Drastis';
                $kategori_warna = 'bg-red-100 text-red-700';
                $kategori_border = 'border-red-700';
                $kategori_bg = 'from-red-50 to-orange-50';
            } elseif ($selisih < 0) {
                $kategori = 'menurun';
                $kategori_label = 'Prediksi Penjualan Menurun';
                $kategori_warna = 'bg-yellow-100 text-yellow-700';
                $kategori_border = 'border-yellow-500';
                $kategori_bg = 'from-yellow-50 to-orange-50';
            } else {
                $kategori = 'stabil';
                $kategori_label = 'Prediksi Penjualan Stabil';
                $kategori_warna = 'bg-blue-100 text-blue-700';
                $kategori_border = 'border-blue-500';
                $kategori_bg = 'from-blue-50 to-indigo-50';
            }
        @endphp

        <div class="bg-gradient-to-r {{ $kategori_bg }} rounded-xl shadow-lg p-6 mb-8 border-2 border-purple-200">
            <div class="flex items-center gap-3 mb-4 pb-3 flex-wrap border-b border-purple-200">
                <h3 class="text-2xl font-bold text-gray-800">
                    Rekomendasi
                </h3>
                <span class="{{ $kategori_warna }} px-4 py-2 rounded-full text-sm font-semibold">
                    {{ $kategori_label }}
                </span>
                @if($selisih != 0)
                    <span class="text-sm text-gray-500">
                        ({{ $selisih > 0 ? '+' : '' }}{{ number_format($persen_selisih, 1) }}% dari rata-rata)
                    </span>
                @endif
            </div>

            {{-- AKSI YANG HARUS DILAKUKAN --}}
            <div class="bg-white rounded-xl p-5 mb-4 shadow-sm">
                <h4 class="font-bold text-gray-800 mb-3">Aksi yang Harus Dilakukan</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if($kategori == 'melonjak')
                        <div class="bg-red-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-red-700">Strategi Agresif</p>
                            <p class="text-xs text-gray-600 mt-1">Iklan massal & diskon besar</p>
                        </div>
                        <div class="bg-orange-100 rounded-lg p-3 text-center hover:shadow-md transition">
                            <p class="font-semibold text-orange-700"> Stok 2-3x Lipat</p>
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
                            <p class="font-semibold text-purple-700"> Optimasi Bertahap</p>
                            <p class="text-xs text-gray-600 mt-1">A/B testing berkala</p>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- REKOMENDASI UTAMA - 3 PILAR --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
             {{-- 1. PRODUK TERLARIS (TAMPIL 5 SAJA) --}}
@if(isset($produk_terlaris) && $produk_terlaris->count() > 0)
    @php
        $produkTerlarisTop5 = $produk_terlaris->take(5);
    @endphp
    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-5 border-l-4 border-yellow-500 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-3">5 Produk Terlaris</h4>
        <div class="text-xs text-gray-500 mb-2">
            Menampilkan {{ $produkTerlarisTop5->count() }} dari {{ $produk_terlaris->count() }} produk
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-yellow-100 sticky top-0">
                    <tr>
                        <th class="text-left py-2 px-2">#</th>
                        <th class="text-left py-2 px-2">Nama Produk</th>
                        <th class="text-center py-2 px-2">Total Pesanan</th>
                        <th class="text-center py-2 px-2">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produkTerlarisTop5 as $produk)
                        <tr class="border-b border-yellow-200 hover:bg-yellow-50">
                            <td class="py-2 px-2 font-semibold">{{ $loop->iteration }}</td>
                            <td class="py-2 px-2 break-words whitespace-normal max-w-[300px] text-sm">
                                {{ $produk->nama }}
                            </td>
                            <td class="py-2 px-2 text-center text-blue-600 font-semibold text-sm">
                                {{ number_format($produk->total_pesanan ?? 0, 0, ',', '.') }} pesanan
                            </td>
                            <td class="py-2 px-2 text-center 
                                @if(($produk->stok ?? 0) <= 0) text-red-600 font-bold
                                @elseif(($produk->stok ?? 0) <= 10) text-orange-600 font-semibold
                                @else text-gray-600 @endif">
                                {{ number_format($produk->stok ?? 0) }} pcs
                                @if(($produk->stok ?? 0) <= 0) (HABIS) @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-5 border-l-4 border-gray-400 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-3">Semua Produk Terlaris</h4>
        <p class="text-sm text-gray-500">Belum ada data produk</p>
    </div>
@endif


              {{-- 2. PRODUK PALING DIMINATI (SEMUA PRODUK) --}}
@if(isset($produk_paling_diminati) && is_array($produk_paling_diminati) && count($produk_paling_diminati) > 0)
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border-l-4 border-blue-500 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-3"> 5 Produk Paling Diminati</h4>
        <div class="text-xs text-gray-500 mb-2">Total {{ count($produk_paling_diminati) }} produk</div>
        <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
            @foreach($produk_paling_diminati as $index => $produk)
                <div class="border-b border-blue-100 pb-2 last:border-0 hover:bg-blue-50/50 p-2 rounded">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="font-medium text-sm">{{ $loop->iteration }}.</span>
                            <span class="text-sm font-medium">{{ $produk['nama'] ?? '-' }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $produk['rekomendasi'] ?? 'Tidak ada rekomendasi' }}</p>
                    <div class="flex gap-3 mt-1 flex-wrap text-xs">
                        @if(($produk['cr'] ?? 0) > 0)
                            <span class="text-green-600">CR: {{ $produk['cr'] }}%</span>
                        @endif
                        @if(($produk['ctr'] ?? 0) > 0)
                            <span class="text-blue-600">CTR: {{ $produk['ctr'] }}%</span>
                        @endif
                        @if(isset($produk['terjual']) && $produk['terjual'] > 0)
                            <span class="text-orange-600">Terjual: Rp {{ number_format($produk['terjual'], 0, ',', '.') }}</span>
                        @endif
                        @if(isset($produk['stok']) && $produk['stok'] > 0)
                            <span class="text-gray-500">Stok: {{ number_format($produk['stok']) }} pcs</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3 pt-2 border-t border-blue-200">
            <p class="text-xs text-gray-600">Popularity Score = 40% Penjualan + 30% CTR + 30% CR</p>
        </div>
    </div>
@else
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-5 border-l-4 border-gray-400 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-3">Semua Produk Paling Diminati</h4>
        <p class="text-sm text-gray-500">Belum ada data produk diminati</p>
    </div>
@endif

               {{-- 3. PRIORITAS RESTOCK (TAMPIL 5 SAJA) --}}
@if(isset($ringkasan_restock['prioritas_restock']) && is_array($ringkasan_restock['prioritas_restock']) && count($ringkasan_restock['prioritas_restock']) > 0)
    @php
        $prioritasRestockTop5 = collect($ringkasan_restock['prioritas_restock'])->take(5);
    @endphp
    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-5 border-l-4 border-red-500 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-3">5 Prioritas Restock</h4>
        <div class="text-xs text-gray-500 mb-2">
            Menampilkan {{ $prioritasRestockTop5->count() }} dari {{ count($ringkasan_restock['prioritas_restock']) }} produk perlu perhatian
        </div>
        <div class="space-y-3 pr-2">
            @foreach($prioritasRestockTop5 as $restock)
                <div class="border-b border-red-100 pb-2 last:border-0 hover:bg-red-50/50 p-2 rounded">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="font-medium text-sm flex-1">{{ $loop->iteration }}. {{ $restock['nama'] ?? '-' }}</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full 
                            @if(($restock['urgensi'] ?? '') == 'Sangat Tinggi') bg-red-600 text-white
                            @elseif(($restock['urgensi'] ?? '') == 'Tinggi') bg-orange-500 text-white
                            @else bg-yellow-500 text-white 
                            @endif">
                            {{ $restock['urgensi'] ?? 'Normal' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap justify-between text-xs text-gray-600 mt-1 gap-2">
                        <span>Stok: {{ number_format($restock['stok'] ?? 0) }} pcs</span>
                        <span>CR: {{ $restock['cr'] ?? 0 }}%</span>
                        @if(isset($restock['terjual']))
                            <span>Terjual: Rp {{ number_format($restock['terjual'], 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-blue-600 mt-1">
                        Rekomendasi Restock: {{ number_format($restock['rekomendasi_restock'] ?? 0) }} pcs
                    </p>
                    @if(($restock['stok'] ?? 0) <= 0)
                        <p class="text-xs font-bold text-red-600 mt-1">STOK HABIS - Segera Restock!</p>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-3 pt-2 border-t border-red-200">
           
        </div>
    </div>
@else
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-5 border-l-4 border-gray-400 shadow-sm">
        <h4 class="font-bold text-lg text-gray-800 mb-3">Semua Prioritas Restock</h4>
        <p class="text-sm text-gray-500">Belum ada produk yang perlu restock</p>
    </div>
@endif


            {{-- KESIMPULAN --}}
            <div class="bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">💡</div>
                    <div>
                        <h4 class="font-bold text-purple-800 text-lg mb-1">Kesimpulan Strategi</h4>
                        @php
                            $totalProdukTerlaris = isset($produk_terlaris) ? min(5, $produk_terlaris->count()) : 0;
                            $totalProdukDiminati = isset($produk_paling_diminati) ? min(5, count($produk_paling_diminati)) : 0;
                            $totalProdukKritis = isset($ringkasan_restock['prioritas_restock']) ? min(5, count($ringkasan_restock['prioritas_restock'])) : 0;
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
        </div>
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
                    <a href="{{ route('prediksi.export.pdf') }}" class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg transition duration-200 flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
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
                        @foreach($dataPrediksi as $index => $item)
                            @php
                                $tempError = null;
                                $errorSign = '';
                                $errorClass = '';
                                $status = 'Belum Ada Realisasi';
                                $statusClass = 'text-gray-400';
                                
                                if(isset($item->penjualan_aktual) && $item->penjualan_aktual !== null && $item->penjualan_aktual > 0) {
                                    $tempError = $item->penjualan_aktual - $item->hasil_prediksi;
                                    $errorSign = $tempError >= 0 ? '+' : '-';
                                    $errorClass = $tempError >= 0 ? 'text-green-600' : 'text-red-600';
                                    
                                    $pencapaian = $item->hasil_prediksi > 0 ? ($item->penjualan_aktual / $item->hasil_prediksi) * 100 : 0;
                                    if($pencapaian >= 100) {
                                        $status = 'Tercapai';
                                        $statusClass = 'text-green-600';
                                    } elseif($pencapaian >= 80) {
                                        $status = 'Mendekati';
                                        $statusClass = 'text-yellow-600';
                                    } elseif($pencapaian >= 50) {
                                        $status = 'On Progress';
                                        $statusClass = 'text-blue-600';
                                    } else {
                                        $status = 'Perlu Aksi';
                                        $statusClass = 'text-red-600';
                                    }
                                }
                                
                                $staticMape = $item->static_mape;
                                $staticRmse = $item->static_rmse;
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

    {{-- INFO TAMBAHAN --}}
    @if(!isset($prediksi) && !isset($rekomendasi_produk))
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-8 text-center">
            <div class="text-6xl mb-4"></div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Siap Memulai Prediksi?</h3>
            <p class="text-gray-500 max-w-md mx-auto">Masukkan total pesanan dan tanggal untuk mendapatkan prediksi penjualan serta rekomendasi produk terlaris, paling diminati, dan prediksi restock.</p>
            <p class="text-sm text-blue-600 mt-3">Restok akan disesuaikan dengan Conversion Rate (CR) masing-masing produk!</p>
            @auth
                @if(auth()->user()->role === 'owner')
                    <p class="text-sm text-green-600 mt-2">✨ Isi form di atas untuk memulai prediksi</p>
                @else
                    <p class="text-sm text-yellow-600 mt-2">Hanya owner yang dapat melakukan prediksi baru</p>
                @endif
            @endauth
        </div>
    @endif
</div>
@endsection