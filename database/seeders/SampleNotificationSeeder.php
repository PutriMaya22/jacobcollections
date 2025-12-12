<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ruangan;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Carbon\Carbon;

class SampleNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample ruangan
        $ruangan = Ruangan::create([
            'nama' => 'Gudang Utama',
            'keterangan' => 'Gudang penyimpanan utama untuk peralatan kantor'
        ]);

        $ruanganIT = Ruangan::create([
            'nama' => 'Ruang IT',
            'keterangan' => 'Ruang khusus peralatan IT dan elektronik'
        ]);

        // Create sample barang dengan berbagai kondisi stok
        $barangs = [
            // Stok normal
            [
                'nama' => 'Laptop Dell Latitude',
                'total' => 15,
                'kategori' => 'Elektronik',
                'kondisi' => 'baik',
                'status' => 'tersedia'
            ],
            // Stok rendah (akan muncul di notifikasi)
            [
                'nama' => 'Printer Canon',
                'total' => 3,
                'kategori' => 'Elektronik', 
                'kondisi' => 'baik',
                'status' => 'tersedia'
            ],
            [
                'nama' => 'Mouse Wireless',
                'total' => 2,
                'kategori' => 'Aksesoris',
                'kondisi' => 'baik',
                'status' => 'tersedia'
            ],
            // Stok habis (akan muncul di notifikasi)
            [
                'nama' => 'Keyboard Mechanical',
                'total' => 0,
                'kategori' => 'Aksesoris',
                'kondisi' => 'baik',
                'status' => 'tersedia'
            ],
            // Barang rusak (akan muncul di notifikasi)
            [
                'nama' => 'Proyektor Epson',
                'total' => 5,
                'kategori' => 'Elektronik',
                'kondisi' => 'rusak_ringan',
                'status' => 'perbaikan'
            ],
            [
                'nama' => 'Monitor LED 24 inch',
                'total' => 8,
                'kategori' => 'Elektronik',
                'kondisi' => 'rusak_berat',
                'status' => 'perbaikan'
            ],
            // Barang normal lainnya
            [
                'nama' => 'Kursi Kantor Ergonomis',
                'total' => 25,
                'kategori' => 'Furniture',
                'kondisi' => 'baik',
                'status' => 'tersedia'
            ],
            [
                'nama' => 'Meja Kerja',
                'total' => 20,
                'kategori' => 'Furniture',
                'kondisi' => 'baik',
                'status' => 'tersedia'
            ]
        ];

        $createdBarangs = [];
        foreach ($barangs as $barangData) {
            $barangData['id_ruangan'] = $barangData['kategori'] == 'Elektronik' ? $ruanganIT->id : $ruangan->id;
            $barangData['tanggal_pembelian'] = Carbon::now()->subMonths(rand(1, 12));
            
            $createdBarangs[] = Barang::create($barangData);
        }

        // Create sample barang masuk (24 jam terakhir untuk notifikasi)
        $barangMasuks = [
            [
                'id_barang' => $createdBarangs[0]->id, // Laptop Dell
                'jumlah' => 5,
                'tanggal_masuk' => Carbon::today(),
                'supplier' => 'PT. Teknologi Maju',
                'keterangan' => 'Pembelian batch baru untuk divisi marketing',
                'penerima' => 'Ahmad Budiman',
                'created_at' => Carbon::now()->subHours(2)
            ],
            [
                'id_barang' => $createdBarangs[1]->id, // Printer Canon
                'jumlah' => 2,
                'tanggal_masuk' => Carbon::today(),
                'supplier' => 'CV. Office Solution',
                'keterangan' => 'Penambahan stok printer untuk cabang baru',
                'penerima' => 'Siti Nurhaliza',
                'created_at' => Carbon::now()->subMinutes(30)
            ],
            [
                'id_barang' => $createdBarangs[6]->id, // Kursi Kantor
                'jumlah' => 10,
                'tanggal_masuk' => Carbon::today(),
                'supplier' => 'PT. Furniture Prima',
                'keterangan' => 'Kursi ergonomis untuk kenyamanan karyawan',
                'penerima' => 'Budi Santoso',
                'created_at' => Carbon::now()->subHours(5)
            ]
        ];

        foreach ($barangMasuks as $masukData) {
            // Temporary disable auto increment to avoid double counting
            $barang = Barang::find($masukData['id_barang']);
            $barang->decrement('total', $masukData['jumlah']); // Temporary reduce first
            
            BarangMasuk::create($masukData); // This will auto increment back
        }

        // Create sample barang keluar (24 jam terakhir untuk notifikasi)
        $barangKeluars = [
            [
                'id_barang' => $createdBarangs[0]->id, // Laptop Dell
                'jumlah' => 3,
                'tanggal_keluar' => Carbon::today(),
                'tujuan' => 'Kantor Cabang Bekasi',
                'keterangan' => 'Transfer untuk tim baru di cabang Bekasi',
                'penanggung_jawab' => 'Manager IT',
                'created_at' => Carbon::now()->subHours(1)
            ],
            [
                'id_barang' => $createdBarangs[4]->id, // Proyektor
                'jumlah' => 1,
                'tanggal_keluar' => Carbon::yesterday(),
                'tujuan' => 'Ruang Meeting Lt. 3',
                'keterangan' => 'Untuk presentasi klien besar',
                'penanggung_jawab' => 'Kepala Divisi Sales',
                'created_at' => Carbon::now()->subHours(10)
            ],
            [
                'id_barang' => $createdBarangs[6]->id, // Kursi Kantor
                'jumlah' => 5,
                'tanggal_keluar' => Carbon::today(),
                'tujuan' => 'Ruang Workshop Lt. 2',
                'keterangan' => 'Setup ruang workshop untuk pelatihan karyawan',
                'penanggung_jawab' => 'HRD Manager',
                'created_at' => Carbon::now()->subHours(3)
            ]
        ];

        foreach ($barangKeluars as $keluarData) {
            BarangKeluar::create($keluarData);
        }

        echo "✅ Sample data untuk sistem notifikasi berhasil dibuat!\n";
        echo "📊 Data yang dibuat:\n";
        echo "   - " . count($createdBarangs) . " barang dengan berbagai kondisi stok\n";
        echo "   - " . count($barangMasuks) . " transaksi barang masuk (24 jam terakhir)\n";
        echo "   - " . count($barangKeluars) . " transaksi barang keluar (24 jam terakhir)\n";
        echo "   - Barang dengan stok rendah: " . Barang::stokRendah()->count() . "\n";
        echo "   - Barang dengan stok habis: " . Barang::stokHabis()->count() . "\n";
        echo "   - Barang rusak: " . Barang::barangRusak()->count() . "\n";
    }
}