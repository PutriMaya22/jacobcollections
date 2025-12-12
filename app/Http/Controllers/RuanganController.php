<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruangan::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $request->search . '%');
        }

        $ruangan = $query->paginate(10); // pagination biar rapi

        return view('data_ruangan.index', compact('ruangan'));
    }

    public function create()
    {
        return view('data_ruangan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'keterangan' => 'nullable'
        ]);

        Ruangan::create($request->all());
        return redirect()->route('data_ruangan.index')->with('success', 'Ruangan berhasil ditambahkan');
    }

    public function edit(Ruangan $data_ruangan)
    {
        return view('data_ruangan.edit', compact('data_ruangan'));
    }

    public function update(Request $request, Ruangan $data_ruangan)
    {
        $request->validate([
            'nama' => 'required',
            'keterangan' => 'nullable'
        ]);

        $data_ruangan->update($request->all());
        return redirect()->route('data_ruangan.index')->with('success', 'Ruangan berhasil diupdate');
    }

    public function destroy(Ruangan $data_ruangan)
    {
        $data_ruangan->delete();
        return redirect()->route('data_ruangan.index')->with('success', 'Ruangan berhasil dihapus');
    }

    // Read-only detail view
    public function show(Ruangan $data_ruangan)
    {
        return view('data_ruangan.show', ['data_ruangan' => $data_ruangan]);
    }
}
