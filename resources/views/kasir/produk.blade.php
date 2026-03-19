@extends('layouts.kasir')

@section('title', 'Produk ' . $kategori->nama_kategori)

@section('back_url', route('kasir.dashboard'))

@push('styles')
<link href="{{ asset('css/kasir/produk.css') }}" rel="stylesheet">
<link href="{{ asset('css/kasir/transaksi.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="produk-wrapper" id="produkWrapper">

    {{-- ============================================================
         AREA KIRI: Grid Produk
         ============================================================ --}}
    <div class="produk-area" id="produkArea">

        {{-- Header --}}
        <div class="produk-header">
            <h2 class="produk-title">PRODUK "{{ strtoupper($kategori->nama_kategori) }}"</h2>
        </div>

        {{-- Grid --}}
        <div class="produk-grid" id="produkGrid">
            @forelse($produk as $item)
            @php
                $hargaList  = $item->hargaProduk;
                $hargaMin   = $hargaList->min('harga');
                $totalStok  = $hargaList->sum('stok');
                $isHabis    = $totalStok <= 0;
            @endphp

            <div class="produk-card {{ $isHabis ? 'produk-card--habis' : '' }}"
                 data-nama="{{ strtolower($item->nama_produk) }}"
                 onclick="openDetailModal({{ $item->id_produk }})">

                {{-- Badge stok habis --}}
                @if($isHabis)
                    <div class="produk-badge-habis">Stok Habis</div>
                @else
                    <div class="produk-badge-stok">
                        <span class="produk-stok-dot"></span>
                    </div>
                @endif

                {{-- Gambar --}}
                <div class="produk-card-img-wrap">
                    @if($item->gambar)
                        <img src="{{ Storage::url($item->gambar) }}" alt="{{ $item->nama_produk }}" class="produk-card-img">
                    @else
                        <div class="produk-card-img-placeholder">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="produk-card-body">
                    <div class="produk-card-name">{{ $item->nama_produk }}</div>

                    {{-- Harga per satuan --}}
                    <div class="produk-card-prices">
                        @foreach($hargaList as $hp)
                        <div class="produk-card-price-row">
                            Rp {{ number_format($hp->harga, 0, ',', '.') }} / {{ $hp->unit->satuan }}
                            <span class="produk-card-stok">| sisa {{ $hp->stok }} {{ $hp->unit->satuan }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tombol tambah --}}
                    @if(!$isHabis)
                    <button class="produk-btn-tambah"
                            onclick="event.stopPropagation(); openTambahModal({{ $item->id_produk }})"
                            title="Tambah ke keranjang">
                        <i class="bi bi-plus-lg me-1"></i> Keranjang
                    </button>
                    @endif
                </div>

            </div>
            @empty
            <div class="produk-empty">
                <i class="bi bi-box-seam"></i>
                <p>Belum ada produk di kategori ini</p>
            </div>
            @endforelse
        </div>

    </div>

    {{-- ============================================================
         PANEL KANAN: Keranjang (slide in)
         ============================================================ --}}
    <div class="cart-panel" id="cartPanel">
        <div class="cart-panel-header">
            <span class="cart-panel-title">Atur Jumlah dan Catatan</span>
            <button class="cart-panel-close" onclick="toggleCartPanel()" title="Tutup">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="cart-panel-body" id="cartPanelBody">
            @php $keranjang = session('keranjang', []); @endphp

            @if(empty($keranjang))
                <div class="cart-panel-empty">
                    <i class="bi bi-cart3"></i>
                    <p>Keranjang masih kosong</p>
                </div>
            @else
                @foreach($keranjang as $key => $item)
                <div class="cart-item">
                    <div class="cart-item-check">
                        <input type="checkbox" class="cart-checkbox" checked>
                    </div>
                    <div class="cart-item-img-wrap">
                        @if($item['gambar'])
                            <img src="{{ Storage::url($item['gambar']) }}" alt="{{ $item['nama_produk'] }}" class="cart-item-img">
                        @else
                            <div class="cart-item-img-placeholder"><i class="bi bi-box-seam"></i></div>
                        @endif
                    </div>
                    <div class="cart-item-info">
                        <div class="cart-item-name">{{ $item['nama_produk'] }}</div>
                        <div class="cart-item-satuan">{{ $item['satuan'] }}</div>
                        <div class="cart-item-harga">Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                        <div class="cart-item-qty">Qty: {{ $item['jumlah'] }}</div>
                    </div>
                    <form action="{{ route('kasir.keranjang.hapus') }}" method="POST" class="cart-item-hapus-form">
                        @csrf
                        <input type="hidden" name="key" value="{{ $key }}">
                        <button type="submit" class="cart-item-hapus" title="Hapus">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Footer: subtotal + checkout --}}
        @if(!empty($keranjang))
        <div class="cart-panel-footer">
            <div class="cart-subtotal">
                <span>Subtotal</span>
                <span class="cart-subtotal-value">
                    Rp {{ number_format(collect($keranjang)->sum('subtotal'), 0, ',', '.') }}
                </span>
            </div>
            <a href="{{ route('kasir.keranjang') }}" class="cart-checkout-btn">
                CHECKOUT
            </a>
        </div>
        @endif
    </div>

</div>

{{-- Overlay backdrop untuk cart panel --}}
<div class="cart-backdrop" id="cartBackdrop" onclick="toggleCartPanel()"></div>


{{-- ============================================================
     MODAL: Detail Produk
     ============================================================ --}}
<div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content produk-modal">
            <div class="modal-header produk-modal-header">
                <h6 class="modal-title">Detail Produk</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="detail-layout">
                    {{-- Gambar --}}
                    <div class="detail-img-col">
                        <div class="detail-img-wrap">
                            <img id="detailGambar" src="" alt="" class="detail-img">
                        </div>
                        <div class="detail-nama" id="detailNama"></div>
                        <div class="detail-badges" id="detailBadges"></div>
                        <div class="detail-meta">
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">Tanggal Kadaluarsa</span>
                                <span class="detail-meta-value" id="detailKadaluarsa">—</span>
                            </div>
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">Stok</span>
                                <span class="detail-meta-value" id="detailStok">—</span>
                            </div>
                        </div>
                        <div class="detail-deskripsi-label">Deskripsi</div>
                        <div class="detail-deskripsi" id="detailDeskripsi">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL: Tambah ke Keranjang
     ============================================================ --}}
<div class="modal fade" id="modalTambahKeranjang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content produk-modal">
            <div class="modal-header produk-modal-header">
                <h6 class="modal-title">Tambahkan ke keranjang</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Info produk --}}
                <div class="tambah-produk-info">
                    <div class="tambah-produk-img-wrap">
                        <img id="tambahGambar" src="" alt="" class="tambah-produk-img">
                    </div>
                    <div class="tambah-produk-nama" id="tambahNama"></div>
                </div>

                {{-- Pilih satuan --}}
                <div class="tambah-satuan-list" id="tambahSatuanList">
                    {{-- Diisi via JS --}}
                </div>

                {{-- Qty + Tombol --}}
                <div class="tambah-footer">
                    <div class="tambah-qty-wrap">
                        <button class="tambah-qty-btn" onclick="kurangQty()" type="button">−</button>
                        <input type="number" id="tambahQty" class="tambah-qty-input" value="1" min="1">
                        <button class="tambah-qty-btn" onclick="tambahQty()" type="button">+</button>
                    </div>
                    <form id="formTambahKeranjang" action="{{ route('kasir.keranjang.tambah') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_harga_produk" id="tambahIdHarga">
                        <input type="hidden" name="jumlah" id="tambahJumlahHidden">
                        <button type="submit" class="tambah-submit-btn" onclick="submitTambah(event)">
                            <i class="bi bi-cart-plus me-1"></i> Keranjang
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>



{{-- Data produk untuk JS --}}
<script>
const produkData = @json($produkJs);
</script>

@include('kasir.transaksi-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/kasir/produk.js') }}"></script>
<script src="{{ asset('js/kasir/transaksi.js') }}"></script>  
@endpush