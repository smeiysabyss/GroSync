@extends('layouts.owner')

@section('title', 'Laporan Transaksi')

@push('styles')
<link href="{{ asset('css/owner/laporan.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="ow-page-header">
    <div>
        <div class="ow-page-title">Laporan Transaksi</div>
        <div class="ow-page-subtitle">Semua riwayat transaksi dari seluruh kasir</div>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        {{-- Summary badges --}}
        <div class="ow-summary-badge">
            <div class="ow-summary-label">Total Transaksi</div>
            <div class="ow-summary-value">{{ $totalTransaksi }}</div>
        </div>
        <div class="ow-summary-badge">
            <div class="ow-summary-label">Total Pendapatan</div>
            <div class="ow-summary-value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        </div>

        {{-- Tombol Export Excel --}}
        <a href="{{ route('owner.laporan.export', request()->only(['dari','sampai','status','kasir'])) }}"
           class="ow-btn-export">
            <i class="bi bi-file-earmark-excel-fill me-1"></i>
            Unduh Excel
        </a>
    </div>
</div>

{{-- FILTER --}}
<div class="ow-card mb-4">
    <div class="ow-card-body">
        <form method="GET" action="{{ route('owner.laporan') }}" id="formFilterLaporan" class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label ow-filter-label">Dari Tanggal</label>
                <input type="date" name="dari" id="filterDari"
                       class="form-control ow-filter-input"
                       value="{{ request('dari') }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label ow-filter-label">Sampai Tanggal</label>
                <input type="date" name="sampai" id="filterSampai"
                       class="form-control ow-filter-input"
                       value="{{ request('sampai') }}">
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label ow-filter-label">Status</label>
                <select name="status" id="filterStatus" class="form-select ow-filter-select">
                    <option value="">Semua</option>
                    <option value="selesai"    {{ request('status') === 'selesai'    ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-2">
                <label class="form-label ow-filter-label">Kasir</label>
                <select name="kasir" id="filterKasir" class="form-select ow-filter-select">
                    <option value="">Semua Kasir</option>
                    @foreach($kasirs as $k)
                    <option value="{{ $k->id }}" {{ request('kasir') == $k->id ? 'selected' : '' }}>
                        {{ $k->username }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn ow-btn-filter w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['dari','sampai','status','kasir']))
                <a href="{{ route('owner.laporan') }}" class="btn ow-btn-reset">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>

            {{-- Shortcut tanggal --}}
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="ow-shortcut-label">Cepat:</span>
                    <a href="{{ route('owner.laporan', ['dari' => now()->format('Y-m-d'), 'sampai' => now()->format('Y-m-d')]) }}"
                       class="ow-shortcut-btn {{ request('dari') == now()->format('Y-m-d') && request('sampai') == now()->format('Y-m-d') ? 'active' : '' }}">
                        Hari ini
                    </a>
                    <a href="{{ route('owner.laporan', ['dari' => now()->startOfWeek()->format('Y-m-d'), 'sampai' => now()->format('Y-m-d')]) }}"
                       class="ow-shortcut-btn">
                        Minggu ini
                    </a>
                    <a href="{{ route('owner.laporan', ['dari' => now()->startOfMonth()->format('Y-m-d'), 'sampai' => now()->format('Y-m-d')]) }}"
                       class="ow-shortcut-btn">
                        Bulan ini
                    </a>
                    <a href="{{ route('owner.laporan', ['dari' => now()->startOfYear()->format('Y-m-d'), 'sampai' => now()->format('Y-m-d')]) }}"
                       class="ow-shortcut-btn">
                        Tahun ini
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TABEL --}}
<div class="ow-card">
    <div class="ow-table-wrap">
        <table class="ow-table">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th>No. Transaksi</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Bayar</th>
                    <th>Kembali</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $i => $trx)
                <tr>
                    <td class="ow-td-no">{{ $transaksis->firstItem() + $i }}</td>
                    <td class="ow-td-nomor">{{ $trx->nomor_unik }}</td>
                    <td class="ow-td-sm">{{ $trx->user->username ?? '-' }}</td>
                    <td class="ow-td-muted">{{ $trx->nama_pelanggan ?: '-' }}</td>
                    <td class="ow-td-sm">{{ $trx->detail->count() }} item</td>
                    <td class="ow-td-total">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                    <td class="ow-td-muted">Rp {{ number_format($trx->uang_bayar, 0, ',', '.') }}</td>
                    <td class="ow-td-muted">Rp {{ number_format($trx->kembalian, 0, ',', '.') }}</td>
                    <td>
                        <span class="ow-badge {{ $trx->status === 'selesai' ? 'ow-badge-green' : 'ow-badge-red' }}">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>
                    <td class="ow-td-waktu">{{ \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="ow-empty">
                            <div class="ow-empty-icon"><i class="bi bi-receipt-cutoff"></i></div>
                            <div class="ow-empty-text">Tidak ada transaksi ditemukan</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transaksis->hasPages())
    <div class="ow-pagination">
        {{ $transaksis->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/owner/laporan.js') }}"></script>
@endpush