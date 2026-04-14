<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir') — GROSYNC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/kasir/dashboard.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

{{-- ============================================================
     TOP NAVBAR KASIR
     ============================================================ --}}
<header class="kasir-navbar">

    {{-- Kiri: Back + Brand --}}
    <div class="kasir-navbar-left">
        @hasSection('back_url')
            <a href="@yield('back_url')" class="kasir-back-btn" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
        @else
            <div style="width: 36px;"></div>
        @endif

        <div class="kasir-divider"></div>

        <span class="kasir-brand">GROSYNC</span>
    </div>

    {{-- Tengah: Search Bar --}}
    <div class="kasir-navbar-center">
        <div class="kasir-search-wrap">
            <i class="bi bi-search kasir-search-icon"></i>
            <input
                type="text"
                id="kasirSearchInput"
                class="kasir-search-input"
                placeholder="@yield('search_placeholder', 'Cari Produk...')"
                autocomplete="off"
            >
        </div>
    </div>

    {{-- Kanan: Cart + Profile --}}
    <div class="kasir-navbar-right">

        {{-- Tombol Keranjang --}}
        @php
            $keranjangSession = session('keranjang', []);
            $totalQtyKeranjang = collect($keranjangSession)->sum('jumlah');
        @endphp
        <button
            class="kasir-cart-btn"
            id="kasirCartBtn"
            title="Keranjang"
            onclick="typeof toggleCartPanel === 'function' ? toggleCartPanel() : window.location.href='{{ route('kasir.keranjang') }}'"
        >
            <i class="bi bi-cart3"></i>
            @if($totalQtyKeranjang > 0)
                <span class="kasir-cart-badge">{{ $totalQtyKeranjang > 99 ? '99+' : $totalQtyKeranjang }}</span>
            @endif
        </button>

        <div class="kasir-divider"></div>

        {{-- Profile Dropdown --}}
        <div class="kasir-profile-wrap" id="kasirProfileWrap">
            <button class="kasir-profile-btn" id="kasirProfileBtn" onclick="toggleKasirProfile()" title="Profil">
                <div class="kasir-avatar">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                </div>
            </button>

            <div class="kasir-profile-menu" id="kasirProfileMenu">
                <div class="kasir-profile-header">
                    <div class="kasir-profile-avatar-lg">
                        {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                    </div>
                    <div>
                        <div class="kasir-profile-name">{{ Auth::user()->username }}</div>
                        <div class="kasir-profile-role">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </div>
                <div class="kasir-profile-divider"></div>
                <a href="{{ route('kasir.riwayat') }}" class="kasir-profile-item">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat Transaksi</span>
                </a>
                <div class="kasir-profile-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="kasir-profile-item kasir-logout-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>

{{-- ============================================================
     FLASH MESSAGES
     ============================================================ --}}
@if(session('success'))
<div class="kasir-flash kasir-flash--success" id="kasirFlash">
    <i class="bi bi-check-circle-fill"></i>
    <span>{{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="kasir-flash-close">&times;</button>
</div>
@endif
@if(session('error'))
<div class="kasir-flash kasir-flash--error" id="kasirFlash">
    <i class="bi bi-exclamation-circle-fill"></i>
    <span>{{ session('error') }}</span>
    <button onclick="this.parentElement.remove()" class="kasir-flash-close">&times;</button>
</div>
@endif

{{-- ============================================================
     PAGE CONTENT
     ============================================================ --}}
<main class="kasir-main">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/kasir/dashboard.js') }}"></script>
@stack('scripts')

<script>
    // Auto dismiss flash setelah 4 detik
    setTimeout(() => {
        const f = document.getElementById('kasirFlash');
        if (f) f.style.opacity === '0' || f.remove();
    }, 4000);
</script>

</body>
</html>