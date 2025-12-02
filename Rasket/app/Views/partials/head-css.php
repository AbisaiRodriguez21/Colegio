<!-- Vendor css -->
<link href="<?= base_url('css/vendor.min.css') ?>" rel="stylesheet" type="text/css" />

<!-- Icons css -->
<link href="<?= base_url('css/icons.min.css') ?>" rel="stylesheet" type="text/css" />

<!-- App css -->
<link href="<?= base_url('css/app.min.css') ?>" rel="stylesheet" type="text/css" />
<style>
/* Oculta el ícono del sol cuando el tema es claro */
[data-bs-theme="light"] .dark-mode {
    display: none !important;
}

/* Oculta el ícono de la luna cuando el tema es oscuro */
[data-bs-theme="dark"] .light-mode {
    display: none !important;
}
</style>
