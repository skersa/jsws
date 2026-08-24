document.addEventListener("DOMContentLoaded", () => {
            applyTheme(localStorage.getItem('theme') || 'dark');
        });
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            document.getElementById('theme-icon').className = theme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
        function toggleTheme() {
            applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        }

/* === maintenance.blade.php === */
document.addEventListener("DOMContentLoaded", () => {
            applyTheme(localStorage.getItem('theme') || 'dark');
        });
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            document.getElementById('theme-icon').className = theme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
        function toggleTheme() {
            applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        }