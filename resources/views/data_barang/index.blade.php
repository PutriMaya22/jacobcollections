@extends('layouts.app')

@section('title', 'Data Barang - ASTERA')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Data Barang</h2>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('data_barang.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Tambah Barang
        </a>
        @endif
    </div>

    <form method="GET" action="{{ route('data_barang.index') }}" class="mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari barang atau nomor seri" 
                    class="border rounded px-3 py-2 w-full"
                >
            </div>
            <div>
                <select name="kategori" class="border rounded px-3 py-2 w-full">
                    <option value="semua">Semua Kategori</option>
                    <optgroup label="Jaringan Komputer">
                        <option value="Router" {{ request('kategori') == 'Router' ? 'selected' : '' }}>Router</option>
                        <option value="Access Point" {{ request('kategori') == 'Access Point' ? 'selected' : '' }}>Access Point</option>
                        <option value="UPS" {{ request('kategori') == 'Network Cable' ? 'selected' : '' }}>Network Cable</option>
                        <option value="Proyektor" {{ request('kategori') == 'Network Tool' ? 'selected' : '' }}>Network Tool</option>
                        <option value="Aplikasi" {{ request('kategori') == 'Aplikasi' ? 'selected' : '' }}>Aplikasi</option>
                        <option value="Server Baremetal Non Virtual" {{ request('kategori') == 'Server Baremetal Non Virtual' ? 'selected' : '' }}>Server Baremetal Non Virtual</option>
                        <option value="Server Fisik Host Virtualisasi" {{ request('kategori') == 'Server Fisik Host Virtualisasi' ? 'selected' : '' }}>Server Fisik Host Virtualisasi</option>
                        <option value="Laptop Pc-LPTPC" {{ request('kategori') == 'Laptop Pc-LPTPC' ? 'selected' : '' }}>Laptop PC-LPTPC</option>
                        <option value="jaringan-NTWRK" {{ request('kategori') == 'jaringan-NTWRK' ? 'selected' : '' }}>Jaringan-NTWRK</option>
                        <option value="Switch" {{ request('kategori') == 'Switch' ? 'selected' : '' }}>Switch</option>
                        <option value="server Storage" {{ request('kategori') == 'server Storage' ? 'selected' : '' }}>Server Storage</option>
                        <option value="Lissence - LICNS" {{ request('kategori') == 'Lissence - LICNS' ? 'selected' : '' }}>Lissence - LICNS</option>
                        <option value="Backup Aplliance" {{ request('kategori') == 'Backup Aplliance' ? 'selected' : '' }}>Backup Aplliance</option>
                        <option value="WLAN Controller" {{ request('kategori') == 'WLAN Controller' ? 'selected' : '' }}>WLAN Controller</option>
                        

                    </optgroup>
                    <optgroup label="Kategori Lainnya">
                        <option value="Elektronik" {{ request('kategori') == 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
                        <option value="Furniture" {{ request('kategori') == 'Furniture' ? 'selected' : '' }}>Furniture</option>
                        <option value="ATK" {{ request('kategori') == 'ATK' ? 'selected' : '' }}>ATK</option>
                    </optgroup>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded flex-1">
                    Cari
                </button>
                <a href="{{ route('data_barang.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Statistik Kategori -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-blue-600 font-semibold">Total Barang</div>
            <div class="text-2xl font-bold text-blue-700">{{ $barang->count() }}</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="text-green-600 font-semibold">Jaringan Komputer</div>
            <div class="text-2xl font-bold text-green-700">
                {{ $barang->whereIn('kategori', ['Router & Switch', 'Access Point', 'Network Cable', 'Network Tool', 'Server'])->count() }}
            </div>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="text-purple-600 font-semibold">Tersedia</div>
            <div class="text-2xl font-bold text-purple-700">
                {{ $barang->where('status', 'tersedia')->count() }}
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="text-orange-600 font-semibold">Perbaikan</div>
            <div class="text-2xl font-bold text-orange-700">
                {{ $barang->where('status', 'perbaikan')->count() }}
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-800">
                    <th class="py-3 px-4 text-left">No</th>
                    <th class="py-3 px-4 text-left">Foto</th>
                    <th class="py-3 px-4 text-left">Nomor Seri</th>
                    <th class="py-3 px-4 text-left">Nama (Merk)</th>
                    <th class="py-3 px-4 text-left">Total</th>
                    <th class="py-3 px-4 text-left">Rack</th>
                    <th class="py-3 px-4 text-left">Kategori</th>
                    <th class="py-3 px-4 text-left">Tgl Input</th>
                    <th class="py-3 px-4 text-left">Kondisi</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    @if(auth()->user()->role === 'admin')
                    <th class="py-3 px-4 text-left">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($barang as $item)
                <tr class="{{ $item->tipe_kategori == 'jaringan' ? 'bg-blue-50' : '' }}">
                    <td class="py-3 px-4">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                        @if($item->foto)
                            <img src="{{ asset('storage/'.$item->foto) }}" alt="{{ $item->nama }}" class="w-16 h-16 object-cover rounded">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                <i class="fas fa-box text-gray-400"></i>
                            </div>
                        @endif
                    </td>
                    <td class="py-3 px-4 font-mono text-sm">{{ $item->nomor_seri ?? '-' }}</td>
                    <td class="py-3 px-4">
                        <div class="font-medium">{{ $item->nama }}</div>
                        @if($item->tipe_kategori == 'jaringan')
                        <span class="text-xs bg-blue-100 text-blue-800 px-1 rounded">Jaringan</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">{{ $item->total }}</td>
                    <td class="py-3 px-4">{{ $item->ruangan->nama }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded text-xs 
                            @if($item->tipe_kategori == 'jaringan') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $item->kategori }}
                        </span>
                    </td>
                    <td class="py-3 px-4">{{ $item->tanggal_pembelian->format('d/m/Y') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if($item->kondisi == 'baik') bg-green-100 text-green-800
                            @elseif($item->kondisi == 'rusak_ringan') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $item->kondisi)) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs 
                            @if($item->status == 'tersedia') bg-blue-100 text-blue-800
                            @elseif($item->status == 'dipinjam') bg-purple-100 text-purple-800
                            @else bg-orange-100 text-orange-800 @endif">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    @if(auth()->user()->role === 'admin')
                    <td class="py-3 px-4 flex space-x-2">
                        <a href="{{ route('data_barang.edit', $item->id) }}" class="text-yellow-500 hover:text-yellow-700">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('data_barang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin?')">
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

    @if($barang->isEmpty())
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-box-open text-4xl mb-2"></i>
        <p>Tidak ada data barang ditemukan</p>
    </div>
    @endif
</div>
@endsection