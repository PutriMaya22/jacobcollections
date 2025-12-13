@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-6">Edit Data Penjualan</h2>
    
    <form action="{{ route('data_penjualan.update', $penjualan->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Tanggal -->
        <div class="mb-4">
            <label for="tanggal" class="block text-gray-700 mb-2">
                Tanggal Penjualan
            </label>
            <input 
                type="date" 
                name="tanggal" 
                id="tanggal"
                value="{{ old('tanggal', $penjualan->tanggal) }}" 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            >
        </div>

        <!-- Total Penjualan -->
        <div class="mb-4">
            <label for="total_penjualan" class="block text-gray-700 mb-2">
                Total Penjualan
            </label>
            <input 
                type="text" 
                name="total_penjualan" 
                id="total_penjualan"
                value="{{ old('total_penjualan', $penjualan->total_penjualan) }}" 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Contoh: Rp 5.000.000"
                required
            >
        </div>

        <!-- Total Pesanan -->
        <div class="mb-4">
            <label for="total_pesanan" class="block text-gray-700 mb-2">
                Total Pesanan
            </label>
            <input 
                type="number" 
                name="total_pesanan" 
                id="total_pesanan"
                value="{{ old('total_pesanan', $penjualan->total_pesanan) }}" 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                min="0"
                required
            >
        </div>

        <!-- Penjualan per Pesanan -->
        <div class="mb-4">
            <label for="penjualan_perpesanan" class="block text-gray-700 mb-2">
                Penjualan per Pesanan
            </label>
            <input 
                type="text" 
                name="penjualan_perpesanan" 
                id="penjualan_perpesanan"
                value="{{ old('penjualan_perpesanan', $penjualan->penjualan_perpesanan) }}" 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Contoh: Rp 200.000 / pesanan"
            >
        </div>
        
        <!-- Tombol -->
        <div class="flex justify-end mt-6 space-x-3">
            <a href="{{ route('data_penjualan.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                Batal
            </a>
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Update
            </button>
        </div>
    </form>
</div>
@endsection
