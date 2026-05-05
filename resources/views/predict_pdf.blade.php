<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Prediksi Penjualan</title>
    <style>
        @page {
            margin: 18px 16px 20px 16px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2f3640;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .container {
            width: 100%;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2d2d2d;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            color: #7b7f87;
            margin-bottom: 8px;
        }

        .line-separator {
            border-top: 2px solid #5a6478;
            margin-bottom: 12px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 16px;
            background: #4f596d;
        }

        .summary-table td {
            text-align: center;
            padding: 12px 8px;
            border-right: 1px solid rgba(255,255,255,0.15);
            color: #fff;
        }

        .summary-table td:last-child {
            border-right: none;
        }

        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #dce2ea;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.2;
        }

        .section-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #30343b;
            margin: 8px 0 8px;
        }

        .chart-wrap {
            width: 100%;
            text-align: center;
            margin-bottom: 14px;
        }

        .chart-wrap img {
            width: 100%;
            max-height: 320px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 6px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #bfc5cf;
            padding: 6px 5px;
        }

        .data-table th {
            background: #d6dbe3;
            color: #2f3640;
            font-weight: bold;
            text-align: center;
        }

        .data-table td {
            background: #f8f9fb;
        }

        .green {
            color: #166534;
            font-weight: bold;
        }

        .orange {
            color: #b45309;
            font-weight: bold;
        }

        .red {
            color: #b91c1c;
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
        }

        .footer-note {
            border-top: 1px solid #d7dbe2;
            margin-top: 12px;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #8b9098;
            line-height: 1.5;
        }

        .status-cell {
            white-space: nowrap;
        }

        .small {
            font-size: 9px;
        }

        .w-no { width: 4%; }
        .w-tanggal { width: 12%; }
        .w-pesanan { width: 8%; }
        .w-prediksi { width: 16%; }
        .w-aktual { width: 14%; }
        .w-error { width: 12%; }
        .w-mape { width: 8%; }
        .w-rmse { width: 12%; }
        .w-r2 { width: 7%; }
        .w-status { width: 13%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Riwayat Prediksi Penjualan</div>
        <div class="subtitle">
            JacobCollections - Laporan prediksi vs aktual dengan metrik evaluasi statis
        </div>

        <div class="line-separator"></div>

        {{-- Ringkasan --}}
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Prediksi</div>
                    <div class="summary-value">Rp {{ number_format($totalPrediksi ?? 0, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Total Aktual</div>
                    <div class="summary-value">Rp {{ number_format($totalAktual ?? 0, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Rata-rata MAPE</div>
                    <div class="summary-value">{{ number_format($rataMape ?? 0, 2, '.', '.') }}%</div>
                </td>
                <td>
                    <div class="summary-label">Rata-rata RMSE</div>
                    <div class="summary-value">Rp {{ number_format($rataRmse ?? 0, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div class="summary-label">Rata-rata R²</div>
                    <div class="summary-value">{{ number_format($rataR2 ?? 0, 4, '.', '.') }}</div>
                </td>
            </tr>
        </table>

       {{-- Grafik --}}
@if(!empty($chartBase64))
<div class="section-title">Grafik Prediksi vs Aktual</div>
<div class="chart-wrap">
    <img src="{{ $chartBase64 }}" alt="Grafik Prediksi vs Aktual">
</div>
@endif

        {{-- Tabel Detail --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-no">No</th>
                    <th class="w-tanggal">Tanggal</th>
                    <th class="w-pesanan">Pesanan</th>
                    <th class="w-prediksi">Prediksi</th>
                    <th class="w-aktual">Aktual</th>
                    <th class="w-error">Error</th>
                    <th class="w-mape">MAPE</th>
                    <th class="w-rmse">RMSE</th>
                    <th class="w-r2">R²</th>
                    <th class="w-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataPrediksi as $index => $item)
                    @php
                        $aktual = $item->penjualan_aktual;
                        $prediksi = $item->hasil_prediksi ?? 0;
                        $error = $item->error;

                        if (is_null($aktual) || $aktual == 0 || $prediksi == 0) {
                            $status = 'Belum Update';
                            $statusClass = 'muted';
                            $statusIcon = '◷';
                        } else {
                            $persen = ($aktual / $prediksi) * 100;

                            if ($persen >= 100) {
                                $status = 'Tercapai';
                                $statusClass = 'green';
                                $statusIcon = '●';
                            } elseif ($persen >= 80) {
                                $status = 'Mendekati Target';
                                $statusClass = 'orange';
                                $statusIcon = '●';
                            } else {
                                $status = 'Jauh dari Target';
                                $statusClass = 'red';
                                $statusIcon = '●';
                            }
                        }
                    @endphp

                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                        </td>
                        <td class="text-center">{{ $item->total_pesanan ?? 0 }}</td>
                        <td class="text-right">Rp {{ number_format($prediksi, 0, ',', '.') }}</td>
                        <td class="text-right">
                            {{ !is_null($aktual) ? 'Rp ' . number_format($aktual, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ !is_null($error) ? 'Rp ' . number_format($error, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-center {{ !is_null($item->static_mape) ? 'green' : '' }}">
                            {{ !is_null($item->static_mape) ? number_format($item->static_mape, 2, '.', '.') . '%' : '-' }}
                        </td>
                        <td class="text-right">
                            {{ !is_null($item->static_rmse) ? 'Rp ' . number_format($item->static_rmse, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-center {{ !is_null($item->static_r_squared) ? 'green' : '' }}">
                            {{ !is_null($item->static_r_squared) ? number_format($item->static_r_squared, 4, '.', '.') : '-' }}
                        </td>
                        <td class="text-left status-cell {{ $statusClass }}">
                            {{ $statusIcon }} {{ $status }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer-note">
            <div>
                Keterangan:
                <span class="green">● Tercapai / Aktual Lebih Tinggi</span> |
                <span class="orange">● Mendekati Target</span> |
                <span class="red">● Jauh dari Target</span>
            </div>
            <div>
                MAPE = Mean Absolute Percentage Error |
                RMSE = Root Mean Square Error |
                R² = Koefisien determinasi
            </div>
            <div>
                Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
