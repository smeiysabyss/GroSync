<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Owner') — GROSYNC</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Custom Sidebar CSS --}}
    <link href="{{ asset('css/owner/sidebar.css') }}" rel="stylesheet">
    {{-- Page-specific CSS --}}
    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    @include('layouts.partials.owner-sidebar')

    {{-- Main Wrapper --}}
    <div class="main-wrapper" id="mainWrapper">

        {{-- Top Navbar --}}
        <header class="top-navbar">

            {{-- Tombol Toggle Sidebar --}}
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Buka/Tutup Sidebar">
                <i class="bi bi-list"></i>
            </button>

            {{-- Kanan: Profile Dropdown --}}
            <div class="navbar-right">
                <div class="profile-dropdown" id="profileDropdown">

                    {{-- Avatar Button --}}
                    <button class="profile-btn" id="profileBtn" onclick="toggleProfileMenu()" title="Profil">
                        <div class="profile-avatar">
                            {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                        </div>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div class="profile-menu" id="profileMenu">
                        {{-- Info User --}}
                        <div class="profile-menu-header">
                            <div class="profile-menu-avatar">
                                {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                            </div>
                            <div class="profile-menu-info">
                                <div class="profile-menu-name">{{ Auth::user()->username }}</div>
                                <div class="profile-menu-role">{{ ucfirst(Auth::user()->role) }}</div>
                            </div>
                        </div>

                        <div class="profile-menu-divider"></div>

                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="profile-menu-item profile-menu-logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </header>

        {{-- Page Content --}}
        <main class="page-content">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Custom Sidebar JS --}}
    <script src="{{ asset('js/owner/sidebar.js') }}"></script>
    {{-- Page-specific JS --}}
    @stack('scripts')
</body>
</html>