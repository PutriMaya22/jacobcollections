@extends('layouts.app')

@section('title', 'Data Ruangan - ASTERA')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Data Ruangan</h2>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('data_ruangan.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Tambah Ruangan
        </a>
        @endif
    </div>

    <form method="GET" action="{{ route('data_ruangan.index') }}" class="mb-4">
        <div class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari Ruangan" 
                class="border rounded px-3 py-2 w-full"
            >
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Cari
            </button>
        </div>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-800">
                    <th class="py-3 px-4 text-left">No</th>
                    <th class="py-3 px-4 text-left">Nama Ruangan</th>
                    <th class="py-3 px-4 text-left">Keterangan</th>
                    <!-- Sembunyikan kolom aksi jika bukan admin -->
                    @if(auth()->user()->role === 'admin')
                    <th class="py-3 px-4 text-left">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($ruangan as $item)
                <tr>
                    <td class="py-3 px-4">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">{{ $item->nama }}</td>
                    <td class="py-3 px-4">{{ $item->keterangan ?? '-' }}</td>
                    <!-- Hanya admin yang bisa melihat tombol aksi -->
                    @if(auth()->user()->role === 'admin')
                    <td class="py-3 px-4 flex space-x-2">
                        <a href="{{ route('data_ruangan.edit', $item->id) }}" class="text-yellow-500 hover:text-yellow-700">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('data_ruangan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection