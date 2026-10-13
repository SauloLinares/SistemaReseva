<?php
/**
 * Sidebar Lateral de AdminLTE v4
 */
$rutaActual = $_GET['url'] ?? '';
?>
<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <a href="<?= url() ?>" class="brand-link">
            <img src="<?= asset('adminlte/img/AdminLTELogo.png') ?>" alt="Logo" class="brand-image opacity-75 shadow rounded" />
            <span class="brand-text fw-light">Sistema Reserva</span>
        </a>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <li class="nav-header">PRINCIPAL</li>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= url() ?>" class="nav-link <?= (empty($rutaActual) || $rutaActual === 'home') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">ADMINISTRACIÓN</li>

                <!-- Módulo Clientes -->
                <li class="nav-item <?= str_starts_with($rutaActual, 'clientes') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= str_starts_with($rutaActual, 'clientes') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>
                            Clientes
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('clientes') ?>" class="nav-link <?= ($rutaActual === 'clientes') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Lista de Clientes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('clientes/crear') ?>" class="nav-link <?= ($rutaActual === 'clientes/crear') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Nuevo Cliente</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header">AYUDA Y GUÍA</li>

                <li class="nav-item">
                    <a href="https://adminlte.io/docs/4.0/" target="_blank" class="nav-link text-info">
                        <i class="nav-icon bi bi-question-circle-fill"></i>
                        <p>Docs AdminLTE v4</p>
                    </a>
                </li>
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
