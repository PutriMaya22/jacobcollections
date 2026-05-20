@extends('layouts.app')

@section('title', 'Tambah Data Produk - JacobCollections')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="text-2xl"></div>
        <h2 class="text-xl font-semibold text-gray-800">Tambah Data Produk</h2>
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

   <!-- Form Tambah Barang -->
<form action="{{ route('data_barang.store') }}" method="POST">
    @csrf
    
    <!-- Kode Produk -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Kode Produk <span class="text-red-500">*</span></label>
        <input type="text" name="kode_produk" value="{{ old('kode_produk') }}" 
               class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
    </div>
    
    <!-- Nama Barang -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Nama Produk <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="{{ old('nama') }}" 
               class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
    </div>
    
    <!-- Kategori -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Kategori <span class="text-red-500">*</span></label>
        <select name="kategori" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" required>
            <option value="Panjang">Panjang</option>
            <option value="Pendek">Pendek</option>
            <option value="Denim">Denim</option>
            <option value="Sedang Diskon">Sedang Diskon</option>
        </select>
    </div>
    
    <!-- Status Produk -->
    <div class="mb-4">
        <label class="block text-gray-700 mb-2 font-medium">Status Produk</label>
        <select name="status_produk" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            <option value="Normal">Normal</option>
            <option value="Diblokir">Diblokir</option>
            <option value="Diarsipkan">Diarsipkan</option>
        </select>
    </div>
    
    <hr class="my-4 border-gray-200">
    
    <!-- Semua Metrik dalam 1 Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Penjualan (Rp)</label>
            <input type="text" name="total_penjualan" id="total_penjualan" value="{{ old('total_penjualan') }}" 
                   class="w-full border rounded-lg px-4 py-2 format-rupiah" placeholder="80.000">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Pesanan</label>
            <input type="number" name="total_pesanan" value="{{ old('total_pesanan') }}" 
                   class="w-full border rounded-lg px-4 py-2" placeholder="0">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Dilihat (Views)</label>
            <input type="number" name="total_dilihat" value="{{ old('total_dilihat') }}" 
                   class="w-full border rounded-lg px-4 py-2" placeholder="0">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Klik (Clicks)</label>
            <input type="number" name="total_klik" value="{{ old('total_klik') }}" 
                   class="w-full border rounded-lg px-4 py-2" placeholder="0">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">CTR (%)</label>
            <input type="number" name="persentase_klik" value="{{ old('persentase_klik', 0) }}" 
                   step="0.01" class="w-full border rounded-lg px-4 py-2" placeholder="Contoh: 6">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">CR (%)</label>
            <input type="number" name="tingkat_konversi" value="{{ old('tingkat_konversi', 0) }}" 
                   step="0.01" class="w-full border rounded-lg px-4 py-2" placeholder="Contoh: 7">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Total Pembeli</label>
            <input type="number" name="total_pembeli" value="{{ old('total_pembeli') }}" 
                   class="w-full border rounded-lg px-4 py-2" placeholder="0">
        </div>
        
        <div>
            <label class="block text-gray-700 mb-2 font-medium">Stok <span class="text-red-500">*</span></label>
            <input type="number" name="stok" value="{{ old('stok') }}" 
                   class="w-full border rounded-lg px-4 py-2" required>
            <p class="text-xs text-gray-500 mt-1">Jumlah stok produk yang tersedia</p>
        </div>
    </div>
    
    <div class="flex justify-end gap-3">
        <a href="{{ route('data_barang.index') }}" class="bg-gray-300 hover:bg-gray-400 px-6 py-2 rounded-lg transition">Batal</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">Simpan</button>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
    // Format Rupiah untuk input total_penjualan
    const rupiahInput = document.getElementById('total_penjualan');
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
        let penjualanInput = document.getElementById('total_penjualan');
        if (penjualanInput) {
            let rawValue = penjualanInput.value.replace(/\./g, '');
            penjualanInput.value = rawValue;
            console.log('Nilai total_penjualan dikirim:', rawValue);
        }
        
        // Debug CTR dan CR
        let ctr = document.querySelector('[name="persentase_klik"]').value;
        let cr = document.querySelector('[name="tingkat_konversi"]').value;
        console.log('CTR dikirim:', ctr);
        console.log('CR dikirim:', cr);
    });
</script>
@endpush