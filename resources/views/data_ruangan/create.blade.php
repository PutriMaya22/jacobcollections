<!-- create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-semibold mb-6">Tambah Ruangan</h2>
    
    <form action="{{ route('data_ruangan.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 mb-2">Nama Ruangan</label>
            <input type="text" name="nama" id="nama" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="mb-4">
            <label for="keterangan" class="block text-gray-700 mb-2">Keterangan</label>
            <textarea name="keterangan" id="keterangan" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection

