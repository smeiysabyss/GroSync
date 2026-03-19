@extends('layouts.kasir')

@section('title', 'Dashboard Kasir')

@section('search_placeholder', 'Cari kategori...')

@section('content')
<div class="kasir-dashboard">

    {{-- Section Header --}}
    <div class="kasir-section-header">
        <div class="kasir-section-title">Pilih Kategori</div>
    </div>

    {{-- Category Grid --}}
    <div class="kasir-category-grid">
        @forelse($kategori as $item)
        <a href="{{ route('kasir.produk', $item->id_kategori) }}" class="kasir-category-card">

            @if($item->gambar)
                <div class="kasir-category-img-wrap">
                    <img
                        src="{{ Storage::url($item->gambar) }}"
                        alt="{{ $item->nama_kategori }}"
                        class="kasir-category-img"
                    >
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

    {{-- Pagination --}}
    @if($kategori->hasPages())
    <div class="kasir-pagination">
        {{ $kategori->links() }}
    </div>
    @endif

</div>

{{-- ============================================================
     MODAL POPUP: Keranjang
     ============================================================ --}}
<div class="cart-modal-backdrop" id="cartModalBackdrop" onclick="tutupCartModal()"></div>

<div class="cart-modal" id="cartModal">

    {{-- Header --}}
    <div class="cart-modal-header">
        <div class="cart-modal-title">
            <i class="bi bi-cart3 me-2"></i> Keranjang Belanja
        </div>
        <button class="cart-modal-close" onclick="tutupCartModal()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Body --}}
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
                {{-- Gambar --}}
                <div class="cart-modal-item-img">
                    @if($item['gambar'])
                        <img src="{{ Storage::url($item['gambar']) }}" alt="{{ $item['nama_produk'] }}">
                    @else
                        <div class="cart-modal-item-img-placeholder">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="cart-modal-item-info">
                    <div class="cart-modal-item-name">{{ $item['nama_produk'] }}</div>
                    <div class="cart-modal-item-satuan">{{ $item['satuan'] }}</div>
                    <div class="cart-modal-item-harga">
                        Rp {{ number_format($item['harga'], 0, ',', '.') }}
                        <span class="cart-modal-item-qty">× {{ $item['jumlah'] }}</span>
                    </div>
                </div>

                {{-- Subtotal --}}
                <div class="cart-modal-item-subtotal">
                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                </div>

                {{-- Hapus --}}
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

    {{-- Footer --}}
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
            <a href="{{ route('kasir.keranjang') }}" class="cart-modal-btn-checkout">
                CHECKOUT <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
/* ============================================================
   CART MODAL POPUP — Dashboard Kasir
   ============================================================ */

/* Backdrop */
.cart-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 1040;
    animation: backdropIn 0.2s ease;
}
.cart-modal-backdrop.show { display: block; }

@keyframes backdropIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* Modal */
.cart-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -48%);
    width: 480px;
    max-width: calc(100vw - 32px);
    max-height: 80vh;
    background: white;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    z-index: 1050;
    display: none;
    flex-direction: column;
    overflow: hidden;
    animation: modalIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.cart-modal.show {
    display: flex;
}

@keyframes modalIn {
    from { opacity: 0; transform: translate(-50%, -44%) scale(0.95); }
    to   { opacity: 1; transform: translate(-50%, -48%) scale(1); }
}

/* Header */
.cart-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    background: #2d5a14;
    color: white;
    flex-shrink: 0;
}
.cart-modal-title {
    font-size: 0.95rem;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.cart-modal-close {
    background: transparent;
    border: none;
    color: white;
    font-size: 0.9rem;
    cursor: pointer;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}
.cart-modal-close:hover { background: rgba(255,255,255,0.15); }

/* Body */
.cart-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Empty */
.cart-modal-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: #9ca3af;
    gap: 8px;
    text-align: center;
}
.cart-modal-empty i    { font-size: 2.8rem; opacity: 0.3; }
.cart-modal-empty p    { font-size: 0.9rem; font-weight: 700; color: #6b7280; margin: 0; }
.cart-modal-empty span { font-size: 0.78rem; }

/* Item */
.cart-modal-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 12px;
    border: 1px solid #f3f4f6;
}
.cart-modal-item-img {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    overflow: hidden;
    background: #e5e7eb;
    flex-shrink: 0;
}
.cart-modal-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cart-modal-item-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.1rem;
}
.cart-modal-item-info { flex: 1; min-width: 0; }
.cart-modal-item-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #1a2e0f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cart-modal-item-satuan {
    font-size: 0.7rem;
    color: #9ca3af;
    margin: 2px 0;
}
.cart-modal-item-harga {
    font-size: 0.78rem;
    font-weight: 600;
    color: #3a6b1a;
}
.cart-modal-item-qty {
    font-weight: 400;
    color: #6b7280;
    font-size: 0.75rem;
}
.cart-modal-item-subtotal {
    font-size: 0.82rem;
    font-weight: 800;
    color: #1a2e0f;
    white-space: nowrap;
    flex-shrink: 0;
}
.cart-modal-item-hapus {
    background: transparent;
    border: none;
    color: #dc2626;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    font-size: 0.85rem;
    transition: background 0.15s;
    flex-shrink: 0;
}
.cart-modal-item-hapus:hover { background: #fee2e2; }

/* Footer */
.cart-modal-footer {
    padding: 14px 16px;
    border-top: 1px solid #f3f4f6;
    flex-shrink: 0;
    background: white;
}
.cart-modal-subtotal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    font-size: 0.875rem;
    color: #374151;
    font-weight: 600;
}
.cart-modal-subtotal-value {
    font-size: 1rem;
    font-weight: 800;
    color: #1a2e0f;
}
.cart-modal-actions {
    display: flex;
    gap: 10px;
}
.cart-modal-btn-lanjut {
    flex: 1;
    padding: 10px;
    background: white;
    border: 2px solid #3a6b1a;
    color: #3a6b1a;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}
.cart-modal-btn-lanjut:hover { background: #f0fce8; }
.cart-modal-btn-checkout {
    flex: 1.5;
    padding: 10px;
    background: #8ece3f;
    color: white;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 800;
    font-family: 'Poppins', sans-serif;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.5px;
    transition: background 0.15s;
}
.cart-modal-btn-checkout:hover { background: #7cbd2f; color: white; }
</style>
@endpush

@push('scripts')
<script>
function bukaCartModal() {
    document.getElementById('cartModal').classList.add('show');
    document.getElementById('cartModalBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function tutupCartModal() {
    document.getElementById('cartModal').classList.remove('show');
    document.getElementById('cartModalBackdrop').classList.remove('show');
    document.body.style.overflow = '';
}

// Override toggleCartPanel dari kasir.js —
// di halaman dashboard, cart icon buka modal bukan slide panel
window.toggleCartPanel = function() {
    const modal = document.getElementById('cartModal');
    if (modal.classList.contains('show')) {
        tutupCartModal();
    } else {
        bukaCartModal();
    }
};

// Tutup dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupCartModal();
});
</script>
@endpush

@endsection