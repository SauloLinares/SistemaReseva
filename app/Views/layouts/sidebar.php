<?php

/**
 * Sidebar Lateral de AdminLTE v4 - Hotel Manager
 */
$rutaActual = $_GET['url'] ?? '';
$rutaActual = trim($rutaActual, '/');

// Función auxiliar para resaltar submenús activos
$esActivo = function(string $prefijo) use ($rutaActual): bool {
    if ($prefijo === '' && ($rutaActual === '' || $rutaActual === 'home')) return true;
    if ($prefijo !== '' && str_starts_with($rutaActual, $prefijo)) return true;
    return false;
};

$configApp = (new \App\Models\Configuracion())->obtenerTodos();
$logoArchivo = $configApp['hotel_logo'] ?? '';
$nombreEmpresa = $configApp['hotel_nombre'] ?? 'Hotel Manager';
?>
<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <a href="<?= url() ?>" class="brand-link">
            <?php if (!empty($logoArchivo) && file_exists(__DIR__ . '/../../../public/uploads/hotel/' . $logoArchivo)): ?>
                <div class="brand-logo-icon bg-white p-1 d-flex align-items-center justify-content-center shadow-xs">
                    <img src="<?= base_url('uploads/hotel/' . $logoArchivo) ?>" alt="Logo">
                </div>
            <?php else: ?>
                <div class="brand-logo-icon">
                    <i class="bi bi-buildings-fill"></i>
                </div>
            <?php endif; ?>
            <div class="brand-info">
                <span class="brand-title" title="<?= htmlspecialchars($nombreEmpresa) ?>"><?= htmlspecialchars($nombreEmpresa) ?></span>
                <span class="brand-subtitle">Gestión Hotelera</span>
            </div>
        </a>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <!-- 1. Panel de control -->
                <li class="nav-item">
                    <a href="<?= url() ?>" class="nav-link <?= ($rutaActual === '' || $rutaActual === 'home') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer2 text-primary"></i>
                        <p>Panel de Control</p>
                    </a>
                </li>

                <!-- SECCIÓN: GESTIÓN HOTELERA -->
                <li class="nav-header fw-bold text-uppercase text-secondary mt-2">GESTIÓN HOTELERA</li>

                <!-- 2. Reservas -->
                <li class="nav-item <?= $esActivo('reservas') || $esActivo('disponibilidad') || $esActivo('calendario') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('reservas') || $esActivo('disponibilidad') || $esActivo('calendario') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-calendar-check text-info"></i>
                        <p>
                            Reservas
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('reservas/crear') ?>" class="nav-link <?= $rutaActual === 'reservas/crear' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-plus-circle"></i>
                                <p>Nueva reserva</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reservas') ?>" class="nav-link <?= $rutaActual === 'reservas' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-list-check"></i>
                                <p>Todas las reservas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reservas/disponibilidad') ?>" class="nav-link <?= $rutaActual === 'reservas/disponibilidad' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-calendar-range"></i>
                                <p>Disponibilidad</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reservas/calendario') ?>" class="nav-link <?= $rutaActual === 'reservas/calendario' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-calendar3"></i>
                                <p>Calendario</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 3. Huéspedes -->
                <li class="nav-item <?= $esActivo('huespedes') || $esActivo('clientes') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('huespedes') || $esActivo('clientes') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-people text-success"></i>
                        <p>
                            Huéspedes
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('huespedes/crear') ?>" class="nav-link <?= $rutaActual === 'huespedes/crear' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person-plus"></i>
                                <p>Nuevo huésped</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('huespedes') ?>" class="nav-link <?= $rutaActual === 'huespedes' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-card-list"></i>
                                <p>Directorio huéspedes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('huespedes/buscar') ?>" class="nav-link <?= $rutaActual === 'huespedes/buscar' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-search"></i>
                                <p>Buscar huésped</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('huespedes/historial') ?>" class="nav-link <?= $rutaActual === 'huespedes/historial' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-clock-history"></i>
                                <p>Historial estadías</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 4. Habitaciones -->
                <li class="nav-item <?= $esActivo('habitaciones') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('habitaciones') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-door-open text-warning"></i>
                        <p>
                            Habitaciones
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('habitaciones/crear') ?>" class="nav-link <?= $rutaActual === 'habitaciones/crear' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-plus-square"></i>
                                <p>Nueva habitación</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('habitaciones') ?>" class="nav-link <?= $rutaActual === 'habitaciones' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-grid-3x3"></i>
                                <p>Todas las habitaciones</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('habitaciones/tipos') ?>" class="nav-link <?= $rutaActual === 'habitaciones/tipos' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-tags"></i>
                                <p>Tipos de habitación</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('habitaciones/tarifas') ?>" class="nav-link <?= $rutaActual === 'habitaciones/tarifas' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-cash-stack"></i>
                                <p>Tarifas por noche</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('habitaciones/estados') ?>" class="nav-link <?= $rutaActual === 'habitaciones/estados' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-layout-text-window"></i>
                                <p>Control de estados</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('habitaciones/mantenimiento') ?>" class="nav-link <?= $rutaActual === 'habitaciones/mantenimiento' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-tools"></i>
                                <p>Mantenimiento</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 5. Recepción -->
                <li class="nav-item <?= $esActivo('recepcion') || $esActivo('checkin') || $esActivo('checkout') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('recepcion') || $esActivo('checkin') || $esActivo('checkout') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-person-badge text-danger"></i>
                        <p>
                            Recepción
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('recepcion/checkin') ?>" class="nav-link <?= $rutaActual === 'recepcion/checkin' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-box-arrow-in-right text-success"></i>
                                <p>Check-in (Ingreso)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('recepcion/checkout') ?>" class="nav-link <?= $rutaActual === 'recepcion/checkout' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-box-arrow-right text-danger"></i>
                                <p>Check-out (Salida)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('recepcion/asignacion') ?>" class="nav-link <?= $rutaActual === 'recepcion/asignacion' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Cambio de habitación</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('recepcion/estadias') ?>" class="nav-link <?= $rutaActual === 'recepcion/estadias' || $rutaActual === 'recepcion' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-journal-bookmark"></i>
                                <p>Control de estadías</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 6. Pagos y facturación -->
                <li class="nav-item <?= $esActivo('pagos') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('pagos') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-credit-card-2-front text-info"></i>
                        <p>
                            Pagos y Facturación
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('pagos/crear') ?>" class="nav-link <?= $rutaActual === 'pagos/crear' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-cash-coin"></i>
                                <p>Registrar pago</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('pagos/anticipos') ?>" class="nav-link <?= $rutaActual === 'pagos/anticipos' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-wallet2"></i>
                                <p>Anticipos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('pagos/pendientes') ?>" class="nav-link <?= $rutaActual === 'pagos/pendientes' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-exclamation-circle text-warning"></i>
                                <p>Saldos pendientes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('pagos/comprobantes') ?>" class="nav-link <?= $rutaActual === 'pagos/comprobantes' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Comprobantes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('pagos') ?>" class="nav-link <?= $rutaActual === 'pagos' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-clock-history"></i>
                                <p>Historial de pagos</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 6.1. Gestión de Caja y Turnos -->
                <li class="nav-item <?= $esActivo('caja') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('caja') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cash-stack text-success"></i>
                        <p>
                            Caja y Turnos
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('caja') ?>" class="nav-link <?= $rutaActual === 'caja' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-calculator"></i>
                                <p>Caja actual / Turno</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('caja/historial') ?>" class="nav-link <?= $rutaActual === 'caja/historial' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-archive"></i>
                                <p>Historial de cierres</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- SECCIÓN: ADMINISTRACIÓN -->
                <li class="nav-header fw-bold text-uppercase text-secondary mt-2">ADMINISTRACIÓN</li>

                <!-- 7. Servicios adicionales -->
                <li class="nav-item <?= $esActivo('servicios') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('servicios') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cup-hot text-primary"></i>
                        <p>
                            Servicios adicionales
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('servicios') ?>" class="nav-link <?= $rutaActual === 'servicios' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-list-stars"></i>
                                <p>Catálogo servicios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('servicios/consumo') ?>" class="nav-link <?= $rutaActual === 'servicios/consumo' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-cart-plus"></i>
                                <p>Registrar consumo</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('servicios/porHuesped') ?>" class="nav-link <?= $rutaActual === 'servicios/porHuesped' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person-lines-fill"></i>
                                <p>Consumo por huésped</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 8. Reportes y estadísticas -->
                <li class="nav-item <?= $esActivo('reportes') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('reportes') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-graph-up-arrow text-success"></i>
                        <p>
                            Reportes y Estadísticas
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('reportes/reservas') ?>" class="nav-link <?= $rutaActual === 'reportes/reservas' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                                <p>Reporte de reservas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reportes/ocupacion') ?>" class="nav-link <?= $rutaActual === 'reportes/ocupacion' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-pie-chart"></i>
                                <p>Reporte de ocupación</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reportes/ingresos') ?>" class="nav-link <?= $rutaActual === 'reportes/ingresos' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-cash-stack"></i>
                                <p>Reporte de ingresos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reportes/huespedes') ?>" class="nav-link <?= $rutaActual === 'reportes/huespedes' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-people-fill"></i>
                                <p>Reporte de huéspedes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('reportes/cancelaciones') ?>" class="nav-link <?= $rutaActual === 'reportes/cancelaciones' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-x-octagon text-danger"></i>
                                <p>Reporte cancelaciones</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 9. Seguridad -->
                <li class="nav-item <?= $esActivo('seguridad') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('seguridad') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-shield-lock text-warning"></i>
                        <p>
                            Seguridad y Roles
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('seguridad/usuarios') ?>" class="nav-link <?= $rutaActual === 'seguridad/usuarios' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-person-fill-gear"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('seguridad/roles') ?>" class="nav-link <?= $rutaActual === 'seguridad/roles' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-shield-check"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('seguridad/permisos') ?>" class="nav-link <?= $rutaActual === 'seguridad/permisos' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-key"></i>
                                <p>Permisos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('seguridad/auditoria') ?>" class="nav-link <?= $rutaActual === 'seguridad/auditoria' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-activity"></i>
                                <p>Auditoría del sistema</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 10. Configuración -->
                <li class="nav-item <?= $esActivo('configuracion') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $esActivo('configuracion') ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-gear text-secondary"></i>
                        <p>
                            Configuración
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('configuracion/hotel') ?>" class="nav-link <?= $rutaActual === 'configuracion/hotel' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-building"></i>
                                <p>Datos del hotel</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('configuracion/documentos') ?>" class="nav-link <?= $rutaActual === 'configuracion/documentos' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>Tipos de documento</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('configuracion/metodosPago') ?>" class="nav-link <?= $rutaActual === 'configuracion/metodosPago' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-credit-card"></i>
                                <p>Métodos de pago</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('configuracion/parametros') ?>" class="nav-link <?= $rutaActual === 'configuracion/parametros' || $rutaActual === 'configuracion' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-sliders"></i>
                                <p>Parámetros generales</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-header fw-bold text-uppercase text-secondary mt-2">SESIÓN</li>

                <li class="nav-item">
                    <a href="<?= url('login/logout') ?>" class="nav-link text-danger">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>Cerrar Sesión</p>
                    </a>
                </li>
            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
