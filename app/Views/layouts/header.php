<?php
/**
 * Header del Layout de AdminLTE v4
 */
// Datos del hotel: nombre para el título y logo para el favicon (también los usa sidebar.php)
$configApp = (new \App\Models\Configuracion())->obtenerTodos();
$nombreEmpresa = $configApp['hotel_nombre'] ?? 'Hotel Manager';
$logoArchivo = $configApp['hotel_logo'] ?? '';
$hayLogo = $logoArchivo !== '' && file_exists(__DIR__ . '/../../../public/uploads/hotel/' . $logoArchivo);
// Algunos controladores ya terminan el título en "- Hotel Manager": se quita para no repetir el nombre
$pageTitle = preg_replace('/\s*[-|·]\s*Hotel Manager\s*$/u', '', $pageTitle ?? 'Panel');
?>
<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>" />
    <meta name="description" content="Sistema de gestión hotelera de <?= e($nombreEmpresa) ?>: reservas, recepción, caja y reportes." />
    <meta name="theme-color" content="#f8fafc" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#0b1120" media="(prefers-color-scheme: dark)" />
    <title><?= e($pageTitle ?? 'Panel') ?> · <?= e($nombreEmpresa) ?></title>
    <?php if ($hayLogo): ?>
        <link rel="icon" href="<?= e(base_url('uploads/hotel/' . $logoArchivo)) ?>" />
        <link rel="apple-touch-icon" href="<?= e(base_url('uploads/hotel/' . $logoArchivo)) ?>" />
    <?php else: ?>
        <!-- Sin logo: icono de edificio en el color primario -->
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Crect width='16' height='16' rx='3' fill='%232563eb'/%3E%3Cpath fill='white' d='M4 3h5v10H4zM10 6h2v7h-2z'/%3E%3C/svg%3E" />
    <?php endif; ?>

    <!-- Tema claro/oscuro y estado del menú: antes de pintar para evitar parpadeo -->
    <?= script_js('tema.js') ?>

    <!-- Datos para el JavaScript (no se ejecuta: la CSP no permite scripts inline) -->
    <script type="application/json" id="app-config"><?= json_encode([
        'baseUrl'  => base_url(),
        'urlRutas' => base_url('index.php?url='),
        'usuario'  => (int) (auth_user('id') ?? 0),
        'moneda'   => moneda(),
        // Atajos globales (F2, F3, F4) solo hacia pantallas que el rol puede abrir
        'sesionMinutos' => auth_check() ? \App\Config\App::sesionMinutos() : 0,
        'atajos'   => array_values(array_filter(['reservas/crear', 'huespedes/buscar', 'recepcion/checkin'], 'puede_ruta')),
    ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?= script_js('nucleo.js') ?>

    <!-- Fuente del sistema: Plus Jakarta Sans (Source Sans 3 de AdminLTE se quitó: ningún elemento la usaba) -->
    <!-- Precarga de los pesos más usados: así el texto y las tablas se miden con la fuente final
         (DataTables calcula el ancho de columnas al cargar; con la fuente de respaldo "saltaban") -->
    <?php foreach ([400, 600, 700] as $peso): ?>
        <link rel="preload" href="<?= asset("vendor/fonts/plus-jakarta-sans/plus-jakarta-sans-latin-{$peso}-normal.woff2") ?>" as="font" type="font/woff2" crossorigin />
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= asset('vendor/fonts/plus-jakarta-sans/fuente.css') ?>" />

    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="<?= asset('vendor/overlayscrollbars/overlayscrollbars.min.css') ?>" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" />

    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= asset('adminlte/css/adminlte.min.css') ?>" />

    <!-- TomSelect y Simple-DataTables: data-lib permite quitarlos de las páginas que no los usan
         (ver quitar_librerias_sin_uso). SweetAlert2 trae su CSS dentro de sweetalert2.all.min.js. -->
    <link rel="stylesheet" data-lib="tomselect" href="<?= asset('vendor/tom-select/tom-select.bootstrap5.min.css') ?>" />
    <link rel="stylesheet" data-lib="datatables" href="<?= asset('vendor/simple-datatables/style.css') ?>" />

    <!-- Estilos personalizados con control de caché dinámico -->
    <link rel="stylesheet" href="<?= e(asset_version('css/custom.css')) ?>" />
</head>
<body class="layout-fixed fixed-header sidebar-expand-lg bg-body-tertiary">
    <!-- Toast Container Flotante para notificaciones no invasivas -->
    <div class="toast-container position-fixed top-0 end-0 p-3 app-toasts" id="toastContainer">
        <?php if (!empty($_SESSION['flash'])): ?>
            <?php 
                $flashTipo = $_SESSION['flash']['tipo'] ?? 'info';
                $flashIcon = match($flashTipo) {
                    'success' => 'bi-check-circle-fill text-success',
                    'danger'  => 'bi-exclamation-triangle-fill text-danger',
                    'warning' => 'bi-exclamation-circle-fill text-warning',
                    default   => 'bi-info-circle-fill text-primary'
                };
                $flashTitulo = match($flashTipo) {
                    'success' => 'Operación Exitosa',
                    'danger'  => 'Atención / Error',
                    'warning' => 'Advertencia',
                    default   => 'Notificación del Sistema'
                };
            ?>
            <div class="toast show shadow-lg border-0 fade" role="alert" aria-live="assertive" aria-atomic="true" id="sessionFlashToast" data-bs-delay="6000" data-bs-autohide="true">
                <div class="toast-header border-bottom-0 pb-1">
                    <i class="bi <?= $flashIcon ?> me-2 fs-5"></i>
                    <strong class="me-auto"><?= $flashTitulo ?></strong>
                    <small class="text-muted">ahora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body pt-1 text-secondary">
                    <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                </div>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
    </div>

    <!--begin::App Wrapper-->
    <div class="app-wrapper">
