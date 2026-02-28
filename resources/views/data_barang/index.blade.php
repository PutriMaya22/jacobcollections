@extends('layouts.app')

@section('title', 'Data Barang - JacobCollections')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Data Barang</h2>

        @if(auth()->user()->role === 'owner')
        <a href="{{ route('data_barang.create') }}"
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Tambah Barang
        </a>
        @endif
    </div>

    <!-- FILTER -->
    <form method="GET" action="{{ route('data_barang.index') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama / kategori / harga / stok"
                class="border rounded px-3 py-2 w-full"
            >

            <select name="kategori" class="border rounded px-3 py-2 w-full">
                <option value="semua">Semua Kategori</option>
                @php
                    $kategoriList = [
                        'Sedang Diskon' => 'Sedang Diskon',
                        'Deni' => 'Denim',
                        'Celana Panjang' => 'Celana Panjang',
                        'Celana Pendek' => 'Celana Pendek',
                        'Jas Formal' => 'Jas Formal'
                    ];
                @endphp
                @foreach($kategoriList as $key => $value)
                    <option value="{{ $key }}" {{ request('kategori') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 rounded flex-1">
                    Cari
                </button>
                <a href="{{ route('data_barang.index') }}"
                   class="bg-gray-500 text-white px-4 py-2 rounded">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- STATISTIK -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-blue-600 font-semibold">Total Barang</div>
            <div class="text-2xl font-bold text-blue-700">{{ $barang->count() }}</div>
        </div>

        @foreach($kategoriList as $key => $value)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="text-green-600 font-semibold">{{ $value }}</div>
            <div class="text-2xl font-bold text-green-700">
                {{ $barang->where('kategori', $key)->count() }}
            </div>
        </div>
        @endforeach
    </div>

    <!-- TABEL -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-800">
                    <th class="py-3 px-4 text-left">No</th>
                    <th class="py-3 px-4 text-left">ID</th>
                    <th class="py-3 px-4 text-left">Nama Barang</th>
                    <th class="py-3 px-4 text-left">Kategori</th>
                    <th class="py-3 px-4 text-left">Harga</th>
                    <th class="py-3 px-4 text-left">Stok</th>
                    <th class="py-3 px-4 text-left">Tanggal Input</th>

                    @if(auth()->user()->role === 'admin')
                    <th class="py-3 px-4 text-left">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @foreach($barang as $item)
                <tr>
                    <td class="py-3 px-4">{{ $loop->iteration }}</td>

                    <!-- ID -->
                    <td class="py-3 px-4">
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-semibold">
                            {{ $item->id }}
                        </span>
                    </td>

                    <td class="py-3 px-4 font-medium">{{ $item->nama }}</td>

                    <td class="py-3 px-4">
                        <span class="bg-gray-100 px-2 py-1 rounded text-xs">
                            {{ $item->kategori }}
                        </span>
                    </td>

                    <td class="py-3 px-4">{{ $item->harga }}</td>

                    <td class="py-3 px-4">
                        @if($item->stok > 0)
                            <span class="text-green-600 font-semibold">{{ $item->stok }}</span>
                        @else
                            <span class="text-red-500 font-semibold">0</span>
                        @endif
                    </td>

                    <td class="py-3 px-4">
                        {{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}
                    </td>

                    @if(auth()->user()->role === 'admin')
                    <td class="py-3 px-4 flex space-x-2">
                        <a href="{{ route('data_barang.edit', $item->id) }}"
                           class="text-yellow-500 hover:text-yellow-700">
                            ✏️
                        </a>

                        <form action="{{ route('data_barang.destroy', $item->id) }}"
                              method="POST"
                              onsubmit="return confirm('Yakin hapus data?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500 hover:text-red-700">🗑️</button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($barang->isEmpty())
    <div class="text-center py-8 text-gray-500">
        <p>Tidak ada data barang</p>
    </div>
    @endif

</div>
@endsection