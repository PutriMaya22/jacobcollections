<?php

namespace App\Imports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenjualanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new DataPenjualan([
            'tanggal' => $row['tanggal'],
            'total_penjualan' => $row['total_penjualan'],
            'total_pesanan' => $row['total_pesanan'],
            'penjualan_perpesanan' => $row['penjualan_perpesanan'] ?? 0,
        ]);
    }
}