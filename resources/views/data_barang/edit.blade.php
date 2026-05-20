@extends('layouts.app')

@section('title', 'Edit Data Produk - JacobCollections')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="text-2xl"></div>
        <h2 class="text-xl font-semibold text-gray-800">Edit Data Produk</h2>
    </div>
    
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
    
    <form action="{{ route('data_barang.update', $data_barang->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <!-- Kode Produk -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Kode Produk <span class="text-red-500">*</span></label>
        <input type="text" name="kode_produk" value="{{ old('kode_produk', $data_barang->kode_produk) }}" 
               class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
    </div>
    
    <!-- Nama Produk -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Nama Produk <span class="text-red-500">*</span></label>
        <textarea name="nama" rows="2" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>{{ old('nama', $data_barang->nama) }}</textarea>
    </div>
    
    <!-- Kategori -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Kategori <span class="text-red-500">*</span></label>
        <select name="kategori" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
            <option value="Panjang" {{ old('kategori', $data_barang->kategori) == 'Panjang' ? 'selected' : '' }}>Panjang</option>
            <option value="Pendek" {{ old('kategori', $data_barang->kategori) == 'Pendek' ? 'selected' : '' }}>Pendek</option>
            <option value="Denim" {{ old('kategori', $data_barang->kategori) == 'Denim' ? 'selected' : '' }}>Denim</option>
            <option value="Sedang Diskon" {{ old('kategori', $data_barang->kategori) == 'Sedang Diskon' ? 'selected' : '' }}>Sedang Diskon</option>
        </select>
    </div>
    
    <!-- Status Produk -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Status Produk</label>
        <select name="status_produk" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            <option value="Normal" {{ old('status_produk', $data_barang->status_produk) == 'Normal' ? 'selected' : '' }}>Normal</option>
            <option value="Diblokir" {{ old('status_produk', $data_barang->status_produk) == 'Diblokir' ? 'selected' : '' }}>Diblokir</option>
            <option value="Diarsipkan" {{ old('status_produk', $data_barang->status_produk) == 'Diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
    </div>
    
    <hr class="my-4 border-gray-200">
    
    <!-- Semua Metrik dalam 1 Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Penjualan (Rp)</label>
            <input type="text" name="total_penjualan" value="{{ old('total_penjualan', number_format($data_barang->total_penjualan ?? 0, 0, ',', '.')) }}" 
                   class="w-full border rounded-lg px-4 py-2 format-rupiah">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Pesanan</label>
            <input type="number" name="total_pesanan" value="{{ old('total_pesanan', $data_barang->total_pesanan ?? 0) }}" 
                   class="w-full border rounded-lg px-4 py-2">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Dilihat (Views)</label>
            <input type="number" name="total_dilihat" value="{{ old('total_dilihat', $data_barang->total_dilihat ?? 0) }}" 
                   class="w-full border rounded-lg px-4 py-2">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Klik (Clicks)</label>
            <input type="number" name="total_klik" value="{{ old('total_klik', $data_barang->total_klik ?? 0) }}" 
                   class="w-full border rounded-lg px-4 py-2">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">CTR (%)</label>
            <input type="number" name="persentase_klik" value="{{ old('persentase_klik', $data_barang->persentase_klik ?? 0) }}" 
                   step="0.01" class="w-full border rounded-lg px-4 py-2" placeholder="Contoh: 6">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">CR (%)</label>
            <input type="number" name="tingkat_konversi" value="{{ old('tingkat_konversi', $data_barang->tingkat_konversi ?? 0) }}" 
                   step="0.01" class="w-full border rounded-lg px-4 py-2" placeholder="Contoh: 7">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Pembeli</label>
            <input type="number" name="total_pembeli" value="{{ old('total_pembeli', $data_barang->total_pembeli ?? 0) }}" 
                   class="w-full border rounded-lg px-4 py-2">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Stok <span class="text-red-500">*</span></label>
            <input type="number" name="stok" value="{{ old('stok', $data_barang->stok) }}" 
                   class="w-full border rounded-lg px-4 py-2" required>
        </div>
    </div>
    
    <div class="flex justify-end gap-3 mt-6">
        <a href="{{ route('data_barang.index') }}" class="bg-gray-300 hover:bg-gray-400 px-6 py-2 rounded-lg">Batal</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update</button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
    const rupiahInput = document.querySelector('.format-rupiah');
    if (rupiahInput) {
        rupiahInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = new Intl.NumberFormat('id-ID').format(value);
            }
        });
    }
    
    document.querySelector('form').addEventListener('submit', function(e) {
        let penjualanInput = document.querySelector('.format-rupiah');
        if (penjualanInput) {
            penjualanInput.value = penjualanInput.value.replace(/\./g, '');
        }
    });
</script>
@endpush