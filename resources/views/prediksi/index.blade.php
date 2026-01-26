@extends('layouts.app')

@section('title', 'Prediksi Penjualan')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-semibold">Prediksi Penjualan</h2>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('prediksi.proses') }}" method="POST">
            @csrf
            <label class="block mb-2 font-medium">Tanggal Prediksi</label>
            <input type="date" name="target_date" value="{{ old('target_date', date('Y-m-d')) }}" required class="border rounded px-3 py-2">
            <button class="mt-4 bg-blue-600 text-white px-6 py-2 rounded">Prediksi</button>
        </form>
    </div>

    @isset($result)
    <div class="bg-white rounded-lg shadow p-6">
        <p><b>Tanggal:</b> {{ $result['tanggal'] }}</p>
        <p class="text-lg font-semibold">
            Total Penjualan Prediksi: <span class="text-green-600">Rp {{ number_format($result['total_penjualan'], 0, ',', '.') }}</span>
        </p>
        <hr class="my-4">
        <h4 class="font-semibold">Rekomendasi Target</h4>
        <ul class="list-disc ml-6">
            <li>Total Pesanan: {{ $result['total_pesanan'] }}</li>
            <li>Penjualan per Pesanan: Rp {{ number_format($result['penjualan_per_pesanan'], 0, ',', '.') }}</li>
        </ul>
    </div>
    @endisset
</div>
@endsection
