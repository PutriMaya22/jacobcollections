@extends('layouts.app')

@section('title', 'Prediksi Penjualan')

@section('content')

<div class="bg-white rounded-lg shadow-sm p-6">

    {{-- JUDUL --}}
    <h2 class="text-2xl font-semibold text-gray-800">
        Prediksi Penjualan Jacobcollections
    </h2>

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM PREDIKSI (HANYA OWNER) --}}
    @auth
        @if(auth()->user()->role === 'owner')
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form action="{{ route('prediksi.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 font-medium">Total Pesanan</label>
                            <input type="number" name="total_pesanan" value="{{ old('total_pesanan') }}" required class="border rounded px-3 py-2 w-full">
                        </div>
                        <div>
                            <label class="block mb-2 font-medium">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ old('tanggal') }}" required class="border rounded px-3 py-2 w-full">
                        </div>
                    </div>
                    <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Prediksi Sekarang</button>
                </form>
            </div>
        @endif
    @endauth

    {{-- HASIL PREDIKSI --}}
    @isset($prediksi)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Hasil Prediksi</h3>
            <div class="space-y-3">
                <p><strong>Tanggal:</strong> {{ $tanggal_input }}</p>
                <p><strong>Total Pesanan:</strong> {{ $total_input }}</p>
                <p class="text-xl">Estimasi Total Penjualan:</p>
                <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($prediksi, 0, ',', '.') }}</p>

                @isset($rata_rata)
                    <p class="mt-4"><strong>Rata-rata Historis:</strong> Rp {{ number_format($rata_rata, 0, ',', '.') }}</p>
                @endisset

                @isset($status)
                    <p class="mt-2 font-semibold">Status: {{ $status }}</p>
                @endisset

                @isset($rekomendasi)
    <div class="mt-3 p-4 bg-blue-50 rounded">
        <strong>Rekomendasi:</strong>
        <p>{{ $rekomendasi }}</p>
    </div>
@endisset

@isset($rekomendasi_kategori_stok)
    <div class="mt-3 p-4 bg-green-50 rounded border border-green-200">
        <strong>Stok yang Direkomendasikan:</strong>
        <p class="mt-1 text-green-700 font-semibold">
            {{ $rekomendasi_kategori_stok }}
        </p>
    </div>
@endisset
            </div> 
    </div> 
@endisset  
    {{-- TABEL RIWAYAT PREDIKSI --}}
    @auth
        @if(isset($dataPrediksi) && $dataPrediksi->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Riwayat Prediksi Penjualan</h3>

                {{-- EXPORT PDF HANYA UNTUK OWNER --}}
                @if(auth()->user()->role === 'owner')
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('prediksi.export.pdf') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Export PDF</a>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-center">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Tanggal</th>
                                <th class="px-4 py-2 border">Hasil Prediksi</th>
                                <th class="px-4 py-2 border">Penjualan Aktual</th>
                                <th>RMSE</th>
                                <th>MAPE</th>
                                <th>R²</th>
                                <th class="px-4 py-2 border">Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataPrediksi as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 border">{{ $item->tanggal }}</td>
                                    <td class="px-4 py-2 border text-blue-600 font-semibold">Rp {{ number_format($item->hasil_prediksi, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 border">
                                        {{ isset($item->penjualan_aktual) ? 'Rp ' . number_format($item->penjualan_aktual, 0, ',', '.') : '-' }}
                                    </td>
                                    <td>
                                        {{ isset($item->rmse) ? number_format($item->rmse, 2) : '-' }}
                                    </td>
                                    <td>
                                        {{ isset($item->mape) ? number_format($item->mape, 2) . '%' : '-' }}
                                    </td>
                                    <td>
                                        {{ isset($item->r_squared) ? number_format($item->r_squared, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-2 border">
                                        {{ isset($item->error) ? 'Rp ' . number_format($item->error, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endauth

</div>
@endsection