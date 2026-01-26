@extends('layouts.app')

@section('title', 'Prediksi')

@section('content')
<div class="space-y-6">

    {{-- Judul --}}
    <h2 class="text-2xl font-semibold text-gray-800">Prediksi</h2>

    {{-- Card Input Prediksi --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-blue-600 text-white px-4 py-2 font-semibold">
            Input Prediksi
        </div>

        <div class="p-6">
            <form action="{{ route('prediksi.proses') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Prediksi <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal"
                        value="{{ old('tanggal') }}"
                        class="w-full max-w-xs border border-gray-300 rounded-md px-3 py-2 focus:ring focus:ring-blue-200 focus:outline-none"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full"
                >
                    Prediksi
                </button>
            </form>
        </div>
    </div>

    {{-- Card Hasil Prediksi --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="bg-blue-600 text-white px-4 py-2 font-semibold">
            Hasil Prediksi
        </div>

        <div class="p-6 space-y-3 text-sm text-gray-800">
            <p><strong>Tanggal :</strong> 24-05-2027</p>
            <p><strong>Total Penjualan :</strong> Rp.11.000.000</p>

            <p class="mt-4 font-semibold">Dengan syarat harus mencapai :</p>

            <ul class="list-disc list-inside space-y-1">
                <li>Total Pesanan: 150</li>
                <li>Penjualan Per Pesanan: 350</li>
                <li>Produk Dilihat: 800</li>
                <li>Total Pengunjung: 1200</li>
            </ul>
        </div>
    </div>

</div>
@endsection
