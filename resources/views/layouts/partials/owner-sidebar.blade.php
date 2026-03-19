<aside id="sidebar" class="sidebar">
    {{-- Sidebar Header --}}
    <div class="sidebar-header">
        <a href="{{ route('owner.dashboard') }}" class="sidebar-brand">
            <span class="brand-text">GROSYNC</span>
        </a>
    </div>

    {{-- Sidebar Navigation --}}
    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('owner.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-grid-fill"></i></span>
                    <span class="sidebar-label">Dashboard</span>
                </a>
            </li>
        </ul>

        {{-- Data & Laporan --}}
        <div class="sidebar-section-label">Data &amp; Laporan</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('owner.produk') }}"
                   class="sidebar-link {{ request()->routeIs('owner.produk') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-box-seam-fill"></i></span>
                    <span class="sidebar-label">Data Produk</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('owner.laporan') }}"
                   class="sidebar-link {{ request()->routeIs('owner.laporan') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-receipt-cutoff"></i></span>
                    <span class="sidebar-label">Laporan Transaksi</span>
                </a>
            </li>
        </ul>

        {{-- Aktivitas --}}
        <div class="sidebar-section-label">Aktivitas</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('owner.log') }}"
                   class="sidebar-link {{ request()->routeIs('owner.log') ? 'active' : '' }}">
                    <span class="sidebar-icon"><i class="bi bi-clock-history"></i></span>
                    <span class="sidebar-label">Log Aktivitas</span>
                </a>
            </li>
        </ul>

    </nav>
</aside>

{{-- Overlay backdrop (mobile) --}}
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>