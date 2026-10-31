<?php
/**
 * Header del Layout de AdminLTE v4
 */
?>
<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>" />
    <title><?= htmlspecialchars($pageTitle ?? 'Sistema de Reserva') ?> | AdminLTE</title>

    <!-- Script de inicialización de tema claro/oscuro & estado del sidebar (AdminLTE v4) -->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try { stored = localStorage.getItem(STORAGE_KEY); } catch {}
        const authored = root.getAttribute('data-bs-theme');
        let resolved = stored || authored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        root.setAttribute('data-bs-theme', resolved);

        // Prevenir parpadeo del sidebar si fue colapsado por el usuario
        try {
          if (localStorage.getItem('lte.sidebar.state') === 'collapsed' && window.innerWidth >= 992) {
            document.addEventListener('DOMContentLoaded', () => {
              document.body.classList.add('sidebar-collapse');
            });
          }
        } catch {}
      })();
    </script>

    <!-- Fuentes de Google: Plus Jakarta Sans & Source Sans 3 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />

    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= asset('adminlte/css/adminlte.min.css') ?>" />

    <!-- TomSelect CSS (Selector con búsqueda instantánea) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" />

    <!-- SweetAlert2 CSS (Alertas y diálogos modernos) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.0/dist/sweetalert2.min.css" />

    <!-- Simple-DataTables CSS (Tablas interactivas ligeras) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@9.2.1/dist/style.css" />

    <!-- Estilos personalizados con control de caché dinámico -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>?v=<?= file_exists(__DIR__ . '/../../../public/assets/css/custom.css') ? filemtime(__DIR__ . '/../../../public/assets/css/custom.css') : time() ?>" />
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
