@extends('layouts.owner')

@section('title', 'Dashboard')

@push('styles')
<link href="{{ asset('css/owner/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="ow-page-header">
    <div>
        <div class="ow-page-title">Selamat datang, {{ Auth::user()->username }} 👋</div>
        <div class="ow-page-subtitle">Berikut ringkasan toko hari ini.</div>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Transaksi Hari Ini --}}
    <div class="col-12 col-md-4">
        <div class="ow-stat-card">
            <div class="ow-stat-icon ow-stat-icon-blue">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="ow-stat-info">
                <div class="ow-stat-label">Transaksi Hari Ini</div>
                <div class="ow-stat-value">{{ $transaksiHariIni }}</div>
                <div class="ow-stat-sub">transaksi selesai</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Pendapatan Hari Ini --}}
    <div class="col-12 col-md-4">
        <div class="ow-stat-card">
            <div class="ow-stat-icon ow-stat-icon-amber">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="ow-stat-info">
                <div class="ow-stat-label">Pendapatan Hari Ini</div>
                <div class="ow-stat-value ow-stat-value--currency">
                    <span class="ow-stat-currency">Rp</span>{{ number_format($pendapatanHariIni, 0, ',', '.') }}
                </div>
                <div class="ow-stat-sub">total penjualan</div>
            </div>
        </div>
    </div>

    {{-- Card 3: Total Laba --}}
    <div class="col-12 col-md-4">
        <div class="ow-stat-card">
            <div class="ow-stat-icon ow-stat-icon-green">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="ow-stat-info">
                <div class="ow-stat-label">Total Laba</div>
                <div class="ow-stat-value ow-stat-value--currency">
                    <span class="ow-stat-currency">Rp</span>{{ number_format($totalLaba, 0, ',', '.') }}
                </div>
                <div class="ow-stat-sub">laba bersih keseluruhan</div>
            </div>
        </div>
    </div>
</div>

{{-- GRAFIK --}}
<div class="ow-card mb-4">
    <div class="ow-card-body">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <div style="font-size:0.95rem; font-weight:700; color:#1e3d0d;">Grafik Pendapatan</div>
                <div style="font-size:0.75rem; color:#9ca3af;">Klik periode untuk mengganti tampilan</div>
            </div>
            <div class="chart-period-tabs">
                <button class="chart-tab active" onclick="gantiPeriode('hari', this)">Harian</button>
                <button class="chart-tab" onclick="gantiPeriode('bulan', this)">Bulanan</button>
                <button class="chart-tab" onclick="gantiPeriode('tahun', this)">Tahunan</button>
            </div>
        </div>
        <canvas id="grafikPendapatan" height="90"></canvas>
    </div>
</div>

{{-- TRANSAKSI TERBARU --}}
<div class="ow-card">
    <div class="ow-card-body" style="padding-bottom: 0;">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div style="font-size:0.95rem; font-weight:700; color:#1e3d0d;">Transaksi Terbaru</div>
            <a href="{{ route('owner.laporan') }}" style="font-size:0.78rem; color:#3a6b1a; font-weight:600; text-decoration:none;">
                Lihat semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="ow-table-wrap">
        <table class="ow-table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksiTerbaru as $trx)
                <tr>
                    <td><span style="font-weight:600; color:#2d5a14;">{{ $trx->nomor_unik }}</span></td>
                    <td>{{ $trx->user->username ?? '-' }}</td>
                    <td>{{ $trx->nama_pelanggan ?: '-' }}</td>
                    <td><strong>Rp {{ number_format($trx->total, 0, ',', '.') }}</strong></td>
                    <td>
                        <span class="ow-badge {{ $trx->status === 'selesai' ? 'ow-badge-green' : 'ow-badge-red' }}">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:0.78rem;">
                        {{ \Carbon\Carbon::parse($trx->created_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="ow-empty">
                            <div class="ow-empty-icon"><i class="bi bi-receipt"></i></div>
                            <div class="ow-empty-text">Belum ada transaksi</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.ownerChartData = {
        dataHarian:  @json($grafikHarian),
        dataBulanan: @json($grafikBulanan),
        dataTahunan: @json($grafikTahunan),
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/owner/dashboard.js') }}"></script>
@endpush