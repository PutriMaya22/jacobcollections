@extends('operasional.layout')

@section('title', 'Barang Masuk')
@section('chart-label', 'Barang Masuk per Bulan')

@section('form')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100">
    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
        <i class="fas fa-plus-circle text-blue-500 mr-2"></i> Form Barang Masuk
    </h3>
    
    <form action="{{ route('barang_masuk.store') }}" method="POST" class="space-y-4">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Barang --> 
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Barang
                </label>
                <select name="id_barang" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" required>
                    <option value="">Pilih Barang</option>
                    @foreach($barangOptions as $barang)
                    <option value="{{ $barang->id }}" @selected(old('id_barang') == $barang->id)>
                        {{ $barang->nama }} (Stok: {{ $barang->total }})
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih barang yang akan ditambahkan stoknya</p>
            </div>
            
            <!-- Jumlah -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Jumlah
                </label>
                <div class="relative">
                    <input type="number" name="jumlah" min="1" value="{{ old('jumlah') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 pr-10 transition-all duration-200 shadow-sm" 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 text-sm">unit</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Masukkan jumlah barang yang masuk</p>
            </div>
            
            <!-- Tanggal Masuk -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tanggal Masuk
                </label>
                <div class="relative">
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-calendar text-gray-400"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Tanggal barang diterima</p>
            </div>
            
            <!-- Supplier -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Supplier
                </label>
                <input type="text" name="supplier" value="{{ old('supplier') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Nama supplier" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama supplier/pemasok</p>
            </div>
            
            <!-- Penerima -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Penerima
                </label>
                <input type="text" name="penerima" value="{{ old('penerima') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Nama penerima" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama yang menerima barang</p>
            </div>
            
            <!-- Keterangan -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    Keterangan (Opsional)
                </label>
                <textarea name="keterangan" rows="2" 
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                          placeholder="Catatan tambahan">{{ old('keterangan') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Tambahkan keterangan jika diperlukan</p>
            </div>
        </div>
        
        <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
            <button type="button" onclick="toggleForm()" 
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                <i class="fas fa-times mr-2"></i> Batal
            </button>
            <button type="submit" 
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm flex items-center">
                <i class="fas fa-save mr-2"></i> Simpan Data
            </button>
        </div>
    </form>
</div>

<script>
    // Validasi form
    document.querySelector('form').addEventListener('submit', function(e) {
        const jumlah = document.querySelector('input[name="jumlah"]');
        if (parseInt(jumlah.value) < 1) {
            e.preventDefault();
            alert('Jumlah harus lebih dari 0');
            jumlah.focus();
        }
    });
    
    // Datepicker default to today
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.querySelector('input[name="tanggal_masuk"]').value) {
            document.querySelector('input[name="tanggal_masuk"]').valueAsDate = new Date();
        }
    });
</script>
@endsection
@section('table')
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerima</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($barangMasuk as $item)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $item->barang->nama }}</div>
                <div class="text-sm text-gray-500">{{ $item->barang->kategori }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->jumlah }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->tanggal_masuk->format('d M Y') }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->supplier }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->penerima }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <form action="{{ route('barang_masuk.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data barang masuk</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $barangMasuk->links() }}
</div>
@endsection