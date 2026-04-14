(function () {
    "use strict";

    // ============================================================
    // Element References
    // ============================================================
    const sidebar = document.getElementById("sidebar");
    const mainWrapper = document.getElementById("mainWrapper");
    const toggleBtn = document.getElementById("sidebarToggleBtn");
    const closeBtn = document.getElementById("sidebarCloseBtn");
    const backdrop = document.getElementById("sidebarBackdrop");

    // ============================================================
    // State Key (localStorage)
    // ============================================================
    const STATE_KEY = "grosync_sidebar_open";

    // ============================================================
    // Helper: Cek apakah layar mobile
    // ============================================================
    function isMobile() {
        return window.innerWidth < 768;
    }

    // ============================================================
    // Buka Sidebar
    // ============================================================
    function openSidebar() {
        if (isMobile()) {
            // Mobile: slide dari kiri ke dalam
            sidebar.classList.add("sidebar-open");
            sidebar.classList.remove("sidebar-hidden");
            backdrop.classList.add("active");
        } else {
            // Desktop: tampilkan sidebar & geser main-wrapper
            sidebar.classList.remove("sidebar-hidden");
            mainWrapper.classList.remove("sidebar-hidden");
        }
        localStorage.setItem(STATE_KEY, "open");
    }

    // ============================================================
    // Tutup Sidebar
    // ============================================================
    function closeSidebar() {
        if (isMobile()) {
            // Mobile: slide kembali ke kiri
            sidebar.classList.remove("sidebar-open");
            sidebar.classList.add("sidebar-hidden");
            backdrop.classList.remove("active");
        } else {
            // Desktop: sembunyikan sidebar & perluas main-wrapper
            sidebar.classList.add("sidebar-hidden");
            mainWrapper.classList.add("sidebar-hidden");
        }
        localStorage.setItem(STATE_KEY, "closed");
    }

    // ============================================================
    // Toggle Sidebar
    // ============================================================
    function toggleSidebar() {
        if (isMobile()) {
            const isOpen = sidebar.classList.contains("sidebar-open");
            isOpen ? closeSidebar() : openSidebar();
        } else {
            const isHidden = sidebar.classList.contains("sidebar-hidden");
            isHidden ? openSidebar() : closeSidebar();
        }
    }

    // ============================================================
    // Restore State dari localStorage
    // ============================================================
    function restoreState() {
        if (isMobile()) {
            // Mobile selalu mulai tertutup
            closeSidebar();
            return;
        }

        const savedState = localStorage.getItem(STATE_KEY);
        // Default: terbuka di desktop
        if (savedState === "closed") {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // ============================================================
    // Handle Resize Window
    // ============================================================
    let resizeTimer;
    function handleResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (!isMobile()) {
                // Kembali ke desktop: hapus kelas mobile
                sidebar.classList.remove("sidebar-open");
                backdrop.classList.remove("active");

                // Terapkan state desktop dari localStorage
                const savedState = localStorage.getItem(STATE_KEY);
                if (savedState === "closed") {
                    sidebar.classList.add("sidebar-hidden");
                    mainWrapper.classList.add("sidebar-hidden");
                } else {
                    sidebar.classList.remove("sidebar-hidden");
                    mainWrapper.classList.remove("sidebar-hidden");
                }
            } else {
                // Kembali ke mobile: pastikan sidebar tertutup
                sidebar.classList.remove("sidebar-hidden");
                mainWrapper.classList.remove("sidebar-hidden");
                if (!sidebar.classList.contains("sidebar-open")) {
                    sidebar.classList.add("sidebar-hidden");
                    backdrop.classList.remove("active");
                }
            }
        }, 100);
    }

    // ============================================================
    // Event Listeners
    // ============================================================

    // Tombol toggle di navbar atas
    if (toggleBtn) {
        toggleBtn.addEventListener("click", toggleSidebar);
    }

    // Tombol X di dalam sidebar
    if (closeBtn) {
        closeBtn.addEventListener("click", closeSidebar);
    }

    // Klik backdrop di mobile untuk menutup sidebar
    if (backdrop) {
        backdrop.addEventListener("click", closeSidebar);
    }

    // Keyboard: tekan Escape untuk menutup sidebar
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeSidebar();
        }
    });

    // Responsive resize
    window.addEventListener("resize", handleResize);

    // ============================================================
    // Inisialisasi saat DOM ready
    // ============================================================
    document.addEventListener("DOMContentLoaded", function () {
        restoreState();
    });

    // Jika script di-load setelah DOMContentLoaded (defer/async)
    if (document.readyState !== "loading") {
        restoreState();
    }

    // Tambahkan di sidebar.js atau buat file terpisah
    // Letakkan setelah Bootstrap JS di admin.blade.php

    (function () {
        "use strict";

        // ============================================================
        // Toggle Profile Dropdown
        // ============================================================
        window.toggleProfileMenu = function () {
            const menu = document.getElementById("profileMenu");
            menu.classList.toggle("show");
        };

        // ============================================================
        // Tutup dropdown jika klik di luar area
        // ============================================================
        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("profileDropdown");
            const menu = document.getElementById("profileMenu");

            if (dropdown && menu && !dropdown.contains(e.target)) {
                menu.classList.remove("show");
            }
        });

        // ============================================================
        // Tutup dropdown jika tekan Escape
        // ============================================================
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                const menu = document.getElementById("profileMenu");
                if (menu) menu.classList.remove("show");
            }
        });
    })();
})();
