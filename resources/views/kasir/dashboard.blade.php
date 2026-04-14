@extends('layouts.kasir')

@section('title', 'Dashboard Kasir')

@section('search_placeholder', 'Cari kategori / produk...')

@push('styles')
<link href="{{ asset('css/kasir/transaksi.css') }}" rel="stylesheet">
<link href="{{ asset('css/kasir/dashboard.css') }}" rel="stylesheet">
<link href="{{ asset('css/kasir/produk.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="kasir-dashboard">

    {{-- Section Header: Kategori --}}
    <div class="kasir-section-header">
        <div class="kasir-section-title">Pilih Kategori</div>
    </div>

    {{-- Category Grid --}}
    <div class="kasir-category-grid">
        @forelse($kategori as $item)
        <a href="{{ route('kasir.produk', $item->id_kategori) }}" class="kasir-category-card">
            @if($item->gambar)
                <div class="kasir-category-img-wrap">
                    <img src="{{ Storage::url($item->gambar) }}" alt="{{ $item->nama_kategori }}" class="kasir-category-img">
                </div>
            @else
                <div class="kasir-category-icon">
                    <i class="bi bi-grid"></i>
                </div>
            @endif
            <div class="kasir-category-name">{{ $item->nama_kategori }}</div>
            <div class="kasir-category-count">{{ $item->produk_count }} produk</div>
        </a>
        @empty
        <div class="kasir-empty-state">
            <i class="bi bi-grid"></i>
            <p>Belum ada kategori tersedia</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination Kategori --}}
    @if($kategori->hasPages())
    <div class="kasir-pagination">
        {{ $kategori->links() }}
    </div>
    @endif

    {{-- ============================================================
         SECTION PRODUK TERBARU
    ============================================================ --}}
    <div class="kasir-produk-section">
        <div class="kasir-section-header">
            <div class="kasir-section-title">Produk Terbaru</div>
        </div>

        <div class="produk-grid" id="produkGrid">
            @forelse($produkTerbaru as $item)
            @php
                $hargaList = $item->hargaProduk;
                $totalStok = $hargaList->sum('stok');
                $isHabis   = $totalStok <= 0;
            @endphp

            <div class="produk-card {{ $isHabis ? 'produk-card--habis' : '' }}"
                 data-nama="{{ strtolower($item->nama_produk) }}"
                 onclick="openDetailModal({{ $item->id_produk }})">

                @if($isHabis)
                    <div class="produk-badge-habis">Stok Habis</div>
                @else
                    <div class="produk-badge-stok">
                        <span class="produk-stok-dot"></span>
                    </div>
                @endif

                <div class="produk-card-img-wrap">
                    @if($item->gambar)
                        <img src="{{ Storage::url($item->gambar) }}" alt="{{ $item->nama_produk }}" class="produk-card-img">
                    @else
                        <div class="produk-card-img-placeholder">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    @endif
                </div>

                <div class="produk-card-body">
                    <div class="produk-card-name">{{ $item->nama_produk }}</div>
                    <div class="produk-card-prices">
                        @foreach($hargaList as $hp)
                        <div class="produk-card-price-row">
                            Rp {{ number_format($hp->harga_jual, 0, ',', '.') }} / {{ $hp->unit->satuan }}
                        </div>
                        @endforeach
                    </div>
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
                <p>Belum ada produk tersedia</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ============================================================
     MODAL POPUP: Keranjang
     ============================================================ --}}
<div class="cart-modal-backdrop" id="cartModalBackdrop" onclick="tutupCartModal()"></div>

<div class="cart-modal" id="cartModal">
    <div class="cart-modal-header">
        <div class="cart-modal-title">
            <i class="bi bi-cart3 me-2"></i> Keranjang Belanja
        </div>
        <button class="cart-modal-close" onclick="tutupCartModal()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="cart-modal-body">
        @php $keranjang = session('keranjang', []); @endphp

        @if(empty($keranjang))
            <div class="cart-modal-empty">
                <i class="bi bi-cart3"></i>
                <p>Keranjang masih kosong</p>
                <span>Pilih kategori dan tambahkan produk</span>
            </div>
        @else
            @foreach($keranjang as $key => $item)
            <div class="cart-modal-item">
                <div class="cart-modal-item-img">
                    @if($item['gambar'])
                        <img src="{{ Storage::url($item['gambar']) }}" alt="{{ $item['nama_produk'] }}">
                    @else
                        <div class="cart-modal-item-img-placeholder">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    @endif
                </div>
                <div class="cart-modal-item-info">
                    <div class="cart-modal-item-name">{{ $item['nama_produk'] }}</div>
                    <div class="cart-modal-item-satuan">{{ $item['satuan'] }}</div>
                    <div class="cart-modal-item-harga">
                        Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}
                        <span class="cart-modal-item-qty">× {{ $item['jumlah'] }}</span>
                    </div>
                </div>
                <div class="cart-modal-item-subtotal">
                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                </div>
                <form action="{{ route('kasir.keranjang.hapus') }}" method="POST">
                    @csrf
                    <input type="hidden" name="key" value="{{ $key }}">
                    <button type="submit" class="cart-modal-item-hapus" title="Hapus">
                        <i class="bi bi-trash3"></i>
                    </button>
                </form>
            </div>
            @endforeach
        @endif
    </div>

    @if(!empty($keranjang))
    <div class="cart-modal-footer">
        <div class="cart-modal-subtotal">
            <span>Total</span>
            <span class="cart-modal-subtotal-value">
                Rp {{ number_format(collect($keranjang)->sum('subtotal'), 0, ',', '.') }}
            </span>
        </div>
        <div class="cart-modal-actions">
            <button class="cart-modal-btn-lanjut" onclick="tutupCartModal()">
                <i class="bi bi-plus-circle me-1"></i> Tambah Produk
            </button>
            <button class="cart-modal-btn-checkout" onclick="tutupCartModal(); setTimeout(bukaModalTrx, 200);">
                CHECKOUT <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
    @endif
</div>

{{-- ============================================================
     MODAL: Detail Produk 
     ============================================================ --}}
<div class="modal fade modal-detail-produk" id="modalDetailProduk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content produk-modal">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-box-seam me-2"></i> Detail Produk
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="detail-container">
                    
                    {{-- KOLOM KIRI: GAMBAR --}}
                    <div class="detail-gambar-col">
                        <div class="detail-gambar-wrap">
                            <img id="detailGambar" src="" alt="" class="detail-gambar" style="display: none;">
                            <div id="detailGambarPlaceholder" class="detail-gambar-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        </div>
                    </div>
                    
                    {{-- KOLOM KANAN: INFO PRODUK --}}
                    <div class="detail-info-col">
                        <div class="detail-nama-produk" id="detailNama"></div>
                        
                        <div class="detail-badges" id="detailBadges"></div>
                        
                        <div class="detail-meta-grid">
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">
                                    <i class="bi bi-calendar me-1"></i> Kadaluarsa
                                </span>
                                <span class="detail-meta-value" id="detailKadaluarsa">—</span>
                            </div>
                            <div class="detail-meta-item">
                                <span class="detail-meta-label">
                                    <i class="bi bi-boxes me-1"></i> Stok Tersedia
                                </span>
                                <span class="detail-meta-value" id="detailStok">—</span>
                            </div>
                        </div>
                        
                        <div class="detail-deskripsi-section">
                            <div class="detail-deskripsi-label">
                                <i class="bi bi-file-text"></i> Deskripsi
                            </div>
                            <div class="detail-deskripsi" id="detailDeskripsi">—</div>
                        </div>
                        
                        <div class="detail-harga-section">
                            <div class="detail-harga-label">
                                <i class="bi bi-tags"></i> Daftar Harga & Stok
                            </div>
                            <table class="detail-harga-table" id="detailHargaTable">
                                <thead>
                                    <tr>
                                        <th>Satuan</th>
                                        <th>Harga Jual</th>
                                        <th>Stok</th>
                                    </tr>
                                </thead>
                                <tbody id="detailHargaTableBody">
                                    <tr>
                                        <td colspan="3" class="text-muted">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="detail-btn-wrap">
                            <button class="detail-btn-tambah" id="detailBtnTambah">
                                <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        </div>
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
                <div class="tambah-produk-info">
                    <div class="tambah-produk-img-wrap">
                        <img id="tambahGambar" src="" alt="" class="tambah-produk-img">
                    </div>
                    <div class="tambah-produk-nama" id="tambahNama"></div>
                </div>
                <div class="tambah-satuan-list" id="tambahSatuanList"></div>
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

{{-- Modal Transaksi --}}
@include('kasir.transaksi-modal')

{{-- Data produk untuk JS (gabungkan produk terbaru) --}}
<script>
const produkData = @json($produkTerbaruJs);
</script>

@endsection

@push('scripts')
<script src="{{ asset('js/kasir/dashboard.js') }}"></script>
<script src="{{ asset('js/kasir/transaksi.js') }}"></script>
<script src="{{ asset('js/kasir/produk.js') }}"></script>
@endpush