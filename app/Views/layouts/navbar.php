<?php
/**
 * Navbar Superior de AdminLTE v4
 */
?>
<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <!-- Botón para colapsar sidebar y enlaces rápidos -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="<?= url() ?>" class="nav-link">Inicio</a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="<?= url('clientes') ?>" class="nav-link">Clientes</a>
            </li>
        </ul>

        <!-- Menú de la derecha -->
        <ul class="navbar-nav ms-auto align-items-center">
            <!-- Cambio de Modo Claro / Oscuro -->
            <li class="nav-item dropdown">
                <button class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center"
                        id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown"
                        data-bs-display="static">
                    <span class="theme-icon-active"><i class="bi bi-circle-half"></i></span>
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

            <!-- Perfil de Usuario -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?= asset('adminlte/img/avatar.png') ?>" class="user-image rounded-circle shadow" alt="User Image" />
                    <span class="d-none d-md-inline">Profesor / Alumno</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <img src="<?= asset('adminlte/img/avatar.png') ?>" class="rounded-circle shadow" alt="User Image" />
                        <p>
                            Plantilla Educativa PHP POO
                            <small>Arquitectura MVC con AdminLTE</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <a href="<?= url('clientes') ?>" class="btn btn-default btn-flat">Módulos</a>
                        <a href="<?= url() ?>" class="btn btn-default btn-flat float-end">Inicio</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<!--end::Header-->
