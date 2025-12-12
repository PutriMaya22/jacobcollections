@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100">
    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
        <i class="fas fa-plus-circle text-blue-500 mr-2"></i> Tambah Barang
    </h3>
    
    <form action="{{ route('data_barang.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Ruangan -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Ruangan
                </label>
                <select name="id_ruangan" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                    <option value="">Pilih Ruangan</option>
                    @foreach($ruangan as $item)
                        <option value="{{ $item->id }}" {{ old('id_ruangan') == $item->id ? 'selected' : '' }}>
                            {{ $item->nama }}
                        </option>
                    @endforeach
                </select>
                @error('id_ruangan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nomor Seri (Read-only, akan di-generate otomatis) -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Nomor Seri</label>
                <input type="text" id="nomor_seri_preview" readonly
                       class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm py-2.5 px-4 shadow-sm"
                       placeholder="Nomor seri akan tergenerate otomatis">
                <input type="hidden" name="nomor_seri" id="nomor_seri_hidden">
                <p class="text-xs text-gray-500 mt-1">Nomor seri akan tergenerate otomatis berdasarkan kategori</p>
            </div>

            <!-- Foto Barang -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Foto Barang</label>
                <input type="file" name="foto" id="foto" 
                       accept="image/jpeg,image/png,image/jpg"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG (Maks. 2MB)</p>
                @error('foto')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Barang -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Nama Barang
                </label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" 
                       placeholder="Masukkan nama barang" required>
                @error('nama')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Total -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Total Stok
                </label>
                <input type="number" name="total" min="1" value="{{ old('total', 1) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" 
                       placeholder="Jumlah barang" required>
                @error('total')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kategori -->
<div class="space-y-1">
    <label class="block text-sm font-medium text-gray-700">
        <span class="text-red-500">*</span> Kategori
    </label>
    <select name="kategori" id="kategori" 
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" 
            required onchange="updateNomorSeriPreview()">
        <option value="">Pilih Kategori</option>
        <optgroup label="Jaringan Komputer">
            <option value="Router" {{ old('kategori') == 'Router' ? 'selected' : '' }}>Router</option>
            <option value="Access Point" {{ old('kategori') == 'Access Point' ? 'selected' : '' }}>Access Point</option>
            <option value="UPS" {{ old('kategori') == 'UPS' ? 'selected' : '' }}>UPS</option>
            <option value="Proyektor" {{ old('kategori') == 'Proyektor' ? 'selected' : '' }}>Proyektor</option>
            <option value="Aplikasi" {{ old('kategori') == 'Aplikasi' ? 'selected' : '' }}>Aplikasi</option>
            <option value="Server Baremetal Non Virtual" {{ old('kategori') == 'Server Baremetal Non Virtual' ? 'selected' : '' }}>Server Baremetal Non Virtual</option>
            <option value="Server Fisik Host Virtualisasi" {{ old('kategori') == 'Server Fisik Host Virtualisasi' ? 'selected' : '' }}>Server Fisik Host Virtualisasi</option>
            <option value="Laptop Pc-LPTPC" {{ old('kategori') == 'Laptop Pc-LPTPC' ? 'selected' : '' }}>Laptop PC-LPTPC</option>
            <option value="jaringan-NTWRK" {{ old('kategori') == 'jaringan-NTWRK' ? 'selected' : '' }}>Jaringan-NTWRK</option>
            <option value="Switch" {{ old('kategori') == 'Switch' ? 'selected' : '' }}>Switch</option>
            <option value="server Storage" {{ old('kategori') == 'server Storage' ? 'selected' : '' }}>Server Storage</option>
            <option value="Lissence - LICNS" {{ old('kategori') == 'Lissence - LICNS' ? 'selected' : '' }}>Lissence - LICNS</option>
            <option value="Backup Aplliance" {{ old('kategori') == 'Backup Aplliance' ? 'selected' : '' }}>Backup Aplliance</option>
            <option value="WLAN Controller" {{ old('kategori') == 'WLAN Controller' ? 'selected' : '' }}>WLAN Controller</option>
        </optgroup>
        <optgroup label="Kategori Lainnya">
            <option value="Elektronik" {{ old('kategori') == 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
            <option value="Furniture" {{ old('kategori') == 'Furniture' ? 'selected' : '' }}>Furniture</option>
            <option value="ATK" {{ old('kategori') == 'ATK' ? 'selected' : '' }}>ATK (Alat Tulis Kantor)</option>
        </optgroup>
    </select>
    @error('kategori')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
            
            <!-- Kondisi -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Kondisi
                </label>
                <select name="kondisi" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                    <option value="">Pilih Kondisi</option>
                    <option value="baik" {{ old('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ old('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ old('kondisi') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
                @error('kondisi')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Status
                </label>
                <select name="status" id="status" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                    <option value="tersedia" {{ old('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="dipinjam" {{ old('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="perbaikan" {{ old('status') == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal Pembelian -->
            <div class="space-y-1">
                <label for="tanggal_pembelian" class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tanggal Pembelian
                </label>
                <input type="date" name="tanggal_pembelian" id="tanggal_pembelian"
                       value="{{ old('tanggal_pembelian', date('Y-m-d')) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                @error('tanggal_pembelian')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <!-- Deskripsi Tambahan untuk Kategori Jaringan -->
        <div id="deskripsi_jaringan" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
            <h4 class="font-medium text-blue-800 mb-2 flex items-center">
                <i class="fas fa-network-wired mr-2"></i> Informasi Jaringan Komputer
            </h4>
            <p class="text-sm text-blue-700">
                Untuk barang kategori jaringan komputer, pastikan informasi berikut sudah lengkap:
            </p>
            <ul class="text-sm text-blue-700 mt-2 list-disc list-inside">
                <li>Nomor seri akan tergenerate otomatis dengan kode khusus jaringan</li>
                <li>Pastikan spesifikasi teknis sudah tercatat dengan benar</li>
                <li>Periksa kondisi konektivitas dan fungsionalitas perangkat</li>
            </ul>
        </div>

        <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100 mt-6">
            <a href="{{ route('data_barang.index') }}" 
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center transition duration-150">
                <i class="fas fa-save mr-2"></i> Simpan Barang
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <script>
        // Inisialisasi flatpickr
        flatpickr("#tanggal_pembelian", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            locale: "id",
            defaultDate: "{{ old('tanggal_pembelian', date('Y-m-d')) }}",
            maxDate: "today"
        });

        // Mapping kategori ke kode
const kodeMapping = {
    // Jaringan Komputer
    'Router': 'RTR',
    'Access Point': 'ACP',
    'UPS': 'UPS',
    'Proyektor': 'PRJ',
    'Aplikasi': 'APP',
    'Server Baremetal Non Virtual': 'SBNV',
    'Server Fisik Host Virtualisasi': 'SFHV',
    'Laptop Pc-LPTPC': 'LPC',
    'jaringan-NTWRK': 'NTW',
    'Switch': 'SWT',
    'server Storage': 'STS',
    'Lissence - LICNS': 'LIC',
    'Backup Aplliance': 'BKA',
    'WLAN Controller': 'WLC',
    // Kategori Lainnya
    'Elektronik': 'ELT',
    'Furniture': 'FTR',
    'ATK': 'ATK'
};

// Kategori jaringan untuk menampilkan info tambahan
const kategoriJaringan = [
    'Router',
    'Access Point',
    'UPS',
    'Proyektor',
    'Aplikasi',
    'Server Baremetal Non Virtual',
    'Server Fisik Host Virtualisasi',
    'Laptop Pc-LPTPC',
    'jaringan-NTWRK',
    'Switch',
    'server Storage',
    'Lissence - LICNS',
    'Backup Aplliance',
    'WLAN Controller'
];

        // Fungsi untuk menampilkan preview nomor seri
        function updateNomorSeriPreview() {
            const kategoriSelect = document.getElementById('kategori');
            const nomorSeriPreview = document.getElementById('nomor_seri_preview');
            const nomorSeriHidden = document.getElementById('nomor_seri_hidden');
            const deskripsiJaringan = document.getElementById('deskripsi_jaringan');

            if (kategoriSelect.value) {
                const kode = kodeMapping[kategoriSelect.value] || 'BRG';
                
                // Tampilkan format preview
                nomorSeriPreview.value = kode + 'XXXXX (akan tergenerate otomatis)';
                nomorSeriHidden.value = 'AUTO';

                // Tampilkan/sembunyikan deskripsi jaringan
                if (kategoriJaringan.includes(kategoriSelect.value)) {
                    deskripsiJaringan.classList.remove('hidden');
                } else {
                    deskripsiJaringan.classList.add('hidden');
                }

                // Tambahkan class styling untuk kategori jaringan
                if (kategoriJaringan.includes(kategoriSelect.value)) {
                    kategoriSelect.classList.add('border-blue-300', 'bg-blue-50');
                } else {
                    kategoriSelect.classList.remove('border-blue-300', 'bg-blue-50');
                }
            } else {
                nomorSeriPreview.value = '';
                nomorSeriHidden.value = '';
                deskripsiJaringan.classList.add('hidden');
                kategoriSelect.classList.remove('border-blue-300', 'bg-blue-50');
            }
        }

        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const kategori = document.getElementById('kategori').value;
            const nama = document.getElementsByName('nama')[0].value;
            
            // Validasi tambahan untuk kategori jaringan
            if (kategoriJaringan.includes(kategori)) {
                // Cek apakah nama barang mengandung kata kunci jaringan
                const kataKunciJaringan = ['router', 'switch', 'access point', 'server', 'kabel', 'network', 'jaringan'];
                const namaLower = nama.toLowerCase();
                const containsKeyword = kataKunciJaringan.some(keyword => 
                    namaLower.includes(keyword)
                );
                
                if (!containsKeyword) {
                    if (!confirm('Barang dengan kategori jaringan sebaiknya memiliki nama yang relevan. Lanjutkan penyimpanan?')) {
                        e.preventDefault();
                        return;
                    }
                }
            }
        });

        // Preview gambar sebelum upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Tampilkan preview gambar (opsional, bisa ditambahkan nanti)
                    console.log('File selected:', file.name);
                }
                reader.readAsDataURL(file);
            }
        });

        // Panggil fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateNomorSeriPreview();
            
            // Set focus ke field pertama
            document.querySelector('input[name="nama"]').focus();
        });
    </script>
@endpush