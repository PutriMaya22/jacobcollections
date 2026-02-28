<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Prediksi Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f0f0f0; }
        .error-kecil { color: green; }
        .error-besar { color: red; }
    </style>
</head>

<body>

    <h2>Riwayat Prediksi Penjualan</h2>

    <h3>Grafik Prediksi vs Aktual</h3>
    <img src="{{ $chartUrl }}" width="100%" style="margin-bottom:20px;">

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Hasil Prediksi</th>
                <th>Penjualan Aktual</th>
                <th>Error</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPrediksi as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>Rp {{ number_format($item->hasil_prediksi, 0, ',', '.') }}</td>
                <td>
                    @if($item->penjualan_aktual)
                        Rp {{ number_format($item->penjualan_aktual, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="{{ $item->error <= 100000 ? 'error-kecil' : 'error-besar' }}">
                    @if($item->error)
                        Rp {{ number_format($item->error, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>