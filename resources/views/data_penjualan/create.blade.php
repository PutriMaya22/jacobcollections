@extends('layouts.app')

@section('title', 'Tambah Data Penjualan')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-6">📝 Tambah Data Penjualan</h2>
    
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ route('data_penjualan.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal') }}" 
                   class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">Total Penjualan (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="total_penjualan" value="{{ old('total_penjualan') }}" 
                   class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                   placeholder="0" min="0" required>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">Total Pesanan <span class="text-red-500">*</span></label>
            <input type="number" name="total_pesanan" value="{{ old('total_pesanan') }}" 
                   class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" 
                   placeholder="0" min="0" required>
        </div>
        
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('data_penjualan.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Simpan</button>
        </div>
    </form>
</div>
@endsection