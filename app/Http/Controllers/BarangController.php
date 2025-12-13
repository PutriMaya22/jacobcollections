<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $kategori = $request->kategori;

        $query = Barang::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('kategori', 'like', '%' . $search . '%')
                  ->orWhere('harga', 'like', '%' . $search . '%');
        }

        if ($request->filled('kategori') && $kategori !== 'semua') {
            $query->where('kategori', $kategori);
        }

        $barang = $query->get();
        $kategoriList = $this->getKategoriList();

        return view('data_barang.index', compact(
            'barang',
            'search',
            'kategori',
            'kategoriList'
        ));
    }

    public function create()
    {
        $kategoriList = $this->getKategoriList();
        return view('data_barang.create', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga'    => 'required|string|max:100'
        ]);

        Barang::create($request->all());

        return redirect()
            ->route('data_barang.index')
            ->with('success', 'Data barang berhasil ditambahkan');
    }

    public function edit(Barang $data_barang)
    {
        $kategoriList = $this->getKategoriList();
        return view('data_barang.edit', compact('data_barang', 'kategoriList'));
    }

    public function update(Request $request, Barang $data_barang)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga'    => 'required|string|max:100'
        ]);

        $data_barang->update($request->all());

        return redirect()
            ->route('data_barang.index')
            ->with('success', 'Data barang berhasil diupdate');
    }

    public function destroy(Barang $data_barang)
    {
        $data_barang->delete();

        return redirect()
            ->route('data_barang.index')
            ->with('success', 'Data barang berhasil dihapus');
    }

    public function show(Barang $data_barang)
    {
        return view('data_barang.show', compact('data_barang'));
    }

    private function getKategoriList()
    {
        return [
            'Pakaian'   => 'Pakaian',
            'Aksesoris' => 'Aksesoris',
            'Kosmetik'  => 'Kosmetik',
            'Elektronik'=> 'Elektronik'
        ];
    }
}
