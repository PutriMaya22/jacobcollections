@extends('layouts.app')

@section('title', 'Data Penjualan - JacobCollections')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Data Penjualan</h2>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('data_penjualan.create') }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Tambah Penjualan
        </a>
        @endif
    </div>

    <!-- SEARCH -->
    <form method="GET" action="{{ route('data_penjualan.index') }}" class="mb-4">
        <div class="flex gap-2">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari tanggal / total penjualan" 
                class="border rounded px-3 py-2 w-full"
            >
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Cari
            </button>
        </div>
    </form>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-800">
                    <th class="py-3 px-4 text-left">No</th>
                    <th class="py-3 px-4 text-left">Tanggal</th>
                    <th class="py-3 px-4 text-left">Total Penjualan</th>
                    <th class="py-3 px-4 text-left">Total Pesanan</th>
                    <th class="py-3 px-4 text-left">Penjualan / Pesanan</th>
                    @if(auth()->user()->role === 'admin')
                    <th class="py-3 px-4 text-left">Aksi</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($penjualan as $item)
                <tr>
                    <td class="py-3 px-4">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                    </td>
                    <td class="py-3 px-4">{{ $item->total_penjualan }}</td>
                    <td class="py-3 px-4">{{ $item->total_pesanan }}</td>
                    <td class="py-3 px-4">
                        {{ $item->penjualan_perpesanan ?? '-' }}
                    </td>

                    @if(auth()->user()->role === 'admin')
                    <td class="py-3 px-4 flex space-x-2">
                        <a href="{{ route('data_penjualan.edit', $item->id) }}" 
                           class="text-yellow-500 hover:text-yellow-700">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="{{ route('data_penjualan.destroy', $item->id) }}" 
                              method="POST"
                              onsubmit="return confirm('Yakin hapus data penjualan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        Data penjualan belum tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $penjualan->links() }}
    </div>
</div>
@endsection
