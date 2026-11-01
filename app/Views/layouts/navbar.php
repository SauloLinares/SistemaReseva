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
            'ruta'     => 'recepcion/checkin',
            'enlace'   => url('recepcion/checkin'),
            'badge'    => 'Hoy',
            'badge_bg' => 'bg-success-subtle text-success border border-success-subtle',
            'cantidad' => $checkinsPendientes,
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
            'ruta'     => 'recepcion/checkout',
            'enlace'   => url('recepcion/checkout'),
            'badge'    => 'Salida',
            'badge_bg' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'cantidad' => $checkoutsPendientes,
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
            'ruta'     => 'habitaciones/mantenimiento',
            'enlace'   => url('habitaciones/mantenimiento'),
            'badge'    => 'Atención',
            'badge_bg' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'cantidad' => $habsMantenimiento,
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
            'ruta'     => 'reservas?estado=pendiente',
            'enlace'   => url('reservas?estado=pendiente'),
            'badge'    => 'Pendiente',
            'badge_bg' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
            'cantidad' => $reservasPendientes,
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
            'ruta'     => 'caja',
            'enlace'   => url('caja'),
            'badge'    => 'Caja',
            'badge_bg' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'cantidad' => 1,
        ];
        $totalAlertas += 1;
    }
} catch (\Throwable $e) {
    // Tolerancia ante cualquier excepción
}

// Solo las alertas de pantallas que el rol del usuario puede abrir
$alertasSistema = array_values(array_filter($alertasSistema, fn($a) => puede_ruta($a['ruta'])));
$totalAlertas = array_sum(array_column($alertasSistema, 'cantidad'));
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
            <?php if (puede_ruta('huespedes/buscar')): ?>
            <a href="<?= url('huespedes/buscar') ?>" class="btn btn-outline-secondary btn-nav-action" title="Atajo: Presiona F3">
                <i class="bi bi-search me-1"></i> Buscar DNI <kbd class="nav-kbd">F3</kbd>
            </a>
            <?php endif; ?>
            <?php if (puede_ruta('recepcion/checkin')): ?>
            <a href="<?= url('recepcion/checkin') ?>" class="btn btn-outline-secondary btn-nav-action" title="Atajo: Presiona F4">
                <i class="bi bi-box-arrow-in-right me-1"></i> Check-in <kbd class="nav-kbd">F4</kbd>
            </a>
            <?php endif; ?>
            <?php if (puede_ruta('reservas/crear')): ?>
            <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-nav-action" title="Atajo: Presiona F2 o Ctrl+K">
                <i class="bi bi-plus-lg me-1"></i> Nueva Reserva <kbd class="nav-kbd nav-kbd-on-primary">F2</kbd>
            </a>
            <?php endif; ?>
        </div>

        <!-- En móvil las acciones rápidas se ocultan: queda un acceso directo a Nueva Reserva -->
        <?php if (puede_ruta('reservas/crear')): ?>
        <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-icon d-md-none ms-auto me-2" title="Nueva reserva" aria-label="Nueva reserva">
            <i class="bi bi-plus-lg"></i>
        </a>
        <?php endif; ?>

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
                        <span class="badge rounded-pill bg-danger nav-alert-badge">
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
                    <div class="dropdown-footer p-2 text-center bg-body-tertiary border-top d-flex justify-content-around gap-2">
                        <?php if (puede_ruta('recepcion/estadias')): ?>
                        <a href="<?= url('recepcion/estadias') ?>" class="small text-decoration-none fw-semibold">
                            <i class="bi bi-door-open me-1"></i>Recepción
                        </a>
                        <?php endif; ?>
                        <?php if (puede_ruta('reservas')): ?>
                        <a href="<?= url('reservas') ?>" class="small text-decoration-none fw-semibold">
                            <i class="bi bi-calendar-check me-1"></i>Reservas
                        </a>
                        <?php endif; ?>
                        <?php if (puede_ruta('habitaciones/estados')): ?>
                        <a href="<?= url('habitaciones/estados') ?>" class="small text-decoration-none fw-semibold">
                            <i class="bi bi-grid me-1"></i>Habitaciones
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>

            <!-- Cuenta del usuario: Mi perfil, contraseña y salir -->
            <?php $nombreUsuario = auth_user('nombre') ?? 'Usuario'; ?>
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-0" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mi cuenta">
                    <?= avatar_usuario(auth_user('foto'), $nombreUsuario, 'avatar-sm shadow-xs') ?>
                    <span class="d-none d-md-inline fw-semibold text-truncate mw-140"><?= e($nombreUsuario) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow menu-cuenta">
                    <li class="px-3 py-2 d-flex align-items-center gap-3">
                        <?= avatar_usuario(auth_user('foto'), $nombreUsuario, 'avatar-lg') ?>
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate"><?= e($nombreUsuario) ?></div>
                            <div class="small text-body-secondary text-truncate"><?= e(auth_user('email') ?? '') ?></div>
                            <span class="badge badge-soft-secondary mt-1"><?= e(auth_user('rol') ?? '') ?></span>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('perfil') ?>"><i class="bi bi-person-circle me-2"></i>Mi perfil</a></li>
                    <li><a class="dropdown-item" href="<?= url('perfil/clave') ?>"><i class="bi bi-key me-2"></i>Cambiar contraseña</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button type="submit" form="formCerrarSesion" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<!--end::Header-->
