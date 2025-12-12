<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        // Simpan keyword pencarian untuk dikirim ke view
        $search = $request->input('search');
        $kategori = $request->input('kategori'); // Filter kategori

        // Mulai query barang dengan relasi 'ruangan'
        $query = Barang::with('ruangan');

        // Jika ada input pencarian
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%')
                  ->orWhere('nomor_seri', 'like', '%' . $search . '%');
            });
        }

        // Filter berdasarkan kategori jika dipilih
        if ($request->filled('kategori') && $request->kategori != 'semua') {
            $query->where('kategori', $request->kategori);
        }

        // Ambil data hasil pencarian atau semua barang
        $barang = $query->get();

        // Daftar kategori untuk dropdown filter
        $kategoriList = $this->getKategoriList();

        // Kirim data + search + kategori ke view
        return view('data_barang.index', compact('barang', 'search', 'kategori', 'kategoriList'));
    }

    public function create()
    {
        $ruangan = Ruangan::all();
        $kategoriList = $this->getKategoriList();
        return view('data_barang.create', compact('ruangan', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ruangan' => 'required|exists:ruangans,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama' => 'required',
            'total' => 'required|integer|min:0',
            'kategori' => 'required',
            'tanggal_pembelian' => 'required|date',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'status' => 'required|in:tersedia,dipinjam,perbaikan'
        ]);

        $data = $request->except('foto');
        
        // Generate nomor seri otomatis
        $data['nomor_seri'] = $this->generateNomorSeri($request->kategori);
        
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('barang', 'public');
        }

        Barang::create($data);
        return redirect()->route('data_barang.index')->with('success', 'Barang berhasil ditambahkan');
    }

    public function edit(Barang $data_barang)
    {
        $ruangan = Ruangan::all();
        $kategoriList = $this->getKategoriList();
        return view('data_barang.edit', compact('data_barang', 'ruangan', 'kategoriList'));
    }

    public function update(Request $request, Barang $data_barang)
    {
        $request->validate([
            'id_ruangan' => 'required|exists:ruangans,id',
            'nomor_seri' => 'nullable|string|unique:barangs,nomor_seri,' . $data_barang->id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama' => 'required',
            'total' => 'required|integer|min:0',
            'kategori' => 'required',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'tanggal_pembelian' => 'required|date',
            'status' => 'required|in:tersedia,dipinjam,perbaikan'
        ]);

        $data = $request->except('foto');
        
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($data_barang->foto) {
                Storage::disk('public')->delete($data_barang->foto);
            }
            $data['foto'] = $request->file('foto')->store('barang', 'public');
        }

        $data_barang->update($data);
        return redirect()->route('data_barang.index')->with('success', 'Barang berhasil diupdate');
    }

    public function destroy(Barang $data_barang)
    {
        if ($data_barang->foto) {
            Storage::disk('public')->delete($data_barang->foto);
        }
        $data_barang->delete();
        return redirect()->route('data_barang.index')->with('success', 'Barang berhasil dihapus');
    }
    
    /**
     * Generate nomor seri otomatis berdasarkan kategori
     */
    private function generateNomorSeri($kategori)
    {
        // Mapping kategori ke kode
        $kodeKategori = [
            'Router & Switch' => 'RTW',
            'Access Point' => 'ACP',
            'Network Cable' => 'CBL',
            'Network Tool' => 'NTL',
            'Server' => 'SVR',
            'Elektronik' => 'ELT',
            'Furniture' => 'FTR',
            'ATK' => 'ATK'
        ];
        
        // Default kode jika kategori tidak ditemukan
        $kode = $kodeKategori[$kategori] ?? 'BRG';
        
        // Cari nomor seri terakhir dengan kategori yang sama
        $lastItem = Barang::where('kategori', $kategori)
                         ->orderBy('id', 'desc')
                         ->first();
        
        // Jika ada item sebelumnya, increment nomor
        if ($lastItem && $lastItem->nomor_seri) {
            $lastNumber = (int) substr($lastItem->nomor_seri, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // Format nomor dengan leading zeros
        return $kode . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Daftar kategori barang
     */
    private function getKategoriList()
    {
        return [
            'Router & Switch' => 'Router & Switch',
            'Access Point' => 'Access Point',
            'Network Cable' => 'Network Cable',
            'Network Tool' => 'Network Tool',
            'Server' => 'Server',
            'Elektronik' => 'Elektronik',
            'Furniture' => 'Furniture',
            'ATK' => 'ATK'
        ];
    }

    // Read-only detail view
    public function show(Barang $data_barang)
    {
        return view('data_barang.show', ['data_barang' => $data_barang]);
    }
}