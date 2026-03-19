// public/js/auth/login.js

(function () {
    "use strict";

    // ============================================================
    // Toggle Show / Hide Password
    // ============================================================
    const toggleBtn = document.getElementById("togglePasswordBtn");
    const passwordInput = document.getElementById("password");
    const iconEye = document.getElementById("iconEye");
    const iconEyeOff = document.getElementById("iconEyeOff");

    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener("click", function () {
            const isPassword = passwordInput.type === "password";

            // Ganti type input
            passwordInput.type = isPassword ? "text" : "password";

            // Ganti icon
            iconEye.style.display = isPassword ? "none" : "block";
            iconEyeOff.style.display = isPassword ? "block" : "none";

            // Kembalikan focus ke input setelah klik tombol
            passwordInput.focus();
        });
    }
})();
