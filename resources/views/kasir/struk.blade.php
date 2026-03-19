<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 11pt; padding: 10px; }
        hr { border: none; border-top: 1px dashed #999; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; font-size: 10pt; }
        .right  { text-align: right; }
        .small  { font-size: 9pt; }
        .bold   { font-weight: bold; }
        .center { text-align: center; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="center">
        <div class="bold" style="font-size:14pt;">GROSYNC</div>
        <div class="small">Jl. Contoh Alamat Toko No. 1, Kota</div>
        <div class="small">No. Telp: 08xxxxxxxxxx</div>
    </div>

    <hr>

    {{-- META TRANSAKSI --}}
    <table>
        <tr>
            <td class="small">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d-m-Y H:i') }}</td>
            <td class="small right">Kasir: {{ $transaksi->user->username ?? '-' }}</td>
        </tr>
        <tr>
            <td class="small">{{ $transaksi->nomor_unik }}</td>
            <td class="small right">{{ $transaksi->nama_pelanggan ?: '-' }}</td>
        </tr>
    </table>

    <hr>

    {{-- DETAIL PRODUK --}}
    @foreach($transaksi->detail as $i => $d)
    <table style="margin-bottom:4px;">
        <tr>
            <td class="bold" colspan="2">
                {{ $i + 1 }}. {{ $d->hargaProduk->produk->nama_produk }}
            </td>
        </tr>
        <tr>
            <td class="small">
                {{ $d->jumlah }}
                {{ $d->hargaProduk->unit->satuan }}
                x Rp {{ number_format($d->hargaProduk->harga, 0, ',', '.') }}
            </td>
            <td class="small right">
                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
            </td>
        </tr>
    </table>
    @endforeach

    <hr>

    {{-- TOTAL --}}
    <table>
        <tr>
            <td class="small">Total QTY</td>
            <td class="small right">{{ $transaksi->detail->sum('jumlah') }}</td>
        </tr>
        <tr>
            <td class="bold">Total</td>
            <td class="bold right">Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="small">Bayar (Cash)</td>
            <td class="small right">Rp {{ number_format($transaksi->uang_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="small">Kembali</td>
            <td class="small right">Rp {{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>

    <hr>

    <div class="center small">Terima kasih telah berbelanja!</div>

</body>
</html>