@extends('operasional.layout')

@section('title', 'Peminjaman Barang')
@section('chart-label', 'Peminjaman per Bulan')

@section('form')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-100">
    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
        <i class="fas fa-hand-holding text-blue-500 mr-2"></i> Form Peminjaman Barang
    </h3>
    
    <form action="{{ route('peminjaman.store') }}" method="POST" class="space-y-4">
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
                <p class="text-xs text-gray-500 mt-1">Masukkan jumlah barang yang dipinjam</p>
            </div>
            
            <!-- Peminjam -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Peminjam
                </label>
                <input type="text" name="peminjam" value="{{ old('peminjam') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Nama peminjam" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama peminjam</p>
            </div>
            
            <!-- Tanggal Pinjam -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Tanggal Pinjam
                </label>
                <div class="relative">
                    <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                           required>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-calendar text-gray-400"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Tanggal peminjaman barang</p>
            </div>

            <!-- Batas Waktu Pengembalian -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    Batas Waktu Pengembalian
                </label>
                <div class="relative">
                    <input type="date" name="batas_waktu" value="{{ old('batas_waktu') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-calendar text-gray-400"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Opsional: batas akhir pengembalian</p>
            </div>
            
            <!-- Penanggung Jawab -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Penanggung Jawab
                </label>
                <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" 
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                       placeholder="Nama penanggung jawab" required>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama penanggung jawab</p>
            </div>
            
            <!-- Keperluan -->
            <div class="space-y-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Keperluan
                </label>
                <textarea name="keperluan" rows="3" 
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 px-4 transition-all duration-200 shadow-sm" 
                          placeholder="Tujuan peminjaman" required>{{ old('keperluan') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Jelaskan keperluan peminjaman</p>
            </div>
        </div>
        
        <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
            <button type="button" onclick="resetForm()" 
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                <i class="fas fa-times mr-2"></i> Batal
            </button>
            <button type="submit" id="submitButton"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm flex items-center">
                <i class="fas fa-paper-plane mr-2"></i> Simpan Peminjaman
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
                barangHelp.textContent = 'Pilih barang yang akan dipinjam';
                barangHelp.className = 'text-xs text-gray-500 mt-1';
            } else {
                barangHelp.textContent = 'Tidak ada barang tersedia di ruangan ini';
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
                alert('Jumlah barang pinjam melebihi stok yang tersedia!');
            }
        }
    });
    
    // Datepicker default to today
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.querySelector('input[name="tanggal_pinjam"]').value) {
            document.querySelector('input[name="tanggal_pinjam"]').valueAsDate = new Date();
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Waktu</th>
            <!-- Hanya admin yang bisa melihat kolom aksi -->
            @if(auth()->user()->role === 'admin')
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            @endif
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($peminjaman as $item)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $item->barang->nama }}</div>
                <div class="text-sm text-gray-500">{{ $item->barang->kategori }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->barang->ruangan->nama }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->peminjam }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->jumlah }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->tanggal_pinjam->format('d M Y') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 rounded-full text-xs 
                    @if($item->status == 'dikembalikan') bg-green-100 text-green-800
                    @elseif($item->status == 'terlambat') bg-red-100 text-red-800
                    @else bg-blue-100 text-blue-800 @endif">
                    {{ ucfirst($item->status) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $item->batas_waktu ? \Carbon\Carbon::parse($item->batas_waktu)->format('d M Y') : '-' }}
            </td>
            <!-- Hanya admin yang bisa melihat tombol aksi -->
            @if(auth()->user()->role === 'admin')
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    @if($item->status == 'dipinjam')
                    <form action="{{ route('peminjaman.kembali', $item->id) }}" method="POST" onsubmit="return confirm('Apakah barang sudah dikembalikan?')">
                        @csrf
                        <button type="submit" class="text-green-500 hover:text-green-700" title="Kembalikan Barang">
                            <i class="fas fa-undo"></i>
                        </button>
                    </form>
                    @endif
                    <!-- Tombol hapus dihilangkan -->
                </div>
            </td>
            @endif
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data peminjaman</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $peminjaman->links() }}
</div>
@endsection