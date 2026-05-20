@extends('layouts.app')

@section('title', 'Data Produk - JacobCollections')

@section('content')

<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .product-card {
        transition: all 0.2s ease;
    }
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .stok-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .stok-aman { background-color: #dcfce7; color: #166534; }
    .stok-menipis { background-color: #fef9c3; color: #854d0e; }
    .stok-habis { background-color: #fee2e2; color: #991b1b; }
    .cr-high { background-color: #dcfce7; color: #166534; }
    .cr-medium { background-color: #dbeafe; color: #1e40af; }
    .cr-low { background-color: #fef9c3; color: #854d0e; }
    .cr-very-low { background-color: #fee2e2; color: #991b1b; }
    .status-active { background-color: #dcfce7; color: #166534; }
    .status-inactive { background-color: #fee2e2; color: #991b1b; }
    .status-draft { background-color: #fef9c3; color: #854d0e; }
</style>

<div class="bg-gray-50 min-h-screen p-6">
    
    <!-- HEADER -->
    <div class="mb-6">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Data Produk</h1>
                <p class="text-gray-500 mt-1">Kelola data produk</p>
            </div>
            
            @if(auth()->user()->role === 'owner')
            <div class="flex gap-3">
                <!-- Tombol Tambah -->
                <a href="{{ route('data_barang.create') }}" 
                   class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg shadow-md transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Produk
                </a>

              <!-- Tombol Import (memunculkan modal) -->
<button type="button" 
        id="btnImportWithDate"
        class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-2 rounded-lg shadow-md transition duration-200 flex items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
    </svg>
    Import Excel
</button>

<!-- Form Import (tersembunyi, diisi via JS) -->
<form id="importForm"
      action="{{ route('data_barang.import') }}" 
      method="POST" 
      enctype="multipart/form-data"
      style="display: none;">
    @csrf
    <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv">
    <input type="hidden" name="bulan" id="bulanImport">
    <input type="hidden" name="tahun" id="tahunImport">
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('btnImportWithDate').addEventListener('click', function() {
    // Buat opsi bulan dan tahun
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const currentYear = new Date().getFullYear();
    let years = [];
    for (let y = currentYear - 2; y <= currentYear + 1; y++) {
        years.push(y);
    }
    
    // HTML untuk pilih bulan-tahun & file
    let htmlContent = `
        <div style="text-align: left;">
            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Pilih Bulan & Tahun Penjualan</label>
            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <select id="selectBulan" style="flex:1; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                    ${months.map((m, i) => `<option value="${i+1}">${m}</option>`).join('')}
                </select>
                <select id="selectTahun" style="flex:1; padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
                    ${years.map(y => `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`).join('')}
                </select>
            </div>
            
            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Pilih File Excel (xlsx/xls/csv)</label>
            <input type="file" id="fileImportInput" accept=".xlsx,.xls,.csv" style="width: 100%; padding: 8px;">
            
            <div class="text-sm text-gray-500 mt-3">
            </div>
        </div>
    `;
    
    Swal.fire({
        title: 'Import Data Penjualan',
        html: htmlContent,
        width: '550px',
        showCancelButton: true,
        confirmButtonText: 'Import Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        preConfirm: () => {
            const fileInput = document.getElementById('fileImportInput');
            const bulan = document.getElementById('selectBulan').value;
            const tahun = document.getElementById('selectTahun').value;
            
            if (!fileInput.files || !fileInput.files[0]) {
                Swal.showValidationMessage('❌ Pilih file Excel terlebih dahulu!');
                return false;
            }
            
            const fileName = fileInput.files[0].name;
            const ext = fileName.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls', 'csv'].includes(ext)) {
                Swal.showValidationMessage('❌ File harus berformat .xlsx, .xls, atau .csv');
                return false;
            }
            
            return { file: fileInput.files[0], bulan: bulan, tahun: tahun };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { file, bulan, tahun } = result.value;
            
            // Isi form hidden
            const form = document.getElementById('importForm');
            const fileInputHidden = document.getElementById('fileInput');
            const bulanInput = document.getElementById('bulanImport');
            const tahunInput = document.getElementById('tahunImport');
            
            // Pindahkan file ke input hidden form
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInputHidden.files = dataTransfer.files;
            
            bulanInput.value = bulan;
            tahunInput.value = tahun;
            
            // Submit form
            form.submit();
            
            Swal.fire({
                title: 'Sedang Import...',
                text: `Data untuk bulan ${bulan}/${tahun} sedang diproses`,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        }
    });
});
</script>
            </div>
            @endif
        </div>
    </div>

    {{-- ==================== MESSAGES ==================== --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6">
            {!! session('success') !!}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded mb-6">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif
<!-- STATISTIK CARD -->
@php
    $barangAman = $totalSemuaBarang - $barangHabis - $barangMenipis;
    $persentaseStokAman = $totalSemuaBarang > 0 ? ($barangAman / $totalSemuaBarang) * 100 : 0;
    $persentaseStokMenipis = $totalSemuaBarang > 0 ? ($barangMenipis / $totalSemuaBarang) * 100 : 0;
    $persentaseStokHabis = $totalSemuaBarang > 0 ? ($barangHabis / $totalSemuaBarang) * 100 : 0;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card 1: Jenis Items -->
    <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm">Jenis Items</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($totalBarangNormal) }}</p>
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
        <div class="mt-3 text-blue-100 text-xs">
            Produk dengan status Normal
        </div>
    </div>

    <!-- Card 2: Total Stok -->
    <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm">Total Stok</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($totalStok) }}</p>
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
        <div class="mt-3 text-green-100 text-xs">
            Seluruh produk
        </div>
    </div>

    <!-- Card 3: Stok Habis -->
    <div class="stat-card bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-red-100 text-sm">Stok Habis</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($barangHabis) }}</p>
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
        <div class="mt-3 text-red-100 text-xs">
            {{ number_format($persentaseStokHabis, 1) }}% dari total produk
        </div>
    </div>

    <!-- Card 4: Total Pesanan -->
    <div class="stat-card bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm">Total Pesanan</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($totalPesanan ?? 0) }}</p>
            </div>
            <div class="text-4xl opacity-75"></div>
        </div>
        <div class="mt-3 text-orange-100 text-xs">
            Seluruh pesanan dari semua produk
        </div>
    </div>
</div>

    <!-- FILTER SECTION -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('data_barang.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Produk</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama produk, kode produk, atau kategori..."
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Kategori</label>
                    <select name="kategori" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kategori)
                            <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                                {{ $kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
                    <select name="status_produk" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="Normal" {{ request('status_produk') == 'Normal' ? 'selected' : '' }}>Normal</option>
                        <option value="Diblokir" {{ request('status_produk') == 'Diblokir' ? 'selected' : '' }}>Diblokir</option>
                        <option value="Diarsipkan" {{ request('status_produk') == 'Diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <label class="text-sm text-gray-600">Tampilkan:</label>
                    <select name="per_page" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 data</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 data</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 data</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 data</option>
                    </select>
                    <span class="text-sm text-gray-500">
                        Menampilkan {{ $barang->firstItem() ?? 0 }} - {{ $barang->lastItem() ?? 0 }} dari {{ $barang->total() }} data
                    </span>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('data_barang.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLE BARANG -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kode Produk</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Produk</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status Produk</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Penjualan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Pesanan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">CTR / CR</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($barang as $item)
                    @php
                        $totalDilihat = $item->total_dilihat ?? 0;
                        $totalKlik = $item->total_klik ?? 0;
                        $totalPesanan = $item->total_pesanan ?? 0;
                         $ctr = $item->persentase_klik ?? 0;
                         $cr = $item->tingkat_konversi ?? 0;
                        
                        // Format untuk CR Class (opsional)
    if ($cr > 10) {
        $crClass = 'cr-high';
        $crIcon = '';
    } elseif ($cr > 5) {
        $crClass = 'cr-medium';
        $crIcon = '';
    } elseif ($cr > 2) {
        $crClass = 'cr-low';
        $crIcon = '';
    } else {
        $crClass = 'cr-very-low';
        $crIcon = '';
    }
    
    // Status Produk Class
    $statusClass = '';
    if ($item->status_produk == 'Normal') {
        $statusClass = 'status-active';
    } elseif ($item->status_produk == 'Diblokir') {
        $statusClass = 'status-inactive';
    } else {
        $statusClass = 'status-draft';
    }
                        
                     
                    @endphp
                    <tr class="product-card hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $loop->iteration + ($barang->currentPage() - 1) * $barang->perPage() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-sm font-medium text-gray-700">
                                {{ $item->kode_produk }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800">
                                {{ $item->nama }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge bg-gray-100 text-gray-700">
                                {{ $item->kategori ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge {{ $statusClass }}">
                                {{ $item->status_produk ?? 'Draft' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $stokClass = '';
                                $stokText = '';
                                if ($item->stok <= 0) {
                                    $stokClass = 'stok-habis';
                                    $stokText = 'Habis';
                                } elseif ($item->stok <= 10) {
                                    $stokClass = 'stok-menipis';
                                    $stokText = 'Menipis';
                                } else {
                                    $stokClass = 'stok-aman';
                                    $stokText = 'Aman';
                                }
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <span class="stok-badge {{ $stokClass }}">
                                    {{ number_format($item->stok) }} 
                                </span>
                                <span class="text-xs text-gray-500">{{ $stokText }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-green-600">
                                Rp {{ number_format($item->total_penjualan ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="font-bold text-blue-600 text-lg">
                                    {{ number_format($item->total_pesanan ?? 0) }}
                                </span>
                                <span class="text-xs text-gray-400">pesanan</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="badge bg-blue-50 text-blue-700 text-xs">
                                    CTR: {{ $ctr }}%
                                </span>
                                <span class="badge {{ $crClass }} text-xs">
                                    CR: {{ $cr }}% {{ $crIcon }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                @if(auth()->user()->role === 'owner')
                                    <a href="{{ route('data_barang.edit', $item->id) }}" 
                                       class="p-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition"
                                       title="Edit">
                                        ✏️
                                    </a>
                                    
                                    <form action="{{ route('data_barang.destroy', $item->id) }}" 
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus produk {{ $item->nama }}?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition"
                                                title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-center">
                                <div class="text-6xl mb-4"></div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Data Produk</h3>
                                <p class="text-gray-500 mb-4">Mulai tambahkan data produk untuk kelola inventaris</p>
                                @if(auth()->user()->role === 'owner')
                                <a href="{{ route('data_barang.create') }}" 
                                   class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Data Pertama
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- PAGINATION LINKS -->
        @if($barang->total() > $barang->perPage())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $barang->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
    
</div>

@endsection