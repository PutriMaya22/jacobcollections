<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Imports\PenjualanImport;
use Maatwebsite\Excel\Facades\Excel;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penjualan::query();

        if ($request->filled('search')) {
            $query->where('tanggal', 'like', '%' . $request->search . '%')
                  ->orWhere('total_penjualan', 'like', '%' . $request->search . '%');
        }

        $penjualan = $query->orderBy('tanggal', 'desc')->paginate(10);

        return view('data_penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        return view('data_penjualan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'total_penjualan' => 'required|string|max:100',
            'total_pesanan' => 'required|integer|min:1',
            'penjualan_perpesanan' => 'nullable|string|max:255',
        ]);

        Penjualan::create($request->all());

        return redirect()
            ->route('data_penjualan.index')
            ->with('success', 'Data penjualan berhasil ditambahkan');
    }

    public function edit(Penjualan $data_penjualan)
    {
        return view('data_penjualan.edit', compact('data_penjualan'));
    }

    public function update(Request $request, Penjualan $data_penjualan)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'total_penjualan' => 'required|string|max:100',
            'total_pesanan' => 'required|integer|min:1',
            'penjualan_perpesanan' => 'nullable|string|max:255',
        ]);

        $data_penjualan->update($request->all());

        return redirect()
            ->route('data_penjualan.index')
            ->with('success', 'Data penjualan berhasil diupdate');
    }

    public function destroy(Penjualan $data_penjualan)
    {
        $data_penjualan->delete();

        return redirect()
            ->route('data_penjualan.index')
            ->with('success', 'Data penjualan berhasil dihapus');
    }
    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv'
    ]);

    Excel::import(new PenjualanImport, $request->file('file'));

    return redirect()->route('data_penjualan.index')
        ->with('success', 'Data berhasil diimport!');
}

    public function show(Penjualan $data_penjualan)
    {
        return view('data_penjualan.show', compact('data_penjualan'));
    }

}
