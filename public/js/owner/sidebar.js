(function () {
    "use strict";

    const sidebar = document.getElementById("sidebar");
    const mainWrapper = document.getElementById("mainWrapper");
    const toggleBtn = document.getElementById("sidebarToggleBtn");
    const backdrop = document.getElementById("sidebarBackdrop");

    // State key berbeda dari admin agar tidak bentrok
    const STATE_KEY = "grosync_owner_sidebar_open";

    function isMobile() {
        return window.innerWidth < 768;
    }

    function openSidebar() {
        if (isMobile()) {
            sidebar.classList.add("sidebar-open");
            sidebar.classList.remove("sidebar-hidden");
            backdrop.classList.add("active");
        } else {
            sidebar.classList.remove("sidebar-hidden");
            mainWrapper.classList.remove("sidebar-hidden");
        }
        localStorage.setItem(STATE_KEY, "open");
    }

    function closeSidebar() {
        if (isMobile()) {
            sidebar.classList.remove("sidebar-open");
            sidebar.classList.add("sidebar-hidden");
            backdrop.classList.remove("active");
        } else {
            sidebar.classList.add("sidebar-hidden");
            mainWrapper.classList.add("sidebar-hidden");
        }
        localStorage.setItem(STATE_KEY, "closed");
    }

    function toggleSidebar() {
        if (isMobile()) {
            sidebar.classList.contains("sidebar-open")
                ? closeSidebar()
                : openSidebar();
        } else {
            sidebar.classList.contains("sidebar-hidden")
                ? openSidebar()
                : closeSidebar();
        }
    }

    function restoreState() {
        if (isMobile()) {
            closeSidebar();
            return;
        }
        const savedState = localStorage.getItem(STATE_KEY);
        savedState === "closed" ? closeSidebar() : openSidebar();
    }

    let resizeTimer;
    function handleResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (!isMobile()) {
                sidebar.classList.remove("sidebar-open");
                backdrop.classList.remove("active");
                const savedState = localStorage.getItem(STATE_KEY);
                if (savedState === "closed") {
                    sidebar.classList.add("sidebar-hidden");
                    mainWrapper.classList.add("sidebar-hidden");
                } else {
                    sidebar.classList.remove("sidebar-hidden");
                    mainWrapper.classList.remove("sidebar-hidden");
                }
            } else {
                sidebar.classList.remove("sidebar-hidden");
                mainWrapper.classList.remove("sidebar-hidden");
                if (!sidebar.classList.contains("sidebar-open")) {
                    sidebar.classList.add("sidebar-hidden");
                    backdrop.classList.remove("active");
                }
            }
        }, 100);
    }

    if (toggleBtn) toggleBtn.addEventListener("click", toggleSidebar);
    if (backdrop) backdrop.addEventListener("click", closeSidebar);

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeSidebar();
    });

    window.addEventListener("resize", handleResize);

    document.addEventListener("DOMContentLoaded", function () {
        restoreState();
    });

    if (document.readyState !== "loading") {
        restoreState();
    }

    // ============================================================
    // Profile Dropdown
    // ============================================================
    (function () {
        "use strict";

        window.toggleProfileMenu = function () {
            const menu = document.getElementById("profileMenu");
            if (menu) menu.classList.toggle("show");
        };

        document.addEventListener("click", function (e) {
            const dropdown = document.getElementById("profileDropdown");
            const menu = document.getElementById("profileMenu");
            if (dropdown && menu && !dropdown.contains(e.target)) {
                menu.classList.remove("show");
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                const menu = document.getElementById("profileMenu");
                if (menu) menu.classList.remove("show");
            }
        });
    })();
})();
