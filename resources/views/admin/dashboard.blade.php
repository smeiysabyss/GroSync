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
     STATS CARDS (3 card - presisi col-md-4)
     ============================================================ --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Total Produk --}}
    <div class="col-12 col-md-4">
        <div class="stat-card stat-card-produk">
            <div class="stat-card-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-label">TOTAL PRODUK</div>
                <div class="stat-card-value">{{ $totalProduk }}</div>
                <div class="stat-card-sub">{{ $totalKategori }} Kategori</div>
            </div>
        </div>
    </div>

    {{-- Card 2: Stok Menipis --}}
    <div class="col-12 col-md-4">
        <div class="stat-card stat-card-stok">
            <div class="stat-card-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-label">STOK MENIPIS</div>
                <div class="stat-card-value {{ $stokMenipis->count() > 0 ? 'text-warning' : '' }}">
                    {{ $stokMenipis->count() }}
                </div>
                <div class="stat-card-sub">Stok &lt; 10</div>
            </div>
        </div>
    </div>

    {{-- Card 3: Akan Kadaluarsa --}}
    <div class="col-12 col-md-4">
        <div class="stat-card stat-card-exp">
            <div class="stat-card-icon">
                <i class="bi bi-calendar-x-fill"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-label">AKAN KADALUARSA</div>
                <div class="stat-card-value {{ $produkKadaluarsa->count() > 0 ? 'text-danger' : '' }}">
                    {{ $produkKadaluarsa->count() }}
                </div>
                <div class="stat-card-sub">30 hari ke depan</div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     PANEL NAVIGATION (TABS)
     ============================================================ --}}
<div class="panel-card">
    <div class="panel-tabs">
        <button class="panel-tab active" data-panel="produk" onclick="switchPanel('produk')">
            <i class="bi bi-box-seam-fill me-2"></i>Total Produk
            <span class="tab-badge">{{ $semuaProduk->count() }}</span>
        </button>
        <button class="panel-tab" data-panel="stok" onclick="switchPanel('stok')">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Stok Menipis
            <span class="tab-badge tab-badge-warning">{{ $stokMenipis->count() }}</span>
        </button>
        <button class="panel-tab" data-panel="exp" onclick="switchPanel('exp')">
            <i class="bi bi-calendar-x-fill me-2"></i>Kadaluarsa
            <span class="tab-badge tab-badge-danger">{{ $produkKadaluarsa->count() }}</span>
        </button>
    </div>

   {{-- ============ PANEL: Total Produk ============ --}}
<div id="panel-produk" class="panel-content">
    <div class="table-responsive">
        <table class="table dashboard-table">
            <thead>
                <tr>
                    <th width="40">NO</th>
                    <th>PRODUK</th>
                    <th>KATEGORI</th>
                    <th>SATUAN</th>
                    <th>HARGA BELI</th>
                    <th>HARGA JUAL</th>
                    <th>STOK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($semuaProduk as $i => $p)
                    @if($p->hargaProduk->count() > 0)
                        {{-- Produk dengan harga --}}
                        @foreach($p->hargaProduk as $hp)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <div class="product-cell">
                                    <div class="product-avatar">{{ strtoupper(substr($p->nama_produk, 0, 1)) }}</div>
                                    <div class="product-name">{{ $p->nama_produk }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-kategori">{{ $p->kategori->nama_kategori ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge-satuan">{{ $hp->unit->satuan ?? '-' }}</span>
                            </td>
                            <td class="harga-beli-cell">
                                Rp {{ number_format($hp->harga, 0, ',', '.') }}
                            </td>
                            <td class="harga-jual-cell">
                                Rp {{ number_format($hp->harga_jual ?? $hp->harga, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="stok-badge {{ $hp->stok < 10 ? 'stok-low' : '' }}">
                                    {{ number_format($hp->stok) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        {{-- Produk TANPA harga --}}
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <div class="product-cell">
                                    <div class="product-avatar">{{ strtoupper(substr($p->nama_produk, 0, 1)) }}</div>
                                    <div class="product-name">{{ $p->nama_produk }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-kategori">{{ $p->kategori->nama_kategori ?? '-' }}</span>
                            </td>
                            <td colspan="4" class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>Belum ada data harga
                            </td>
                        </tr>
                    @endif
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-box-seam"></i>
                            <p>Belum ada produk</p>
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
            <table class="table dashboard-table">
                <thead>
                    <tr>
                        <th width="40">NO</th>
                        <th>PRODUK</th>
                        <th>SATUAN</th>
                        <th>HARGA BELI</th>
                        <th>HARGA JUAL</th>
                        <th>STOK</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokMenipis as $i => $hp)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <div class="product-cell">
                                <div class="product-avatar product-avatar-warning">{{ strtoupper(substr($hp->produk->nama_produk, 0, 1)) }}</div>
                                <div class="product-name">{{ $hp->produk->nama_produk }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-satuan">{{ $hp->unit->satuan ?? '-' }}</span>
                        </td>
                        <td class="harga-beli-cell">Rp {{ number_format($hp->harga, 0, ',', '.') }}</td>
                        <td class="harga-jual-cell">Rp {{ number_format($hp->harga_jual ?? $hp->harga, 0, ',', '.') }}</td>
                        <td>
                            <span class="stok-badge {{ $hp->stok == 0 ? 'stok-habis' : 'stok-low' }}">
                                {{ $hp->stok }} {{ $hp->unit->satuan ?? '' }}
                            </span>
                        </td>
                        <td>
                            @if($hp->stok == 0)
                                <span class="status-badge status-danger">Habis</span>
                            @else
                                <span class="status-badge status-warning">Menipis</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="empty-state text-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <p>Semua stok dalam kondisi baik!</p>
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
            <table class="table dashboard-table">
                <thead>
                    <tr>
                        <th width="40">NO</th>
                    <th>PRODUK</th>
                    <th>KATEGORI</th>
                    <th>SATUAN</th>
                    <th>JUMLAH</th>
                    <th>TANGGAL KADALUARSA</th>
                    <th>SUMBER</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produkKadaluarsa as $i => $item)
                    @php
                        $expDate = \Carbon\Carbon::parse($item->tanggal_kadaluarsa);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <div class="product-cell">
                                <div class="product-avatar product-avatar-danger">
                                    {{ strtoupper(substr($item->produk->nama_produk ?? '-', 0, 1)) }}
                                </div>
                                <div class="product-name">{{ $item->produk->nama_produk ?? '-' }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                        </td>
                        <td>
                            @if($item->sumber == 'batch')
                                <span class="badge-satuan">{{ $item->satuan }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->sumber == 'batch')
                                <span class="text-muted small">{{ number_format($item->jumlah_batch) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="exp-date">
                            {{ $expDate->format('d/m/Y') }}
                        </td>
                        <td>
                            @if($item->sumber == 'produk')
                                <span class="badge-produk">Kadaluarsa Produk</span>
                            @else
                                <span class="badge-batch">Per Batch</span>
                            @endif
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="empty-state text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <p>Tidak ada produk yang akan kadaluarsa!</p>
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
    const panelConfig = {
        produk: { title: 'Total Produk', count: {{ $semuaProduk->count() }} },
        stok: { title: 'Stok Menipis', count: {{ $stokMenipis->count() }} },
        exp: { title: 'Produk Kadaluarsa', count: {{ $produkKadaluarsa->count() }} },
    };
</script>
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush