@extends('layouts.app')

@section('title', 'Tambah Data Barang - JacobCollections')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="text-2xl"></div>
        <h2 class="text-xl font-semibold text-gray-800">Tambah Data Barang</h2>
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
    
    {{-- 🔥 PERBAIKAN: action langsung ke store, tidak pakai $data_barang --}}
    <form action="{{ route('data_barang.store') }}" method="POST">
        @csrf
        
        <!-- Kode Produk -->
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">
                Kode Produk <span class="text-red-500">*</span>
            </label>
            <input type="text" name="kode_produk" value="{{ old('kode_produk') }}" 
                   class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('kode_produk') border-red-500 @enderror" 
                   placeholder="Contoh: 28951216360" required>
            @error('kode_produk')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Nama Produk -->
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">
                Nama Produk <span class="text-red-500">*</span>
            </label>
            <textarea name="nama" rows="2" 
                   class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama') border-red-500 @enderror" 
                   placeholder="Nama lengkap produk..." required>{{ old('nama') }}</textarea>
            @error('nama')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Kategori -->
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">
                Kategori <span class="text-red-500">*</span>
            </label>
            <select name="kategori" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('kategori') border-red-500 @enderror" required>
                <option value="">Pilih Kategori</option>
                <option value="Panjang" {{ old('kategori') == 'Panjang' ? 'selected' : '' }}>Panjang</option>
                <option value="Pendek" {{ old('kategori') == 'Pendek' ? 'selected' : '' }}>Pendek</option>
                <option value="Denim" {{ old('kategori') == 'Denim' ? 'selected' : '' }}>Denim</option>
                <option value="Sedang Diskon" {{ old('kategori') == 'Sedang Diskon' ? 'selected' : '' }}>Sedang Diskon</option>
            </select>
            @error('kategori')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <!-- Status Produk -->
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">
                Status Produk
            </label>
            <select name="status_produk" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="Aktif" {{ old('status_produk') == 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Nonaktif" {{ old('status_produk') == 'Diblokir' ? 'selected' : '' }}>Diblokir</option>
                <option value="Diskon" {{ old('status_produk') == 'Diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
            </select>
        </div>
        
        <hr class="my-4 border-gray-200">
        
        <!-- Metrik Penjualan Section -->
        <div class="bg-blue-50 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-blue-800 mb-3">Metrik Penjualan</h3>
            <p class="text-xs text-blue-600 mb-3">Data ini akan digunakan untuk menghitung Conversion Rate (CR)</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Total Penjualan -->
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">
                        Total Penjualan (Rp)
                    </label>
                    <input type="text" name="total_penjualan" value="{{ old('total_penjualan') }}" 
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 format-rupiah @error('total_penjualan') border-red-500 @enderror" 
                           placeholder="0">
                    @error('total_penjualan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Total Dilihat -->
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">
                        Total Dilihat (Views)
                    </label>
                    <input type="number" name="total_dilihat" value="{{ old('total_dilihat', 0) }}" 
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('total_dilihat') border-red-500 @enderror" 
                           placeholder="0" min="0">
                    @error('total_dilihat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Total Klik -->
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">
                        Total Klik (Clicks)
                    </label>
                    <input type="number" name="total_klik" value="{{ old('total_klik', 0) }}" 
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('total_klik') border-red-500 @enderror" 
                           placeholder="0" min="0">
                    @error('total_klik')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Total Pesanan -->
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">
                        Total Pesanan (Orders)
                    </label>
                    <input type="number" name="total_pesanan" value="{{ old('total_pesanan', 0) }}" 
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('total_pesanan') border-red-500 @enderror" 
                           placeholder="0" min="0">
                    @error('total_pesanan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Total Pembeli -->
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">
                        Total Pembeli
                    </label>
                    <input type="number" name="total_pembeli" value="{{ old('total_pembeli', 0) }}" 
                           class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('total_pembeli') border-red-500 @enderror" 
                           placeholder="0" min="0">
                    @error('total_pembeli')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Stok -->
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">
                Stok <span class="text-red-500">*</span>
            </label>
            <input type="number" name="stok" value="{{ old('stok', 0) }}" 
                   class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('stok') border-red-500 @enderror" 
                   placeholder="0" min="0" required>
            @error('stok')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-400 mt-1">Jumlah stok barang yang tersedia</p>
        </div>
    
        
        <!-- Tombol -->
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('data_barang.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition duration-200">
                Batal
            </a>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                Simpan Produk
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Format Rupiah untuk input total_penjualan
    const rupiahInput = document.querySelector('.format-rupiah');
    if (rupiahInput) {
        rupiahInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = new Intl.NumberFormat('id-ID').format(value);
            }
        });
    }
    
    // Bersihkan format rupiah sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        let penjualanInput = document.querySelector('.format-rupiah');
        if (penjualanInput) {
            penjualanInput.value = penjualanInput.value.replace(/\./g, '');
        }
    });
</script>
@endpush