<?php
/**
 * Navbar Superior de AdminLTE v4
 */

// Consulta de alertas operativas del sistema en tiempo real
$alertasSistema = [];
$totalAlertas = 0;

try {
    $dbAlerts = \App\Config\Database::conectar();
    $hoy = date('Y-m-d');

    // 1. Check-ins de hoy pendientes
    $stmtIn = $dbAlerts->prepare("SELECT COUNT(*) FROM reservas WHERE fecha_entrada = :hoy AND estado = 'confirmada'");
    $stmtIn->execute([':hoy' => $hoy]);
    $checkinsPendientes = (int)$stmtIn->fetchColumn();
    if ($checkinsPendientes > 0) {
        $alertasSistema[] = [
            'tipo'     => 'checkin',
            'icono'    => 'bi-box-arrow-in-right',
            'color'    => 'success',
            'titulo'   => "$checkinsPendientes Check-in(s) para hoy",
            'desc'     => 'Huéspedes programados para ingresar hoy',
            'enlace'   => url('recepcion/checkin'),
            'badge'    => 'Hoy',
            'badge_bg' => 'bg-success-subtle text-success border border-success-subtle'
        ];
        $totalAlertas += $checkinsPendientes;
    }

    // 2. Check-outs de hoy pendientes
    $stmtOut = $dbAlerts->prepare("SELECT COUNT(*) FROM reservas WHERE fecha_salida = :hoy AND estado = 'en_estadia'");
    $stmtOut->execute([':hoy' => $hoy]);
    $checkoutsPendientes = (int)$stmtOut->fetchColumn();
    if ($checkoutsPendientes > 0) {
        $alertasSistema[] = [
            'tipo'     => 'checkout',
            'icono'    => 'bi-box-arrow-right',
            'color'    => 'danger',
            'titulo'   => "$checkoutsPendientes Check-out(s) para hoy",
            'desc'     => 'Huéspedes que deben desocupar habitación hoy',
            'enlace'   => url('recepcion/checkout'),
            'badge'    => 'Salida',
            'badge_bg' => 'bg-danger-subtle text-danger border border-danger-subtle'
        ];
        $totalAlertas += $checkoutsPendientes;
    }

    // 3. Habitaciones en mantenimiento
    $stmtMant = $dbAlerts->query("SELECT COUNT(*) FROM habitaciones WHERE estado = 'mantenimiento'");
    $habsMantenimiento = (int)$stmtMant->fetchColumn();
    if ($habsMantenimiento > 0) {
        $alertasSistema[] = [
            'tipo'     => 'mantenimiento',
            'icono'    => 'bi-tools',
            'color'    => 'warning',
            'titulo'   => "$habsMantenimiento Habitación(es) en mantenimiento",
            'desc'     => 'Habitaciones fuera de servicio o en limpieza',
            'enlace'   => url('habitaciones/mantenimiento'),
            'badge'    => 'Atención',
            'badge_bg' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
        ];
        $totalAlertas += $habsMantenimiento;
    }

    // 4. Reservas pendientes de pago
    $stmtPend = $dbAlerts->query("SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente'");
    $reservasPendientes = (int)$stmtPend->fetchColumn();
    if ($reservasPendientes > 0) {
        $alertasSistema[] = [
            'tipo'     => 'pago',
            'icono'    => 'bi-clock-history',
            'color'    => 'info',
            'titulo'   => "$reservasPendientes Reserva(s) pendientes de pago",
            'desc'     => 'Reservas sin comprobante o saldo por cobrar',
            'enlace'   => url('reservas?estado=pendiente'),
            'badge'    => 'Pendiente',
            'badge_bg' => 'bg-info-subtle text-info-emphasis border border-info-subtle'
        ];
        $totalAlertas += $reservasPendientes;
    }

    // 5. Turno de Caja (si no hay turno activo)
    $stmtCaja = $dbAlerts->query("SELECT COUNT(*) FROM turnos_caja WHERE estado = 'abierto'");
    $cajaAbierta = (int)$stmtCaja->fetchColumn();
    if ($cajaAbierta === 0) {
        $alertasSistema[] = [
            'tipo'     => 'caja',
            'icono'    => 'bi-cash-coin',
            'color'    => 'danger',
            'titulo'   => 'Caja cerrada sin turno activo',
            'desc'     => 'Abre un turno para registrar cobros y anticipos',
            'enlace'   => url('caja/apertura'),
            'badge'    => 'Caja',
            'badge_bg' => 'bg-danger-subtle text-danger border border-danger-subtle'
        ];
        $totalAlertas += 1;
    }
} catch (\Throwable $e) {
    // Tolerancia ante cualquier excepción
}
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
        </ul>

        <!-- Acciones rápidas: una sola principal (Nueva Reserva); las demás en gris para no competir -->
        <div class="d-none d-md-flex align-items-center gap-2 ms-auto me-3">
            <a href="<?= url('huespedes/buscar') ?>" class="btn btn-outline-secondary btn-nav-action" title="Atajo: Presiona F3">
                <i class="bi bi-search me-1"></i> Buscar DNI <kbd class="nav-kbd">F3</kbd>
            </a>
            <a href="<?= url('recepcion/checkin') ?>" class="btn btn-outline-secondary btn-nav-action" title="Atajo: Presiona F4">
                <i class="bi bi-box-arrow-in-right me-1"></i> Check-in <kbd class="nav-kbd">F4</kbd>
            </a>
            <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-nav-action" title="Atajo: Presiona F2 o Ctrl+K">
                <i class="bi bi-plus-lg me-1"></i> Nueva Reserva <kbd class="nav-kbd nav-kbd-on-primary">F2</kbd>
            </a>
        </div>

        <!-- Menú de la derecha: Tema & Perfil -->
        <ul class="navbar-nav d-flex align-items-center gap-2">
            <!-- Interruptor Tic-Tac Modo Claro / Oscuro -->
            <li class="nav-item d-flex align-items-center me-1">
                <button type="button" class="theme-toggle-switch" id="btnThemeToggle" title="Cambiar a Modo Oscuro / Claro" aria-label="Cambiar tema">
                    <span class="theme-switch-track">
                        <span class="theme-switch-icon icon-sun">
                            <i class="bi bi-sun-fill"></i>
                        </span>
                        <span class="theme-switch-icon icon-moon">
                            <i class="bi bi-moon-stars-fill"></i>
                        </span>
                        <span class="theme-switch-thumb shadow-xs">
                            <i class="bi bi-sun-fill thumb-icon-sun"></i>
                            <i class="bi bi-moon-stars-fill thumb-icon-moon"></i>
                        </span>
                    </span>
                </button>
            </li>

            <!-- Botón de Alertas y Notificaciones Operativas -->
            <li class="nav-item dropdown me-1">
                <a class="nav-link btn-nav-alert position-relative p-2 d-flex align-items-center justify-content-center rounded-circle" 
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Centro de Alertas">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if ($totalAlertas > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light nav-alert-badge">
                            <?= $totalAlertas > 99 ? '99+' : $totalAlertas ?>
                            <span class="visually-hidden">alertas pendientes</span>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0 p-0 alert-dropdown-menu">
                    <!-- Cabecera del Panel de Alertas -->
                    <div class="dropdown-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-bell-fill"></i>
                            <span class="fw-bold fs-6">Centro de Alertas</span>
                        </div>
                        <?php if ($totalAlertas > 0): ?>
                            <span class="badge bg-white bg-opacity-25 text-white fw-semibold">
                                <?= $totalAlertas ?> <?= $totalAlertas === 1 ? 'alerta' : 'alertas' ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-white bg-opacity-25 text-white fw-semibold">Al día</span>
                        <?php endif; ?>
                    </div>

                    <!-- Lista de Alertas -->
                    <div class="alert-list-container">
                        <?php if (!empty($alertasSistema)): ?>
                            <?php foreach ($alertasSistema as $alerta): ?>
                                <a href="<?= $alerta['enlace'] ?>" class="dropdown-item d-flex align-items-start gap-3 py-2 px-3 border-bottom alert-item-hover">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white bg-<?= $alerta['color'] ?> shadow-xs avatar-sm">
                                        <i class="bi <?= $alerta['icono'] ?>"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                                            <strong class="text-body fw-bold text-truncate text-sm"><?= htmlspecialchars($alerta['titulo']) ?></strong>
                                            <span class="text-2xs badge <?= $alerta['badge_bg'] ?>"><?= htmlspecialchars($alerta['badge']) ?></span>
                                        </div>
                                        <p class="text-sm text-muted mb-0 small text-truncate"><?= htmlspecialchars($alerta['desc']) ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <div class="mb-2">
                                    <i class="bi bi-shield-check text-success display-5"></i>
                                </div>
                                <h6 class="fw-bold text-body mb-1">¡Todo en orden!</h6>
                                <p class="small text-muted mb-0">No hay alertas operativas pendientes en el hotel.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pie de Alertas -->
                    <div class="dropdown-footer p-2 text-center bg-body-tertiary border-top d-flex justify-content-around">
                        <a href="<?= url('recepcion/estadias') ?>" class="small text-decoration-none fw-semibold">
                            <i class="bi bi-door-open me-1"></i>Recepción
                        </a>
                        <span class="text-muted">•</span>
                        <a href="<?= url('reservas') ?>" class="small text-decoration-none fw-semibold">
                            <i class="bi bi-calendar-check me-1"></i>Reservas
                        </a>
                        <span class="text-muted">•</span>
                        <a href="<?= url('habitaciones/estados') ?>" class="small text-decoration-none fw-semibold">
                            <i class="bi bi-grid me-1"></i>Habitaciones
                        </a>
                    </div>
                </div>
            </li>

            <!-- Perfil de Usuario Autenticado -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-0" data-bs-toggle="dropdown">
                    <img src="<?= asset('adminlte/img/avatar.png') ?>" class="user-image rounded-circle shadow-xs" alt="User Image" />
                    <span class="d-none d-md-inline fw-semibold text-truncate mw-140"><?= htmlspecialchars(auth_user('nombre') ?? 'Usuario') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                    <li class="user-header text-bg-primary">
                        <img src="<?= asset('adminlte/img/avatar.png') ?>" class="rounded-circle shadow" alt="User Image" />
                        <p>
                            <?= htmlspecialchars(auth_user('nombre') ?? 'Usuario') ?>
                            <small class="d-block text-white-50"><?= htmlspecialchars(auth_user('email') ?? '') ?></small>
                            <span class="badge badge-soft-secondary mt-1"><?= htmlspecialchars(auth_user('rol') ?? 'Administrador') ?></span>
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
