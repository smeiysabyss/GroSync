<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — GROSYNC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin/sidebar.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    @include('layouts.partials.admin-sidebar')

    {{-- Main Wrapper --}}
    <div class="main-wrapper" id="mainWrapper">

        {{-- Top Navbar --}}
        <header class="top-navbar">

            {{-- Kiri: Toggle + Divider + Brand --}}
            <div class="admin-navbar-left">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Buka/Tutup Sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="admin-nav-divider"></div>
            </div>

            {{-- Tengah: Search Bar (hanya tampil jika bukan dashboard) --}}
            @if(!request()->routeIs('admin.dashboard'))
            <div class="admin-navbar-center">
                <div class="admin-search-wrap">
                    <i class="bi bi-search admin-search-icon"></i>
                    <input
                        type="text"
                        id="adminSearchInput"
                        class="admin-search-input"
                        placeholder="@yield('search_placeholder', 'Cari data...')"
                        autocomplete="off"
                    >
                </div>
            </div>
            @else
            {{-- Dashboard: tidak ada search bar, beri div kosong agar layout tetap rapi --}}
            <div class="admin-navbar-center"></div>
            @endif

            {{-- Kanan: Profile Dropdown --}}
            <div class="admin-navbar-right">
                <div class="profile-dropdown" id="profileDropdown">
                    <button class="profile-btn" id="profileBtn" onclick="toggleProfileMenu()" title="Profil">
                        <div class="profile-avatar">
                            {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                        </div>
                    </button>
                    <div class="profile-menu" id="profileMenu">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/admin/sidebar.js') }}"></script>
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alert.alert-success, .alert.alert-danger').forEach(function (el) {
                setTimeout(function () {
                    bootstrap.Alert.getOrCreateInstance(el).close();
                }, 4000);
            });
        });
    </script>

</body>
</html>