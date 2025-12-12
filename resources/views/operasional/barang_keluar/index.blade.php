@extends('operasional.layout')

@section('title', 'Barang Keluar')
@section('chart-label', 'Barang Keluar per Bulan')

@section('content')
    <!-- Notifikasi -->
    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="font-medium">Error!</p>
                <p>{{ session('error') }}</p>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-red-100 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="font-medium">Sukses!</p>
                <p>{{ session('success') }}</p>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-green-100 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

@section('form')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100">
    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
        <i class="fas fa-arrow-up text-blue-500 mr-2"></i> Form Barang Keluar
    </h3>
    
    <form action="{{ route('barang_keluar.store') }}" method="POST" class="space-y-4">
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
                <select name="id_barang" id="barangSelect" 
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                        required onchange="updateStokInfo()" disabled>
                    <option value="">Pilih Barang</option>
                    <!-- Options akan diisi via JavaScript -->
                </select>
                <div id="stokInfo" class="text-xs text-gray-500 mt-1 hidden">
                    Stok tersedia: <span id="stokValue" class="font-medium">0</span>
                </div>
                <div id="barangHelp" class="text-xs text-gray-500 mt-1">
                    Pilih ruangan terlebih dahulu untuk melihat daftar barang
                </div>
            </div>
            
            <!-- Jumlah -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Jumlah
                </label>
                <div class="relative">
                    <input type="number" name="jumlah" id="jumlahInput" min="1" value="{{ old('jumlah') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 pr-10 transition-all duration-200 shadow-sm" 
                           required oninput="validateStok()" disabled>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 text-sm">unit</span>
                    </div>
                </div>
                <div id="stokWarning" class="text-xs text-red-500 mt-1 hidden flex items-start">
                    <i class="fas fa-exclamation-circle mr-1 mt-0.5"></i>
                    <span>Jumlah melebihi stok tersedia!</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Masukkan jumlah barang yang keluar</p>
            </div>
            
            <!-- Tanggal Keluar -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tanggal Keluar
                </label>
                <div class="relative">
                    <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar', date('Y-m-d')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-calendar text-gray-400"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Tanggal barang dikeluarkan</p>
            </div>
            
            <!-- Tujuan -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tujuan
                </label>
                <input type="text" name="tujuan" value="{{ old('tujuan') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Tujuan barang" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan tujuan pengeluaran barang</p>
            </div>
            
            <!-- Penanggung Jawab -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Penanggung Jawab
                </label>
                <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Nama penanggung jawab" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama yang bertanggung jawab</p>
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
            <button type="button" onclick="resetForm()" 
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                <i class="fas fa-times mr-2"></i> Batal
            </button>
            <button type="submit" id="submitButton"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm flex items-center">
                <i class="fas fa-paper-plane mr-2"></i> Simpan Data
            </button>
        </div>
    </form>
</div>

<script>
    // Data barang berdasarkan ruangan (akan diisi via AJAX)
    let barangByRuangan = {!! json_encode($barangByRuangan) !!};

    // Fungsi untuk memuat barang berdasarkan ruangan yang dipilih
    function loadBarangByRuangan() {
        const ruanganSelect = document.getElementById('ruanganSelect');
        const barangSelect = document.getElementById('barangSelect');
        const jumlahInput = document.getElementById('jumlahInput');
        const stokInfo = document.getElementById('stokInfo');
        const barangHelp = document.getElementById('barangHelp');
        
        const ruanganId = ruanganSelect.value;
        
        // Reset form barang
        barangSelect.innerHTML = '<option value="">Pilih Barang</option>';
        barangSelect.disabled = true;
        jumlahInput.disabled = true;
        jumlahInput.value = '';
        stokInfo.classList.add('hidden');
        
        if (ruanganId) {
            // Cari barang berdasarkan ruangan
            const barangList = barangByRuangan[ruanganId] || [];
            
            if (barangList.length > 0) {
                // Tambahkan opsi barang
                barangList.forEach(barang => {
                    const option = document.createElement('option');
                    option.value = barang.id;
                    option.textContent = `${barang.nama} (Stok: ${barang.total})`;
                    option.setAttribute('data-stok', barang.total);
                    barangSelect.appendChild(option);
                });
                
                barangSelect.disabled = false;
                barangHelp.textContent = 'Pilih barang yang akan dikeluarkan';
                barangHelp.className = 'text-xs text-gray-500 mt-1';
            } else {
                barangHelp.textContent = 'Tidak ada barang di ruangan ini';
                barangHelp.className = 'text-xs text-yellow-600 mt-1';
            }
        } else {
            barangHelp.textContent = 'Pilih ruangan terlebih dahulu untuk melihat daftar barang';
            barangHelp.className = 'text-xs text-gray-500 mt-1';
        }
        
        validateStok();
    }

    // Fungsi untuk update info stok
    function updateStokInfo() {
        const select = document.getElementById('barangSelect');
        const stokInfo = document.getElementById('stokInfo');
        const stokValue = document.getElementById('stokValue');
        const jumlahInput = document.getElementById('jumlahInput');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            const stok = selectedOption.getAttribute('data-stok');
            stokValue.textContent = stok;
            stokInfo.classList.remove('hidden');
            jumlahInput.disabled = false;
            validateStok();
        } else {
            stokInfo.classList.add('hidden');
            jumlahInput.disabled = true;
            jumlahInput.value = '';
        }
    }
    
    // Fungsi validasi stok
    function validateStok() {
        const select = document.getElementById('barangSelect');
        const jumlahInput = document.getElementById('jumlahInput');
        const stokWarning = document.getElementById('stokWarning');
        const submitButton = document.getElementById('submitButton');
        
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value && jumlahInput.value) {
            const stok = parseInt(selectedOption.getAttribute('data-stok'));
            const jumlah = parseInt(jumlahInput.value);
            
            if (jumlah > stok) {
                stokWarning.classList.remove('hidden');
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                stokWarning.classList.add('hidden');
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        } else {
            stokWarning.classList.add('hidden');
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
    
    // Fungsi reset form
    function resetForm() {
        document.getElementById('ruanganSelect').value = '';
        document.getElementById('barangSelect').innerHTML = '<option value="">Pilih Barang</option>';
        document.getElementById('barangSelect').disabled = true;
        document.getElementById('jumlahInput').value = '';
        document.getElementById('jumlahInput').disabled = true;
        document.getElementById('stokInfo').classList.add('hidden');
        document.getElementById('stokWarning').classList.add('hidden');
        document.getElementById('barangHelp').textContent = 'Pilih ruangan terlebih dahulu untuk melihat daftar barang';
        document.getElementById('barangHelp').className = 'text-xs text-gray-500 mt-1';
        
        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    // Validasi saat form akan disubmit
    document.querySelector('form').addEventListener('submit', function(e) {
        const ruanganSelect = document.getElementById('ruanganSelect');
        const barangSelect = document.getElementById('barangSelect');
        const jumlahInput = document.getElementById('jumlahInput');
        
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
        
        const selectedOption = barangSelect.options[barangSelect.selectedIndex];
        if (selectedOption.value && jumlahInput.value) {
            const stok = parseInt(selectedOption.getAttribute('data-stok'));
            const jumlah = parseInt(jumlahInput.value);
            
            if (jumlah > stok) {
                e.preventDefault();
                alert('Jumlah barang keluar melebihi stok yang tersedia!');
            }
        }
    });
    
    // Datepicker default to today
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.querySelector('input[name="tanggal_keluar"]').value) {
            document.querySelector('input[name="tanggal_keluar"]').valueAsDate = new Date();
        }
        
        // Inisialisasi jika ada old input
        const oldRuanganId = "{{ old('id_ruangan') }}";
        if (oldRuanganId) {
            document.getElementById('ruanganSelect').value = oldRuanganId;
            loadBarangByRuangan();
            
            // Set barang jika ada old input
            const oldBarangId = "{{ old('id_barang') }}";
            if (oldBarangId) {
                setTimeout(() => {
                    document.getElementById('barangSelect').value = oldBarangId;
                    updateStokInfo();
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Keluar</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penanggung Jawab</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($barangKeluar as $item)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $item->barang->nama }}</div>
                <div class="text-sm text-gray-500">{{ $item->barang->kategori }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->barang->ruangan->nama }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->jumlah }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->tanggal_keluar->format('d M Y') }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->tujuan }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->penanggung_jawab }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <form action="{{ route('barang_keluar.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data barang keluar</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $barangKeluar->links() }}
</div>
@endsection