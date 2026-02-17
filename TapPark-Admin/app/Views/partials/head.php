<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<title><?= $title ?? 'TapPark Admin' ?></title>

<!-- CSS to prevent layout shift from scrollbars -->
<style>
/* Prevent layout shift when scrollbars appear/disappear */
html {
    scrollbar-gutter: stable; /* Reserve space for scrollbar */
    overflow-y: scroll; /* Always show scrollbar track */
}

body {
    overflow-x: hidden; /* Prevent horizontal scrollbar */
}

/* Alternative fallback for browsers that don't support scrollbar-gutter */
@supports not (scrollbar-gutter: stable) {
    html {
        overflow-y: scroll; /* Force scrollbar to always be visible */
    }
}

/* Main content handles display properly */
.main-content {
    min-height: 100vh;
}

/* Content wrapper stability */
.content-wrapper {
    width: 100%;
    box-sizing: border-box;
}

/* Prevent container width changes */
.container-fluid {
    width: 100%;
    max-width: none;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    box-sizing: border-box;
}

/* Card stability */
.card {
    box-sizing: border-box;
}

/* Ensure all sections maintain consistent alignment */
.row {
    box-sizing: border-box;
}

.col, .col-1, .col-2, .col-3, .col-4, .col-5, .col-6, 
.col-7, .col-8, .col-9, .col-10, .col-11, .col-12 {
    box-sizing: border-box;
}
</style>

<!-- Preconnect to CDN origins for faster asset loading -->
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<noscript>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
</noscript>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<noscript>
    <link rel="stylesheet" href="<?= base_url('assets/css/fontawesome.min.css') ?>">
</noscript>

<!-- Custom Theme CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/theme.css') ?: time() ?>">

<!-- Layout CSS -->
<link rel="stylesheet" href="<?= base_url('assets/css/layout.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/layout.css') ?: time() ?>">

