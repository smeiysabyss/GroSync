<table>
    {{-- Baris 1: Judul utama --}}
    <tr>
        <td colspan="10"><strong>GROSYNC — Laporan Transaksi</strong></td>
    </tr>

    {{-- Baris 2: Periode --}}
    <tr>
        <td colspan="10">
            Periode:
            @if($dari && $sampai)
                {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
            @elseif($dari)
                Mulai {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }}
            @elseif($sampai)
                Sampai {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
            @else
                Semua periode
            @endif
            @if($status)
                | Status: {{ ucfirst($status) }}
            @endif
        </td>
    </tr>

    {{-- Baris 3: Tanggal cetak --}}
    <tr>
        <td colspan="10">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td>
    </tr>

    {{-- Baris 4: Kosong --}}
    <tr><td colspan="10"></td></tr>

    {{-- Baris 5: Header kolom --}}
    <tr>
        <td><strong>#</strong></td>
        <td><strong>No. Transaksi</strong></td>
        <td><strong>Kasir</strong></td>
        <td><strong>Pelanggan</strong></td>
        <td><strong>Jumlah Item</strong></td>
        <td><strong>Total (Rp)</strong></td>
        <td><strong>Uang Bayar (Rp)</strong></td>
        <td><strong>Kembalian (Rp)</strong></td>
        <td><strong>Status</strong></td>
        <td><strong>Waktu Transaksi</strong></td>
    </tr>

    {{-- Baris 6+: Data transaksi --}}
    @forelse($transaksis as $i => $trx)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $trx->nomor_unik }}</td>
        <td>{{ $trx->user->username ?? '-' }}</td>
        <td>{{ $trx->nama_pelanggan ?: '-' }}</td>
        <td>{{ $trx->detail->count() }}</td>
        <td>{{ $trx->total }}</td>
        <td>{{ $trx->uang_bayar }}</td>
        <td>{{ $trx->kembalian }}</td>
        <td>{{ ucfirst($trx->status) }}</td>
        <td>{{ \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y H:i') }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="10">Tidak ada data transaksi</td>
    </tr>
    @endforelse

    {{-- Baris kosong sebelum ringkasan --}}
    <tr><td colspan="10"></td></tr>

    {{-- Baris ringkasan --}}
    <tr>
        <td colspan="4"><strong>TOTAL</strong></td>
        <td><strong>{{ $transaksis->count() }} transaksi</strong></td>
        <td><strong>{{ $totalPendapatan }}</strong></td>
        <td colspan="4"></td>
    </tr>
</table>