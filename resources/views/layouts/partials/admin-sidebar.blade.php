<aside id="sidebar" class="sidebar">
    {{-- Sidebar Header --}}
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <span class="brand-text">GROSYNC</span>
        </a>
      
        </button>
    </div>

    {{-- Sidebar Navigation --}}
    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-grid-fill"></i></span>
                    <span class="sidebar-label">Dashboard</span>
                </a>
            </li>
        </ul>

        {{-- Manajemen Produk --}}
        <div class="sidebar-section-label">Manajemen Produk</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.produk.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.produk.index') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-box-seam-fill"></i></span>
                    <span class="sidebar-label">Kelola Produk</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.harga.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.harga.index') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-tags-fill"></i></span>
                    <span class="sidebar-label">Kelola Stok & Harga</span>
                </a>
            </li>
        </ul>

        {{-- Manajemen Kategori & Satuan --}}
        <div class="sidebar-section-label">Manajemen Kategori &amp; Satuan</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.satuan.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.satuan.index') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-bezier2"></i></span>
                    <span class="sidebar-label">Kelola Satuan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{  route('admin.kategori.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.kategori.index') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-diagram-3-fill"></i></span>
                    <span class="sidebar-label">Kelola Kategori</span>
                </a>
            </li>
        </ul>

        {{-- Manajemen Pengguna --}}
        <div class="sidebar-section-label">Manajemen Pengguna</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('admin.pengguna.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.pengguna.index') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-person-gear"></i></span>
                    <span class="sidebar-label">Kelola Pengguna</span>
                </a>
            </li>
        </ul>

    </nav>
</aside>

{{-- Overlay backdrop (mobile) --}}
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>