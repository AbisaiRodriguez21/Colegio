<!-- Vendor Javascript -->
<script src="<?= base_url('js/vendor.js') ?>"></script>

<!-- App Javascript -->
<script src="<?= base_url('js/app.js') ?>"></script>

<!-- Script para alternar modo claro/oscuro -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const html = document.documentElement;
    const themeToggle = document.querySelector('[data-toggle="theme"]');

    // Cargar tema guardado o usar por defecto "light"
    const savedTheme = localStorage.getItem("theme") || "light";
    html.setAttribute("data-bs-theme", savedTheme);
    document.body.classList.toggle("dark-mode-active", savedTheme === "dark");

    // Cambiar entre claro/oscuro al hacer clic
    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            const current = html.getAttribute("data-bs-theme");
            const next = current === "dark" ? "light" : "dark";
            html.setAttribute("data-bs-theme", next);
            localStorage.setItem("theme", next);

            // Sincronizar colores del menú y la barra superior
            if (next === "dark") {
                html.setAttribute("data-menu-color", "dark");
                html.setAttribute("data-topbar-color", "dark");
            } else {
                html.setAttribute("data-menu-color", "light");
                html.setAttribute("data-topbar-color", "light");
            }
        });
    }
});
</script>
