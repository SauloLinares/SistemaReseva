<?php
/**
 * Navbar Superior de AdminLTE v4
 */
?>


<!--begin::Header-->
<nav class="app-header navbar navbar-expand sticky-top bg-body shadow-xs">
    <div class="container-fluid px-3">
        <!-- Navegación Izquierda: Toggle & Enlaces de navegación -->
        <ul class="navbar-nav d-flex align-items-center gap-1">
            <li class="nav-item">
                <a class="nav-link btn-nav-toggle" data-lte-toggle="sidebar" href="#" role="button" title="Colapsar / Expandir menú">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center">
                <a href="<?= url() ?>" class="nav-link nav-link-custom fw-semibold">
                    <i class="bi bi-speedometer2 me-1 text-primary"></i> Dashboard
                </a>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center">
                <a href="<?= url('reservas/disponibilidad') ?>" class="nav-link nav-link-custom text-body-secondary">
                    <i class="bi bi-calendar-range me-1"></i> Disponibilidad
                </a>
            </li>
            <li class="nav-item d-none d-lg-flex align-items-center">
                <a href="<?= url('reservas/calendario') ?>" class="nav-link nav-link-custom text-body-secondary">
                    <i class="bi bi-calendar3 me-1"></i> Calendario
                </a>
            </li>
        </ul>

        <!-- Acciones Rápidas Centrales / Derecha: Botones alineados uniformemente -->
        <div class="d-none d-md-flex align-items-center gap-2 ms-auto me-3">
            <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-nav-action" title="Atajo: Presiona F2 o Ctrl+K">
                <i class="bi bi-plus-circle me-1"></i> Nueva Reserva <kbd class="nav-kbd bg-white bg-opacity-25 text-white">F2</kbd>
            </a>
            <a href="<?= url('recepcion/checkin') ?>" class="btn btn-outline-success btn-nav-action" title="Atajo: Presiona F4">
                <i class="bi bi-box-arrow-in-right me-1"></i> Check-in <kbd class="nav-kbd bg-success-subtle text-success">F4</kbd>
            </a>
        </div>

        <!-- Menú de la derecha: Tema & Perfil -->
        <ul class="navbar-nav d-flex align-items-center gap-2">
            <!-- Cambio de Modo Claro / Oscuro -->
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link py-0 px-2 d-flex align-items-center"
                        id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown"
                        data-bs-display="static" title="Cambiar tema">
                    <span class="theme-icon-active fs-5"><i class="bi bi-circle-half"></i></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light">
                            <i class="bi bi-sun-fill me-2 opacity-50"></i> Claro
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark">
                            <i class="bi bi-moon-stars-fill me-2 opacity-50"></i> Oscuro
                        </button>
                    </li>
                </ul>
            </li>

            <!-- Perfil de Usuario Autenticado -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-0" data-bs-toggle="dropdown">
                    <img src="<?= asset('adminlte/img/avatar.png') ?>" class="user-image rounded-circle shadow-xs" alt="User Image" style="width: 32px; height: 32px; object-fit: cover;" />
                    <span class="d-none d-md-inline fw-semibold text-truncate" style="max-width: 130px;"><?= htmlspecialchars(auth_user('nombre') ?? 'Usuario') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                    <li class="user-header text-bg-primary">
                        <img src="<?= asset('adminlte/img/avatar.png') ?>" class="rounded-circle shadow" alt="User Image" />
                        <p>
                            <?= htmlspecialchars(auth_user('nombre') ?? 'Usuario') ?>
                            <small class="d-block text-white-50"><?= htmlspecialchars(auth_user('email') ?? '') ?></small>
                            <span class="badge bg-light text-dark mt-1"><?= htmlspecialchars(auth_user('rol') ?? 'Administrador') ?></span>
                        </p>
                    </li>
                    <li class="user-footer d-flex justify-content-between">
                        <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-people-fill me-1"></i> Clientes
                        </a>
                        <a href="<?= url('login/logout') ?>" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<!--end::Header-->
