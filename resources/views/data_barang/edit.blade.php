@extends('layouts.app')

@section('title', 'Edit Data Barang')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl mx-auto">

    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-edit text-blue-500 mr-2"></i> Edit Data Barang
    </h3>

    <form action="{{ route('data_barang.update', $data_barang->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Nama Barang -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Barang <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama', $data_barang->nama) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"
                    required
                >
                @error('nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select
                    name="kategori"
                    class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"
                    required
                >
                    <option value="">Pilih Kategori</option>
                    @foreach($kategoriList as $key => $value)
                        <option value="{{ $key }}"
                            {{ old('kategori', $data_barang->kategori) == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                @error('kategori')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Harga -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="harga"
                    value="{{ old('harga', $data_barang->harga) }}"
                    class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200"
                    placeholder="Contoh: Rp 150.000"
                    required
                >
                @error('harga')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <!-- Tombol -->
        <div class="flex justify-end space-x-3 mt-8">
            <a href="{{ route('data_barang.index') }}"
               class="px-5 py-2 text-sm border rounded-lg text-gray-700 hover:bg-gray-100">
                Batal
            </a>
            <button
                type="submit"
                class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Update Data
            </button>
        </div>

    </form>
</div>
@endsection
