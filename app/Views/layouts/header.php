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
    <title><?= htmlspecialchars($pageTitle ?? 'Sistema de Reserva') ?> | AdminLTE</title>

    <!-- Script de inicialización de tema claro/oscuro (AdminLTE v4) -->
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
      })();
    </script>

    <!-- Fuentes de Google -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />

    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= asset('adminlte/css/adminlte.min.css') ?>" />

    <!-- Estilos personalizados para los alumnos -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>" />
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
