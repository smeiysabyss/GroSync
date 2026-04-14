<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #3a6b1a;
        }
        .header p {
            margin: 5px 0;
            color: #6b7280;
        }
        .section-title {
            background: #e8f5d9;
            padding: 8px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #3a6b1a;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .summary-table td {
            border: none;
            padding: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GROSYNC</h1>
        <p>Laporan Laba</p>
        <p>Periode: {{ $periode }} ({{ $tanggal_mulai }} - {{ $tanggal_selesai }})</p>
    </div>

    <div class="section-title"> RINGKASAN</div>
    <table class="summary-table">
        <tr>
            <td width="30%">Total Pendapatan</td>
            <td width="70%" class="text-right"><strong>Rp {{ number_format($total_pendapatan, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Total HPP (Harga Pokok)</td>
            <td class="text-right">Rp {{ number_format($total_hpp, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Laba Bersih</strong></td>
            <td class="text-right"><strong style="color: #3a6b1a;">Rp {{ number_format($laba_bersih, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="section-title">📋 DETAIL LABA PER PRODUK</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Satuan</th>
                <th class="text-right">Terjual</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">HPP</th>
                <th class="text-right">Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laba_per_produk as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item['nama_produk'] }}</td>
                <td>{{ $item['satuan'] }}</td>
                <td class="text-right">{{ number_format($item['jumlah']) }}</td>
                <td class="text-right">Rp {{ number_format($item['pendapatan'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['hpp'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['laba'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada transaksi dalam periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ $tanggal_export }}
    </div>
</body>
</html>