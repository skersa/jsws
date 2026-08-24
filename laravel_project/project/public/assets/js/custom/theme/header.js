document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("themeToggle");
    if (!btn) return;

    function setTheme(mode) {
        document.documentElement.classList.remove("light-mode", "dark-mode");
        document.documentElement.classList.add(mode + "-mode");
        localStorage.setItem("theme", mode);
    }

    function updateIcon() {
        btn.innerHTML =
            document.documentElement.classList.contains("dark-mode")
                ? '<i class="bi bi-moon fs-5"></i>'
                : '<i class="bi bi-sun fs-5"></i>';
    }

    updateIcon();

    btn.addEventListener("click", function () {
        const isDark = document.documentElement.classList.contains("dark-mode");
        setTheme(isDark ? "light" : "dark");
        updateIcon();
    });

});
