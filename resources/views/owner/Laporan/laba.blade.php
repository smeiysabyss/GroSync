@extends('layouts.owner')

@section('title', 'Laporan Laba')

@push('styles')
<link href="{{ asset('css/owner/laba.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="ow-page-header">
    <div>
        <div class="ow-page-title">Laporan Laba</div>
        <div class="ow-page-subtitle">Ringkasan keuntungan bisnis</div>
    </div>
</div>

{{-- FILTER PERIODE --}}
<div class="ow-card mb-4">
    <div class="ow-card-body">
        <form method="GET" action="{{ route('owner.laporan.laba') }}" id="formFilterLaba" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label ow-filter-label">Periode</label>
                <select name="periode" id="filterPeriode" class="form-select ow-filter-select">
                    <option value="hari" {{ $periode == 'hari' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="minggu" {{ $periode == 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="bulan" {{ $periode == 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="tahun" {{ $periode == 'tahun' ? 'selected' : '' }}>Tahun Ini</option>
                </select>
            </div>
            <div class="col-md-8 d-flex gap-2 justify-content-end">
                <button type="button" onclick="exportLaporan('excel')" class="btn ow-btn-filter" style="background: #1e40af;">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </button>
                <button type="button" onclick="exportLaporan('pdf')" class="btn ow-btn-filter" style="background: #dc2626;">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </button>
            </div>
        </form>
    </div>
</div>

{{-- CARD METRIK --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="laba-card">
            <div class="laba-label">📈 Total Pendapatan</div>
            <div class="laba-number laba-positive">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="laba-card">
            <div class="laba-label">📦 HPP (Harga Pokok)</div>
            <div class="laba-number">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="laba-card">
            <div class="laba-label">💚 Laba Bersih</div>
            <div class="laba-number {{ $labaBersih >= 0 ? 'laba-positive' : 'laba-negative' }}">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

{{-- GRAFIK TREN LABA --}}
<div class="ow-card mb-4">
    <div class="ow-card-header">
        <div class="ow-card-title">📊 Tren Laba 6 Bulan Terakhir</div>
    </div>
    <div class="ow-card-body">
        <canvas id="labaChart" style="height: 300px; width: 100%;"></canvas>
    </div>
</div>

{{-- TABEL LABA PER PRODUK --}}
<div class="ow-card">
    <div class="ow-card-header">
        <div class="ow-card-title">📋 Rincian Laba per Produk</div>
    </div>
    <div class="ow-table-wrap">
        <table class="ow-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Satuan</th>
                    <th>Terjual</th>
                    <th>Pendapatan</th>
                    <th>HPP</th>
                    <th>Laba</th>
                </tr>
            </thead>
            <tbody>
                @forelse($labaPerProduk as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div class="laba-produk-nama">{{ $item['nama_produk'] }}</div>
                    </td>
                    <td><span class="laba-produk-satuan">{{ $item['satuan'] }}</span></td>
                    <td>{{ number_format($item['jumlah']) }}</td>
                    <td>Rp {{ number_format($item['pendapatan'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item['hpp'], 0, ',', '.') }}</td>
                    <td class="laba-produk-laba">Rp {{ number_format($item['laba'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="ow-empty">
                            <div class="ow-empty-icon"><i class="bi bi-receipt"></i></div>
                            <div class="ow-empty-text">Belum ada transaksi dalam periode ini</div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Data untuk grafik (dikirim dari controller)
    window.labaChartData = @json($grafikData);
    
    // Auto-submit saat filter berubah
    document.getElementById('filterPeriode')?.addEventListener('change', function() {
        document.getElementById('formFilterLaba').submit();
    });
    
    // ============================================================
    // EXPORT LAPORAN LABA
    // ============================================================
    function exportLaporan(format) {
        const periode = document.getElementById('filterPeriode').value;
        window.location.href = "{{ route('owner.laporan.laba.export') }}?periode=" + periode + "&format=" + format;
    }
</script>
<script src="{{ asset('js/owner/laba.js') }}"></script>
@endpush