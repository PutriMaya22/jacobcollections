@extends('operasional.layout')

@section('title', 'Pemeliharaan Barang')
@section('chart-label', 'Pemeliharaan per Bulan')

@section('form')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100">
    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
        <i class="fas fa-tools text-blue-500 mr-2"></i> Form Jadwal Pemeliharaan
    </h3>
    
    <form action="{{ route('pemeliharaan.store') }}" method="POST" class="space-y-4">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Ruangan -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Ruangan
                </label>
                <select name="id_ruangan" id="ruanganSelect" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                        required onchange="loadBarangByRuangan()">
                    <option value="">Pilih Ruangan</option>
                    @foreach($ruanganOptions as $ruangan)
                    <option value="{{ $ruangan->id }}" @selected(old('id_ruangan') == $ruangan->id)>
                        {{ $ruangan->nama }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih ruangan terlebih dahulu</p>
            </div>
            
            <!-- Barang -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Barang
                </label>
                <select name="barang_id" id="barangSelect" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                        required onchange="updateBarangInfo()" disabled>
                    <option value="">Pilih Barang</option>
                    <!-- Options akan diisi via JavaScript -->
                </select>
                <div id="barangInfo" class="text-xs text-gray-500 mt-1 hidden">
                    Kondisi: <span id="kondisiValue" class="font-medium">-</span>
                </div>
                <div id="barangHelp" class="text-xs text-gray-500 mt-1">
                    Pilih ruangan terlebih dahulu untuk melihat daftar barang
                </div>
            </div>
            
            <!-- Jenis Pemeliharaan -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Jenis Pemeliharaan
                </label>
                <select name="jenis_pemeliharaan" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" required>
                    <option value="">Pilih Jenis</option>
                    <option value="rutin" @selected(old('jenis_pemeliharaan') == 'rutin')>Rutin</option>
                    <option value="preventif" @selected(old('jenis_pemeliharaan') == 'preventif')>Preventif</option>
                    <option value="korektif" @selected(old('jenis_pemeliharaan') == 'korektif')>Korektif</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih jenis pemeliharaan</p>
            </div>
            
            <!-- Tanggal Mulai -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tanggal Mulai
                </label>
                <div class="relative">
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-calendar text-gray-400"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Tanggal mulai pemeliharaan</p>
            </div>
            
            <!-- Teknisi -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Teknisi
                </label>
                <input type="text" name="teknisi" value="{{ old('teknisi') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Nama teknisi" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama teknisi</p>
            </div>
            
            <!-- Biaya -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Biaya
                </label>
                <div class="relative">
                    <input type="number" name="biaya" min="0" value="{{ old('biaya', 0) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 pr-10 transition-all duration-200 shadow-sm" 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 text-sm">Rp</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Masukkan biaya pemeliharaan</p>
            </div>
            
            <!-- Status -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Status
                </label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" required>
                    <option value="dijadwalkan" @selected(old('status') == 'dijadwalkan')>Dijadwalkan</option>
                    <option value="dalam_pengerjaan" @selected(old('status') == 'dalam_pengerjaan')>Dalam Pengerjaan</option>
                    <option value="selesai" @selected(old('status') == 'selesai')>Selesai</option>
                    <option value="ditunda" @selected(old('status') == 'ditunda')>Ditunda</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih status pemeliharaan</p>
            </div>
            
            <!-- Keterangan -->
            <div class="space-y-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Keterangan
                </label>
                <textarea name="keterangan" rows="3" 
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                          placeholder="Deskripsi pemeliharaan" required>{{ old('keterangan') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Tambahkan keterangan pemeliharaan</p>
            </div>
        </div>
        
        <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
            <button type="button" onclick="resetForm()" 
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                <i class="fas fa-times mr-2"></i> Batal
            </button>
            <button type="submit" id="submitButton"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm flex items-center">
                <i class="fas fa-save mr-2"></i> Simpan Jadwal
            </button>
        </div>
    </form>
</div>

<script>
    // Data barang berdasarkan ruangan (akan diisi via PHP)
    let barangByRuangan = {!! json_encode($barangByRuangan) !!};

    // Fungsi untuk memuat barang berdasarkan ruangan yang dipilih
    function loadBarangByRuangan() {
        const ruanganSelect = document.getElementById('ruanganSelect');
        const barangSelect = document.getElementById('barangSelect');
        const barangInfo = document.getElementById('barangInfo');
        const barangHelp = document.getElementById('barangHelp');
        
        const ruanganId = ruanganSelect.value;
        
        // Reset form barang
        barangSelect.innerHTML = '<option value="">Pilih Barang</option>';
        barangSelect.disabled = true;
        barangInfo.classList.add('hidden');
        
        if (ruanganId) {
            // Cari barang berdasarkan ruangan
            const barangList = barangByRuangan[ruanganId] || [];
            
            if (barangList.length > 0) {
                // Tambahkan opsi barang
                barangList.forEach(barang => {
                    const option = document.createElement('option');
                    option.value = barang.id;
                    option.textContent = `${barang.nama} (${barang.kondisi})`;
                    option.setAttribute('data-kondisi', barang.kondisi);
                    option.setAttribute('data-kategori', barang.kategori);
                    barangSelect.appendChild(option);
                });
                
                barangSelect.disabled = false;
                barangHelp.textContent = 'Pilih barang yang akan dipelihara';
                barangHelp.className = 'text-xs text-gray-500 mt-1';
            } else {
                barangHelp.textContent = 'Tidak ada barang di ruangan ini yang membutuhkan pemeliharaan';
                barangHelp.className = 'text-xs text-yellow-600 mt-1';
            }
        } else {
            barangHelp.textContent = 'Pilih ruangan terlebih dahulu untuk melihat daftar barang';
            barangHelp.className = 'text-xs text-gray-500 mt-1';
        }
    }

    // Fungsi untuk update info barang
    function updateBarangInfo() {
        const select = document.getElementById('barangSelect');
        const barangInfo = document.getElementById('barangInfo');
        const kondisiValue = document.getElementById('kondisiValue');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            const kondisi = selectedOption.getAttribute('data-kondisi');
            kondisiValue.textContent = kondisi.charAt(0).toUpperCase() + kondisi.slice(1).replace('_', ' ');
            barangInfo.classList.remove('hidden');
        } else {
            barangInfo.classList.add('hidden');
        }
    }
    
    // Fungsi reset form
    function resetForm() {
        document.getElementById('ruanganSelect').value = '';
        document.getElementById('barangSelect').innerHTML = '<option value="">Pilih Barang</option>';
        document.getElementById('barangSelect').disabled = true;
        document.getElementById('barangInfo').classList.add('hidden');
        document.getElementById('barangHelp').textContent = 'Pilih ruangan terlebih dahulu untuk melihat daftar barang';
        document.getElementById('barangHelp').className = 'text-xs text-gray-500 mt-1';
    }
    
    // Validasi form
    document.querySelector('form').addEventListener('submit', function(e) {
        const ruanganSelect = document.getElementById('ruanganSelect');
        const barangSelect = document.getElementById('barangSelect');
        const biaya = document.querySelector('input[name="biaya"]');
        
        if (!ruanganSelect.value) {
            e.preventDefault();
            alert('Silakan pilih ruangan terlebih dahulu!');
            return;
        }
        
        if (!barangSelect.value) {
            e.preventDefault();
            alert('Silakan pilih barang terlebih dahulu!');
            return;
        }
        
        if (parseFloat(biaya.value) < 0) {
            e.preventDefault();
            alert('Biaya tidak boleh negatif');
            biaya.focus();
        }
    });
    
    // Datepicker default to today
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.querySelector('input[name="tanggal_mulai"]').value) {
            document.querySelector('input[name="tanggal_mulai"]').valueAsDate = new Date();
        }
        
        // Inisialisasi jika ada old input
        const oldRuanganId = "{{ old('id_ruangan') }}";
        if (oldRuanganId) {
            document.getElementById('ruanganSelect').value = oldRuanganId;
            loadBarangByRuangan();
            
            // Set barang jika ada old input
            const oldBarangId = "{{ old('barang_id') }}";
            if (oldBarangId) {
                setTimeout(() => {
                    document.getElementById('barangSelect').value = oldBarangId;
                    updateBarangInfo();
                }, 100);
            }
        }
    });
</script>
@endsection

@section('table')
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teknisi</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($pemeliharaan as $item)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $item->barang->nama }}</div>
                <div class="text-sm text-gray-500">{{ $item->barang->kategori }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->barang->ruangan->nama }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ ucfirst($item->jenis_pemeliharaan) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->tanggal_mulai->format('d M Y') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->teknisi }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                Rp {{ number_format($item->biaya, 0, ',', '.') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 rounded-full text-xs 
                    @if($item->status == 'selesai') bg-green-100 text-green-800
                    @elseif($item->status == 'dalam_pengerjaan') bg-yellow-100 text-yellow-800
                    @elseif($item->status == 'ditunda') bg-red-100 text-red-800
                    @else bg-blue-100 text-blue-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <a href="{{ route('pemeliharaan.edit', $item->id) }}" class="text-yellow-500 hover:text-yellow-700">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('pemeliharaan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data pemeliharaan</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $pemeliharaan->links() }}
</div>
@endsection