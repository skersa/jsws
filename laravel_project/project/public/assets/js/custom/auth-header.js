(function () {
        const t = localStorage.getItem("theme") || "dark";
        document.documentElement.classList.add(t + "-mode");
    })();
