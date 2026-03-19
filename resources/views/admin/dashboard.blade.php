{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@push('styles')
<link href="{{ asset('css/admin/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')

{{-- Greeting --}}
<div class="mb-4">
    <h4 class="page-title mb-1">Dashboard</h4>
    <p class="text-muted small mb-0">Berikut ringkasan toko hari ini.</p>
</div>

{{-- ============================================================
     Info Cards
     ============================================================ --}}
<div class="row g-3 mb-4">

    {{-- Total Produk --}}
    <div class="col-12 col-md-4">
        <div class="info-card info-card-produk active" id="card-produk" onclick="switchPanel('produk')">
            <div class="info-card-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="info-card-content">
                <div class="info-card-label">Total Produk</div>
                <div class="info-card-value">{{ $totalProduk }}</div>
                <div class="info-card-sub">{{ $totalKategori }} kategori tersedia</div>
            </div>
            <div class="info-card-arrow"><i class="bi bi-chevron-right"></i></div>
        </div>
    </div>

    {{-- Stok Menipis --}}
    <div class="col-12 col-md-4">
        <div class="info-card info-card-stok {{ $stokMenipis->count() > 0 ? 'has-alert' : '' }}" id="card-stok" onclick="switchPanel('stok')">
            <div class="info-card-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="info-card-content">
                <div class="info-card-label">Stok Menipis</div>
                <div class="info-card-value">{{ $stokMenipis->count() }}</div>
                <div class="info-card-sub">Produk dengan stok &lt; 10</div>
            </div>
            <div class="info-card-arrow"><i class="bi bi-chevron-right"></i></div>
        </div>
    </div>

    {{-- Produk Kadaluarsa --}}
    <div class="col-12 col-md-4">
        <div class="info-card info-card-exp {{ $produkKadaluarsa->count() > 0 ? 'has-alert' : '' }}" id="card-exp" onclick="switchPanel('exp')">
            <div class="info-card-icon">
                <i class="bi bi-calendar-x-fill"></i>
            </div>
            <div class="info-card-content">
                <div class="info-card-label">Produk Kadaluarsa</div>
                <div class="info-card-value">{{ $produkKadaluarsa->count() }}</div>
                <div class="info-card-sub">Kadaluarsa dalam 30 hari</div>
            </div>
            <div class="info-card-arrow"><i class="bi bi-chevron-right"></i></div>
        </div>
    </div>

</div>

{{-- ============================================================
     Dynamic Panel (Tabel)
     ============================================================ --}}
<div class="panel-card">

    {{-- Panel Header --}}
    <div class="panel-header" id="panelHeader">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-seam-fill panel-header-icon" id="panelIcon"></i>
            <span class="panel-header-title" id="panelTitle">Total Produk</span>
        </div>
        <span class="panel-header-count badge-count" id="panelCount"></span>
    </div>

    {{-- ============ PANEL: Total Produk ============ --}}
    <div id="panel-produk" class="panel-content">
        <div class="table-responsive">
            <table class="table grosync-table mb-0">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Mulai</th>
                        <th>Total Stok</th>
                        <th>Exp. Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semuaProduk as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="tbl-avatar">{{ strtoupper(substr($p->nama_produk, 0, 1)) }}</div>
                                <span class="fw-500">{{ $p->nama_produk }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kategori">{{ $p->kategori->nama_kategori ?? '-' }}</span>
                        </td>
                        <td>
                            @if($p->hargaProduk->isNotEmpty())
                                Rp {{ number_format($p->hargaProduk->min('harga'), 0, ',', '.') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php $totalStok = $p->hargaProduk->sum('stok'); @endphp
                            <span class="badge-stok {{ $totalStok < 10 ? 'badge-stok-low' : '' }}">
                                {{ number_format($totalStok) }}
                            </span>
                        </td>
                        <td>
                            @if($p->tanggal_kadaluarsa)
                                @php
                                    $exp  = \Carbon\Carbon::parse($p->tanggal_kadaluarsa);
                                    $days = now()->diffInDays($exp, false);
                                @endphp
                                <span class="{{ $days <= 30 ? 'text-danger fw-600' : 'text-muted' }}">
                                    {{ $exp->format('d/m/Y') }}
                                    @if($days <= 30 && $days >= 0)
                                        <small>({{ $days }}h lagi)</small>
                                    @elseif($days < 0)
                                        <small>(Kadaluarsa)</small>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="empty-inline">
                                <i class="bi bi-box-seam"></i>
                                <span>Belum ada produk</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ PANEL: Stok Menipis ============ --}}
    <div id="panel-stok" class="panel-content d-none">
        <div class="table-responsive">
            <table class="table grosync-table mb-0">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Stok Tersisa</th>
                        <th>Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokMenipis as $i => $hp)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="tbl-avatar tbl-avatar-warn">{{ strtoupper(substr($hp->produk->nama_produk, 0, 1)) }}</div>
                                <span class="fw-500">{{ $hp->produk->nama_produk ?? '-' }}</span>
                            </div>
                        </td>
                        <td>{{ $hp->unit->satuan ?? '-' }}</td>
                        <td>
                            <span class="stok-badge {{ $hp->stok == 0 ? 'stok-habis' : 'stok-menipis' }}">
                                {{ $hp->stok }} {{ $hp->unit->satuan ?? '' }}
                                @if($hp->stok == 0) <i class="bi bi-exclamation-circle ms-1"></i> @endif
                            </span>
                        </td>
                        <td>Rp {{ number_format($hp->harga, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="empty-inline text-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Semua stok dalam kondisi baik!</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ PANEL: Produk Kadaluarsa ============ --}}
    <div id="panel-exp" class="panel-content d-none">
        <div class="table-responsive">
            <table class="table grosync-table mb-0">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produkKadaluarsa as $i => $p)
                    @php
                        $exp  = \Carbon\Carbon::parse($p->tanggal_kadaluarsa);
                        $days = now()->diffInDays($exp, false);
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="tbl-avatar tbl-avatar-danger">{{ strtoupper(substr($p->nama_produk, 0, 1)) }}</div>
                                <span class="fw-500">{{ $p->nama_produk }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kategori">{{ $p->kategori->nama_kategori ?? '-' }}</span>
                        </td>
                        <td class="text-danger fw-600">{{ $exp->format('d/m/Y') }}</td>
                        <td>
                            @if($days < 0)
                                <span class="exp-badge exp-habis">Sudah kadaluarsa</span>
                            @elseif($days <= 7)
                                <span class="exp-badge exp-kritis">{{ $days }} hari lagi</span>
                            @else
                                <span class="exp-badge exp-warning">{{ $days }} hari lagi</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="empty-inline text-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Tidak ada produk yang akan kadaluarsa!</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    // Data panel untuk header dinamis
    const panelConfig = {
        produk: {
            icon  : 'bi-box-seam-fill',
            title : 'Total Produk',
            count : {{ $totalProduk }},
        },
        stok: {
            icon  : 'bi-exclamation-triangle-fill',
            title : 'Stok Menipis',
            count : {{ $stokMenipis->count() }},
        },
        exp: {
            icon  : 'bi-calendar-x-fill',
            title : 'Daftar Produk Kadaluarsa',
            count : {{ $produkKadaluarsa->count() }},
        },
    };
</script>
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush