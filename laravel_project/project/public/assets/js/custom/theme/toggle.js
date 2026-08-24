(function () {
    "use strict";

    const root = document.documentElement;

    function apply(mode) {
        root.classList.remove("light-mode", "dark-mode");
        root.classList.add(mode + "-mode");
        root.setAttribute("data-bs-theme", mode === "dark" ? "dark" : "light");
        if (document.body) {
            document.body.classList.remove("light-mode", "dark-mode");
            document.body.classList.add(mode + "-mode");
        }
    }

    function current() {
        return root.classList.contains("dark-mode") ? "dark" : "light";
    }

    function updateIcon() {
        const btn = document.getElementById("themeToggle");
        if (!btn) return;
        btn.innerHTML = current() === "dark"
            ? '<i class="bi bi-moon fs-5"></i>'
            : '<i class="bi bi-sun fs-5"></i>';
    }

    // Apply stored theme as early as possible (head script already set <html>).
    apply(localStorage.getItem("theme") || "dark");

    function markLoaded() {
        root.classList.add("loaded");
        if (document.body) document.body.classList.add("loaded");
    }

    function wire() {
        apply(localStorage.getItem("theme") || "dark");
        updateIcon();
        markLoaded();

        const btn = document.getElementById("themeToggle");
        if (btn && !btn.dataset.themeBound) {
            btn.dataset.themeBound = "1";
            btn.addEventListener("click", function () {
                const next = current() === "dark" ? "light" : "dark";
                // Disable transitions during the swap to avoid color glitch/flicker
                root.classList.add("theme-switching");
                apply(next);
                localStorage.setItem("theme", next);
                updateIcon();
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        root.classList.remove("theme-switching");
                    });
                });
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", wire);
    } else {
        wire();
    }

    window.addEventListener("load", markLoaded);
    // Safety net: never let the loader block the UI
    setTimeout(markLoaded, 1200);
})();
