<?php
/**
 * Vista: Dashboard Principal - Hotel Manager
 * Versión Moderna SaaS UI/UX
 */
$totalHab = max(1, (int)($conteoHab['total'] ?? 0));
$ocupadas = (int)($conteoHab['ocupada'] ?? 0);
$disponibles = (int)($conteoHab['disponible'] ?? 0);
$limpieza = (int)($conteoHab['limpieza'] ?? 0);
$mantenimiento = (int)($conteoHab['mantenimiento'] ?? 0);
$porcentajeOcupacion = round(($ocupadas / $totalHab) * 100);
?>

<!--begin::App Content Header-->
<div class="app-content-header py-3">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-speedometer2 fs-3"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold">Panel de Control</h3>
                        <p class="text-muted mb-0 small">
                            <i class="bi bi-calendar3 me-1"></i><?= date('d') ?> de <?= ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][date('n')-1] ?> de <?= date('Y') ?> &bull; Estado del Hotel en vivo
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                <a href="<?= url('reservas/crear') ?>" class="btn btn-primary shadow-sm me-1">
                    <i class="bi bi-plus-circle me-1"></i> Nueva Reserva
                </a>
                <a href="<?= url('recepcion/checkin') ?>" class="btn btn-success shadow-sm me-1">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Check-in
                </a>
                <a href="<?= url('reservas/disponibilidad') ?>" class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-calendar-range me-1"></i> Disponibilidad
                </a>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Mensajes Flash de Sesión -->
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Tarjetas de Estadísticas Principales (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Habitaciones -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-primary h-100">
                    <div class="inner">
                        <h3><?= $conteoHab['total'] ?></h3>
                        <p class="mb-1">Total de Habitaciones</p>
                        <small class="opacity-75 d-block"><?= count($habitaciones) ?> unidades operativas registradas</small>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <a href="<?= url('habitaciones') ?>" class="small-box-footer link-light link-underline-opacity-0">
                        Ver inventario <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- 2. Disponibles vs Ocupadas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-success h-100">
                    <div class="inner">
                        <h3><?= $disponibles ?> <span class="fs-6 opacity-75">/ <?= $ocupadas ?> Ocup.</span></h3>
                        <p class="mb-1">Habitaciones Disponibles</p>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="opacity-75">Ocupación: <strong><?= $porcentajeOcupacion ?>%</strong></small>
                            <small class="opacity-75"><?= $limpieza ?> limpieza &bull; <?= $mantenimiento ?> mant.</small>
                        </div>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <a href="<?= url('habitaciones/estados') ?>" class="small-box-footer link-light link-underline-opacity-0">
                        Gestionar estados <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- 3. Movimientos de Hoy -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-warning h-100">
                    <div class="inner">
                        <h3><?= count($reservasHoy) ?></h3>
                        <p class="mb-1">Movimientos de Hoy</p>
                        <small class="opacity-75 d-block">Entradas y salidas programadas hoy</small>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <a href="<?= url('reservas') ?>" class="small-box-footer link-dark link-underline-opacity-0">
                        Ver agenda del día <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- 4. Ingresos del Día y del Mes -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="small-box text-bg-info h-100">
                    <div class="inner">
                        <h3><?= dinero($ingresosHoy) ?></h3>
                        <p class="mb-1">Ingresos de Hoy</p>
                        <small class="opacity-75 d-block">Acumulado mes: <strong><?= dinero($ingresosMes) ?></strong></small>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <a href="<?= url('pagos') ?>" class="small-box-footer link-light link-underline-opacity-0">
                        Historial de caja <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mapeo Gráfico de Habitaciones en Vivo -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Mapa Visual de Habitaciones en Tiempo Real
                    </h5>
                    <small class="text-muted">Monitoreo y asignación inmediata por habitación</small>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="badge badge-soft-success"><span class="pulse-dot pulse-dot-disponible"></span>Disponible (<?= $disponibles ?>)</span>
                    <span class="badge badge-soft-danger"><span class="pulse-dot pulse-dot-ocupada"></span>Ocupada (<?= $ocupadas ?>)</span>
                    <span class="badge badge-soft-warning"><span class="pulse-dot pulse-dot-limpieza"></span>Limpieza (<?= $limpieza ?>)</span>
                    <span class="badge badge-soft-secondary"><span class="pulse-dot pulse-dot-mantenimiento"></span>Mantenimiento (<?= $mantenimiento ?>)</span>
                </div>
            </div>

            <!-- Barra de Ocupación Global -->
            <div class="px-4 pt-3 pb-1">
                <div class="d-flex justify-content-between align-items-center mb-1 small">
                    <span class="text-muted fw-semibold">Nivel de ocupación general del hotel</span>
                    <span class="fw-bold text-primary"><?= $ocupadas ?> de <?= $totalHab ?> habitaciones (<?= $porcentajeOcupacion ?>%)</span>
                </div>
                <div class="occupancy-bar">
                    <div class="occupancy-bar-fill" style="width: <?= min(100, $porcentajeOcupacion) ?>%;"></div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($habitaciones as $h): ?>
                        <?php
                            $estado = $h['estado'];
                            $stateClass = match($estado) {
                                'disponible'    => 'room-state-disponible',
                                'ocupada'       => 'room-state-ocupada',
                                'limpieza'      => 'room-state-limpieza',
                                'mantenimiento' => 'room-state-mantenimiento',
                                default         => ''
                            };
                            $icon = match($estado) {
                                'disponible'    => 'bi-check-circle-fill',
                                'ocupada'       => 'bi-person-fill-check',
                                'limpieza'      => 'bi-stars',
                                'mantenimiento' => 'bi-wrench-adjustable-circle-fill',
                                default         => 'bi-door-closed'
                            };
                        ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card room-card-modern <?= $stateClass ?> h-100 text-center p-3">
                                <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 end-0 m-2" style="font-size: 0.65rem; border-radius: 6px;">
                                    Piso <?= htmlspecialchars($h['piso']) ?>
                                </span>
                                
                                <div class="room-icon fs-2 mt-2 mb-1">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <h4 class="fw-bold mb-0">Hab. <?= htmlspecialchars($h['numero']) ?></h4>
                                <small class="text-muted text-truncate d-block fw-semibold" style="font-size: 0.78rem;"><?= htmlspecialchars($h['tipo_nombre']) ?></small>
                                <div class="mt-2 small fw-bold text-body-emphasis"><?= dinero($h['precio_diario']) ?><span class="text-muted fw-normal" style="font-size: 0.72rem;">/noc</span></div>
                                
                                <div class="mt-2 pt-2 border-top border-light-subtle">
                                    <span class="badge badge-soft-<?= match($estado) {
                                        'disponible' => 'success',
                                        'ocupada' => 'danger',
                                        'limpieza' => 'warning',
                                        default => 'secondary'
                                    } ?> text-uppercase w-100 justify-content-center" style="font-size: 0.68rem;">
                                        <span class="pulse-dot pulse-dot-<?= $estado ?>"></span>
                                        <?= htmlspecialchars($estado) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="<?= url('habitaciones/estados') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-right-circle me-1"></i> Control de Estados y Limpieza Rápida
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Tabla: Reservas de Hoy y Recientes -->
            <div class="col-12 col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-check text-primary me-2"></i>Reservas Recientes y Movimientos
                            </h5>
                            <small class="text-muted">Últimas transacciones registradas</small>
                        </div>
                        <a href="<?= url('reservas') ?>" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Huésped</th>
                                        <th>Habitaciones</th>
                                        <th>Fechas</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($ultimasReservas)): ?>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">No hay reservas registradas.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($ultimasReservas as $r): ?>
                                            <?php 
                                                $inicial = mb_strtoupper(mb_substr(trim($r['huesped_nombre'] ?? 'H'), 0, 1));
                                            ?>
                                            <tr>
                                                <td><span class="fw-bold text-primary font-monospace"><?= htmlspecialchars($r['codigo_reserva']) ?></span></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle avatar-primary me-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                            <?= $inicial ?>
                                                        </div>
                                                        <div class="text-truncate" style="max-width: 140px;">
                                                            <div class="fw-semibold text-truncate"><?= htmlspecialchars($r['huesped_nombre']) ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($r['numero_documento']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-secondary">
                                                        <i class="bi bi-door-closed me-1"></i><?= htmlspecialchars($r['habitaciones_asignadas'] ?: 'Sin asignar') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="small text-success"><i class="bi bi-box-arrow-in-right me-1"></i><?= fecha_es($r['fecha_entrada']) ?></div>
                                                    <div class="small text-muted"><i class="bi bi-box-arrow-right me-1"></i><?= fecha_es($r['fecha_salida']) ?></div>
                                                </td>
                                                <td><?= badge_estado_reserva($r['estado']) ?></td>
                                                <td>
                                                    <div class="fw-bold text-body-emphasis"><?= dinero($r['total']) ?></div>
                                                    <?php if ((float)$r['saldo'] > 0): ?>
                                                        <small class="badge badge-soft-danger py-0 px-1" style="font-size: 0.68rem;">Resta: <?= dinero($r['saldo']) ?></small>
                                                    <?php else: ?>
                                                        <small class="badge badge-soft-success py-0 px-1" style="font-size: 0.68rem;"><i class="bi bi-check2"></i> Pagado</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= url('reservas/editar/' . $r['id']) ?>" class="btn btn-sm btn-light border shadow-sm" title="Ver detalles">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel: Estadías Activas y Acciones Rápidas -->
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-badge text-success me-2"></i>Huéspedes Alojados
                            </h5>
                            <small class="text-muted">Control de check-in activo</small>
                        </div>
                        <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i><?= count($estadiasActivas) ?> Activos</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php if (empty($estadiasActivas)): ?>
                                <li class="list-group-item text-center py-4 text-muted">No hay huéspedes alojados en este momento.</li>
                            <?php else: ?>
                                <?php foreach ($estadiasActivas as $est): ?>
                                    <?php 
                                        $inicial = mb_strtoupper(mb_substr(trim($est['huesped_nombre'] ?? 'H'), 0, 1));
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle avatar-success me-3 flex-shrink-0">
                                                <?= $inicial ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">
                                                    Hab. <?= htmlspecialchars($est['habitacion_numero']) ?> &bull; <?= htmlspecialchars($est['huesped_nombre']) ?>
                                                </h6>
                                                <small class="text-muted d-block">
                                                    Check-in: <?= fecha_es($est['fecha_checkin'], true) ?> &bull; Doc: <?= htmlspecialchars($est['numero_documento']) ?>
                                                </small>
                                                <?php if ((float)$est['reserva_saldo'] > 0): ?>
                                                    <span class="badge badge-soft-danger py-0 px-1 mt-1" style="font-size: 0.7rem;">Saldo: <?= dinero($est['reserva_saldo']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-soft-success py-0 px-1 mt-1" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>Cuenta al día</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="<?= url('recepcion/checkout') ?>" class="btn btn-sm btn-outline-danger" title="Procesar Check-out">
                                                <i class="bi bi-box-arrow-right me-1"></i> Salida
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= url('recepcion/estadias') ?>" class="btn btn-link text-decoration-none btn-sm fw-semibold">
                            Ver monitoreo completo de estadías <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
