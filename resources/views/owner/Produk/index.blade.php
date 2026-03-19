@extends('layouts.owner')

@section('title', 'Data Produk')

@push('styles')
<link href="{{ asset('css/owner/produk.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="ow-page-header">
    <div>
        <div class="ow-page-title">Data Produk</div>
        <div class="ow-page-subtitle">{{ $totalProduk }} produk tersedia</div>
    </div>
</div>

{{-- FILTER --}}
<div class="ow-card mb-4">
    <div class="ow-card-body">
        <form method="GET" action="{{ route('owner.produk') }}" id="formFilterProduk" class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-4">
                <label class="form-label ow-filter-label">Cari Produk</label>
                <div class="ow-search-wrap">
                    <i class="bi bi-search ow-search-icon"></i>
                    <input type="text" name="q" id="searchProduk"
                           class="form-control ow-search-input"
                           placeholder="Nama produk..."
                           value="{{ request('q') }}"
                           autocomplete="off">
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label ow-filter-label">Kategori</label>
                <select name="kategori" id="filterKategori" class="form-select ow-filter-select">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $k)
                    <option value="{{ $k->id_kategori }}" {{ request('kategori') == $k->id_kategori ? 'selected' : '' }}>
                        {{ $k->nama_kategori }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label ow-filter-label">Satuan</label>
                <select name="satuan" id="filterSatuan" class="form-select ow-filter-select">
                    <option value="">Semua Satuan</option>
                    @foreach($units as $u)
                    <option value="{{ $u->id_unit }}" {{ request('satuan') == $u->id_unit ? 'selected' : '' }}>
                        {{ $u->satuan }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn ow-btn-filter w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['q','kategori','satuan']))
                <a href="{{ route('owner.produk') }}" class="btn ow-btn-reset">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- TABEL PRODUK --}}
<div class="ow-card">
    <div class="ow-table-wrap">
        <table class="ow-table">
            <thead>
                <tr>
                    <th class="col-no">#</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga &amp; Satuan</th>
                    <th>Stok</th>
                    <th>Kadaluarsa</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produks as $i => $produk)
                <tr>
                    <td class="ow-td-no">{{ $produks->firstItem() + $i }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($produk->gambar)
                                <img src="{{ Storage::url($produk->gambar) }}" alt="" class="ow-produk-img">
                            @else
                                <div class="ow-produk-img-placeholder">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            @endif
                            <div>
                                <div class="ow-produk-name">{{ $produk->nama_produk }}</div>
                                @if($produk->deskripsi)
                                <div class="ow-produk-desc">{{ Str::limit($produk->deskripsi, 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="ow-badge ow-badge-green">{{ $produk->kategori->nama_kategori ?? '-' }}</span>
                    </td>
                    <td>
                        @foreach($produk->hargaProduk as $hp)
                        <div class="ow-harga-row">
                            <strong>Rp {{ number_format($hp->harga, 0, ',', '.') }}</strong>
                            <span class="text-muted">/ {{ $hp->unit->satuan ?? '-' }}</span>
                        </div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($produk->hargaProduk as $hp)
                        <div class="ow-harga-row">
                            @if($hp->stok <= 5)
                                <span class="ow-badge ow-badge-red">{{ $hp->stok }} {{ $hp->unit->satuan ?? '' }}</span>
                            @elseif($hp->stok <= 20)
                                <span class="ow-badge ow-badge-amber">{{ $hp->stok }} {{ $hp->unit->satuan ?? '' }}</span>
                            @else
                                <span class="ow-stok-normal">{{ $hp->stok }} {{ $hp->unit->satuan ?? '' }}</span>
                            @endif
                        </div>
                        @endforeach
                    </td>
                    <td>
                        @if($produk->tanggal_kadaluarsa)
                            @php
                                $exp       = \Carbon\Carbon::parse($produk->tanggal_kadaluarsa);
                                $isExpired = $exp->isPast();
                                $nearExp   = $exp->diffInDays(now()) <= 30 && !$isExpired;
                            @endphp
                            @if($isExpired)
                                <span class="ow-badge ow-badge-red">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Kadaluarsa
                                </span>
                            @elseif($nearExp)
                                <span class="ow-badge ow-badge-amber">{{ $exp->format('d/m/Y') }}</span>
                            @else
                                <span class="ow-exp-date">{{ $exp->format('d/m/Y') }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="ow-empty">
                            <div class="ow-empty-icon"><i class="bi bi-box-seam"></i></div>
                            <div class="ow-empty-text">Produk tidak ditemukan</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($produks->hasPages())
    <div class="ow-pagination">
        {{ $produks->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/owner/produk.js') }}"></script>
@endpush