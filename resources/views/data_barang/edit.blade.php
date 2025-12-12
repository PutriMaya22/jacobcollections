@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100 max-w-4xl mx-auto">
    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
        <i class="fas fa-edit text-blue-500 mr-2"></i> Edit Data Barang
    </h3>
    
    <form action="{{ route('data_barang.update', $data_barang->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Ruangan -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Ruangan
                </label>
                <select name="id_ruangan" id="id_ruangan" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                    <option value="">Pilih Ruangan</option>
                    @foreach($ruangan as $item)
                    <option value="{{ $item->id }}" {{ old('id_ruangan', $data_barang->id_ruangan) == $item->id ? 'selected' : '' }}>
                        {{ $item->nama }}
                    </option>
                    @endforeach
                </select>
                @error('id_ruangan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Nomor Seri -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Nomor Seri</label>
                <input type="text" name="nomor_seri" id="nomor_seri" 
                       value="{{ old('nomor_seri', $data_barang->nomor_seri) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm font-mono"
                       placeholder="Masukkan nomor seri">
                <p class="text-xs text-gray-500 mt-1">
                    @if($data_barang->nomor_seri)
                        Nomor seri saat ini: <span class="font-medium">{{ $data_barang->nomor_seri }}</span>
                    @else
                        Kosongkan untuk generate otomatis
                    @endif
                </p>
                @error('nomor_seri')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Foto Barang -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Foto Barang</label>
                <input type="file" name="foto" id="foto" 
                       accept="image/jpeg,image/png,image/jpg"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm">
                <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG (Maks. 2MB)</p>
                
                @if($data_barang->foto)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg border">
                    <p class="text-sm font-medium text-gray-700 mb-2">Foto Saat Ini:</p>
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('storage/'.$data_barang->foto) }}" alt="Foto Barang" 
                             class="w-20 h-20 object-cover rounded-lg border">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">File: {{ basename($data_barang->foto) }}</p>
                            <label class="flex items-center mt-2">
                                <input type="checkbox" name="hapus_foto" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Hapus foto saat ini</span>
                            </label>
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-2 p-3 bg-gray-50 rounded-lg border text-center">
                    <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500">Tidak ada foto</p>
                </div>
                @endif
                @error('foto')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Nama Barang -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Nama Barang
                </label>
                <input type="text" name="nama" id="nama" 
                       value="{{ old('nama', $data_barang->nama) }}" 
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
                <input type="number" name="total" id="total" min="0"
                       value="{{ old('total', $data_barang->total) }}" 
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
            required onchange="updateKategoriStyle()">
        <option value="">Pilih Kategori</option>
        <optgroup label="Jaringan Komputer">
            <option value="Router" {{ old('kategori', $data_barang->kategori) == 'Router' ? 'selected' : '' }}>Router</option>
            <option value="Access Point" {{ old('kategori', $data_barang->kategori) == 'Access Point' ? 'selected' : '' }}>Access Point</option>
            <option value="UPS" {{ old('kategori', $data_barang->kategori) == 'UPS' ? 'selected' : '' }}>UPS</option>
            <option value="Proyektor" {{ old('kategori', $data_barang->kategori) == 'Proyektor' ? 'selected' : '' }}>Proyektor</option>
            <option value="Aplikasi" {{ old('kategori', $data_barang->kategori) == 'Aplikasi' ? 'selected' : '' }}>Aplikasi</option>
            <option value="Server Baremetal Non Virtual" {{ old('kategori', $data_barang->kategori) == 'Server Baremetal Non Virtual' ? 'selected' : '' }}>Server Baremetal Non Virtual</option>
            <option value="Server Fisik Host Virtualisasi" {{ old('kategori', $data_barang->kategori) == 'Server Fisik Host Virtualisasi' ? 'selected' : '' }}>Server Fisik Host Virtualisasi</option>
            <option value="Laptop Pc-LPTPC" {{ old('kategori', $data_barang->kategori) == 'Laptop Pc-LPTPC' ? 'selected' : '' }}>Laptop PC-LPTPC</option>
            <option value="jaringan-NTWRK" {{ old('kategori', $data_barang->kategori) == 'jaringan-NTWRK' ? 'selected' : '' }}>Jaringan-NTWRK</option>
            <option value="Switch" {{ old('kategori', $data_barang->kategori) == 'Switch' ? 'selected' : '' }}>Switch</option>
            <option value="server Storage" {{ old('kategori', $data_barang->kategori) == 'server Storage' ? 'selected' : '' }}>Server Storage</option>
            <option value="Lissence - LICNS" {{ old('kategori', $data_barang->kategori) == 'Lissence - LICNS' ? 'selected' : '' }}>Lissence - LICNS</option>
            <option value="Backup Aplliance" {{ old('kategori', $data_barang->kategori) == 'Backup Aplliance' ? 'selected' : '' }}>Backup Aplliance</option>
            <option value="WLAN Controller" {{ old('kategori', $data_barang->kategori) == 'WLAN Controller' ? 'selected' : '' }}>WLAN Controller</option>
        </optgroup>
        <optgroup label="Kategori Lainnya">
            <option value="Elektronik" {{ old('kategori', $data_barang->kategori) == 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
            <option value="Furniture" {{ old('kategori', $data_barang->kategori) == 'Furniture' ? 'selected' : '' }}>Furniture</option>
            <option value="ATK" {{ old('kategori', $data_barang->kategori) == 'ATK' ? 'selected' : '' }}>ATK (Alat Tulis Kantor)</option>
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
                <select name="kondisi" id="kondisi" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                    <option value="">Pilih Kondisi</option>
                    <option value="baik" {{ old('kondisi', $data_barang->kondisi) == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ old('kondisi', $data_barang->kondisi) == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ old('kondisi', $data_barang->kondisi) == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
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
                    <option value="tersedia" {{ old('status', $data_barang->status) == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="dipinjam" {{ old('status', $data_barang->status) == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="perbaikan" {{ old('status', $data_barang->status) == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
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
                       value="{{ old('tanggal_pembelian', $data_barang->tanggal_pembelian->format('Y-m-d')) }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 shadow-sm" required>
                @error('tanggal_pembelian')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Informasi Barang Saat Ini -->
<div class="mt-6 p-4 bg-gray-50 rounded-lg border">
    <h4 class="font-medium text-gray-800 mb-2 flex items-center">
        <i class="fas fa-info-circle text-blue-500 mr-2"></i> Informasi Barang Saat Ini
    </h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <span class="text-gray-600">Kategori:</span>
            <span class="font-medium ml-1 {{ in_array($data_barang->kategori, ['Router', 'Access Point', 'UPS', 'Proyektor', 'Aplikasi', 'Server Baremetal Non Virtual', 'Server Fisik Host Virtualisasi', 'Laptop Pc-LPTPC', 'jaringan-NTWRK', 'Switch', 'server Storage', 'Lissence - LICNS', 'Backup Aplliance', 'WLAN Controller']) ? 'text-blue-600' : 'text-gray-800' }}">
                {{ $data_barang->kategori }}
            </span>
        </div>
        <div>
            <span class="text-gray-600">Nomor Seri:</span>
            <span class="font-medium ml-1 font-mono">{{ $data_barang->nomor_seri ?? '-' }}</span>
        </div>
        <div>
            <span class="text-gray-600">Ruangan:</span>
            <span class="font-medium ml-1">{{ $data_barang->ruangan->nama }}</span>
        </div>
        <div>
            <span class="text-gray-600">Terakhir Update:</span>
            <span class="font-medium ml-1">{{ $data_barang->updated_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</div>

        <!-- Deskripsi Tambahan untuk Kategori Jaringan -->
<div id="deskripsi_jaringan" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg 
    {{ in_array($data_barang->kategori, ['Router', 'Access Point', 'UPS', 'Proyektor', 'Aplikasi', 'Server Baremetal Non Virtual', 'Server Fisik Host Virtualisasi', 'Laptop Pc-LPTPC', 'jaringan-NTWRK', 'Switch', 'server Storage', 'Lissence - LICNS', 'Backup Aplliance', 'WLAN Controller']) ? '' : 'hidden' }}">
    <h4 class="font-medium text-blue-800 mb-2 flex items-center">
        <i class="fas fa-network-wired mr-2"></i> Informasi Jaringan Komputer
    </h4>
    <p class="text-sm text-blue-700">
        Barang ini termasuk dalam kategori jaringan komputer. Pastikan informasi teknis tetap akurat.
    </p>
</div>

        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-100">
            <a href="{{ route('data_barang.index') }}" 
               class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150">
                <i class="fas fa-times mr-2"></i> Batal
            </a>
            <button type="submit" 
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center transition duration-150">
                <i class="fas fa-save mr-2"></i> Update Barang
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
            defaultDate: "{{ old('tanggal_pembelian', $data_barang->tanggal_pembelian->format('Y-m-d')) }}",
            maxDate: "today"
        });

        // Kategori jaringan untuk styling
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
        // Fungsi untuk update styling kategori
        function updateKategoriStyle() {
            const kategoriSelect = document.getElementById('kategori');
            const deskripsiJaringan = document.getElementById('deskripsi_jaringan');
            
            if (kategoriSelect.value && kategoriJaringan.includes(kategoriSelect.value)) {
                kategoriSelect.classList.add('border-blue-300', 'bg-blue-50');
                deskripsiJaringan.classList.remove('hidden');
            } else {
                kategoriSelect.classList.remove('border-blue-300', 'bg-blue-50');
                deskripsiJaringan.classList.add('hidden');
            }
        }

        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const kategori = document.getElementById('kategori').value;
            const nama = document.getElementById('nama').value;
            const total = document.getElementById('total').value;
            
            // Validasi untuk kategori jaringan
            if (kategoriJaringan.includes(kategori)) {
                const kataKunciJaringan = ['router', 'switch', 'access point', 'server', 'kabel', 'network', 'jaringan'];
                const namaLower = nama.toLowerCase();
                const containsKeyword = kataKunciJaringan.some(keyword => 
                    namaLower.includes(keyword)
                );
                
                if (!containsKeyword) {
                    if (!confirm('Barang dengan kategori jaringan sebaiknya memiliki nama yang relevan. Lanjutkan update?')) {
                        e.preventDefault();
                        return;
                    }
                }
            }

            // Validasi total tidak boleh negatif
            if (parseInt(total) < 0) {
                alert('Total stok tidak boleh negatif');
                e.preventDefault();
                return;
            }

            // Konfirmasi update
            if (!confirm('Apakah Anda yakin ingin mengupdate data barang ini?')) {
                e.preventDefault();
                return;
            }
        });

        // Preview gambar baru sebelum upload
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('foto-preview');
            
            if (!previewContainer) {
                // Buat container preview jika belum ada
                const container = document.createElement('div');
                container.id = 'foto-preview';
                container.className = 'mt-3 p-3 bg-green-50 rounded-lg border border-green-200';
                this.parentNode.appendChild(container);
            } else {
                previewContainer.innerHTML = '';
            }

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.getElementById('foto-preview');
                    previewDiv.innerHTML = `
                        <p class="text-sm font-medium text-green-700 mb-2">Preview Foto Baru:</p>
                        <div class="flex items-center space-x-4">
                            <img src="${e.target.result}" alt="Preview Foto Baru" 
                                 class="w-20 h-20 object-cover rounded-lg border">
                            <div class="flex-1">
                                <p class="text-sm text-green-600">File: ${file.name}</p>
                                <p class="text-xs text-green-500">Size: ${(file.size / 1024).toFixed(2)} KB</p>
                            </div>
                        </div>
                    `;
                }
                reader.readAsDataURL(file);
            }
        });

        // Panggil fungsi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateKategoriStyle();
            
            // Set focus ke field nama
            document.getElementById('nama').focus();
        });
    </script>
@endpush