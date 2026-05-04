@extends('layouts.app')

@section('title', 'Edit Data Penjualan')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-6 flex items-center">
        <span class="mr-2">✏️</span> Edit Data Penjualan
    </h2>
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- 🔥 PERBAIKAN: Gunakan parameter TANGGAL, bukan ID --}}
    <form action="{{ route('data_penjualan.update', $data_penjualan->tanggal) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Tanggal -->
        <div class="mb-4">
            <label for="tanggal" class="block text-gray-700 mb-2 font-medium">
                Tanggal Penjualan <span class="text-red-500">*</span>
            </label>
            <input 
                type="date" 
                name="tanggal" 
                id="tanggal"
                value="{{ old('tanggal', $data_penjualan->tanggal instanceof \Carbon\Carbon ? $data_penjualan->tanggal->format('Y-m-d') : date('Y-m-d', strtotime($data_penjualan->tanggal))) }}"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tanggal') border-red-500 @enderror"
                required
            >
            @error('tanggal')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Total Penjualan -->
        <div class="mb-4">
            <label for="total_penjualan" class="block text-gray-700 mb-2 font-medium">
                Total Penjualan (Rp) <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                name="total_penjualan" 
                id="total_penjualan"
                value="{{ old('total_penjualan', number_format($data_penjualan->total_penjualan, 0, ',', '.')) }}"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_penjualan') border-red-500 @enderror"
                placeholder="Contoh: 5.000.000"
                required
            >
            @error('total_penjualan')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Total Pesanan -->
        <div class="mb-4">
            <label for="total_pesanan" class="block text-gray-700 mb-2 font-medium">
                Total Pesanan <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                name="total_pesanan" 
                id="total_pesanan"
                value="{{ old('total_pesanan', $data_penjualan->total_pesanan) }}"
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_pesanan') border-red-500 @enderror"
                min="1"
                required
            >
            @error('total_pesanan')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('data_penjualan.index') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition">
                Batal
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Update
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('total_penjualan').addEventListener('input', function(e) {
        let value = this.value.replace(/[^\d]/g, '');
        if (value) {
            this.value = new Intl.NumberFormat('id-ID').format(value);
        }
    });
    
    document.querySelector('form').addEventListener('submit', function(e) {
        let penjualanInput = document.getElementById('total_penjualan');
        penjualanInput.value = penjualanInput.value.replace(/\./g, '');
    });
</script>
@endpush