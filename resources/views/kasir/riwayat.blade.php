@extends('layouts.kasir')

@section('title', 'Riwayat Transaksi')

@push('styles')
<link href="{{ asset('css/kasir/riwayat.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- PAGE HEADER --}}
<div class="rw-page-header">
    <div>
        <div class="rw-page-title">Riwayat Transaksi Hari Ini</div>
        <div class="rw-page-subtitle">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
    </div>
</div>

{{-- STAT MINI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="rw-stat-card">
            <div class="rw-stat-icon rw-icon-blue">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div>
                <div class="rw-stat-label">Total Transaksi</div>
                <div class="rw-stat-value">{{ $totalTransaksi }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rw-stat-card">
            <div class="rw-stat-icon rw-icon-green">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="rw-stat-label">Total Pendapatan</div>
                <div class="rw-stat-value rw-stat-value--sm">
                    <span class="rw-currency">Rp</span>{{ number_format($pendapatanHariIni, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABEL RIWAYAT --}}
<div class="rw-card">
    <div class="rw-table-wrap">
        <table class="rw-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Kembali</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $i => $trx)
                <tr>
                    <td class="rw-td-no">{{ $i + 1 }}</td>
                    <td><span class="rw-nomor">{{ $trx->nomor_unik }}</span></td>
                    <td class="rw-td-sm">{{ $trx->nama_pelanggan ?: '-' }}</td>
                    <td class="rw-td-center">{{ $trx->detail->count() }} item</td>
                    <td class="rw-td-total">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                    <td class="rw-td-muted">Rp {{ number_format($trx->uang_bayar, 0, ',', '.') }}</td>
                    <td class="rw-td-muted">Rp {{ number_format($trx->kembalian, 0, ',', '.') }}</td>
                    <td>
                        <span class="rw-badge {{ $trx->status === 'selesai' ? 'rw-badge-green' : 'rw-badge-red' }}">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>
                    <td class="rw-td-waktu">
                        {{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            {{-- Lihat Detail --}}
                            <button class="rw-btn-icon rw-btn-detail"
                                    title="Lihat Detail"
                                    data-trx="{{ json_encode([
                                        'nomor'     => $trx->nomor_unik,
                                        'pelanggan' => $trx->nama_pelanggan ?: 'Umum',
                                        'total'     => $trx->total,
                                        'bayar'     => $trx->uang_bayar,
                                        'kembali'   => $trx->kembalian,
                                        'status'    => $trx->status,
                                        'waktu'     => \Carbon\Carbon::parse($trx->created_at)->format('H:i'),
                                        'items'     => $trx->detail->map(fn($d) => [
                                            'nama'     => $d->hargaProduk->produk->nama_produk ?? '-',
                                            'satuan'   => $d->hargaProduk->unit->satuan ?? '-',
                                            'jumlah'   => $d->jumlah,
                                            'subtotal' => $d->subtotal,
                                        ]),
                                    ]) }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            {{-- Cetak Ulang Struk --}}
                            <button class="rw-btn-icon rw-btn-struk"
                                    data-id="{{ $trx->id_transaksi }}"
                                    title="Cetak Ulang Struk">
                                <i class="bi bi-printer"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="rw-empty">
                            <div class="rw-empty-icon"><i class="bi bi-receipt"></i></div>
                            <div class="rw-empty-text">Belum ada transaksi hari ini</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============================================================
     MODAL DETAIL TRANSAKSI
     ============================================================ --}}
<div class="rw-modal-overlay" id="overlayDetail"></div>
<div class="rw-modal" id="modalDetail">
    <div class="rw-modal-header">
        <div>
            <div class="rw-modal-title">Detail Transaksi</div>
            <div class="rw-modal-subtitle" id="detailNomor">—</div>
        </div>
        <button class="rw-modal-close" id="btnTutupDetail">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="rw-modal-body">

        <div class="rw-detail-info-grid" id="detailInfoGrid"></div>

        <div class="rw-detail-section-title">Daftar Produk</div>
        <div class="rw-detail-table-wrap">
            <table class="rw-detail-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Satuan</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detailItems"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="rw-detail-total-label">Total</td>
                        <td class="rw-detail-total-value text-end" id="detailTotal"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>

{{-- iframe cetak struk --}}
<iframe id="iframeStruk" style="display:none;"></iframe>

@endsection

@push('scripts')
<script src="{{ asset('js/kasir/riwayat.js') }}"></script>
@endpush