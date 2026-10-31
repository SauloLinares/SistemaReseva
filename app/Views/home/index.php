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

$estadiasPorHab = [];
if (!empty($estadiasActivas)) {
    foreach ($estadiasActivas as $ea) {
        $estadiasPorHab[$ea['habitacion_id']] = $ea;
    }
}
?>

<?php partial('page_header', [
    'titulo'    => 'Panel de Control',
    'icono'     => 'bi-speedometer2',
    'subtitulo' => date('d') . ' de ' . ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][date('n') - 1] . ' de ' . date('Y') . ' • Estado del Hotel en vivo',
    // Nueva Reserva y Check-in ya están en la barra superior
    'acciones'  => [
        ['texto' => 'Disponibilidad', 'url' => 'reservas/disponibilidad', 'icono' => 'bi-calendar-range', 'variante' => 'outline-secondary'],
        ['texto' => 'Calendario', 'url' => 'reservas/calendario', 'icono' => 'bi-calendar3', 'variante' => 'outline-secondary'],
    ],
]) ?>

<?php
// Accesos rápidos de recepción, con lo que hay pendiente hoy en cada uno
$hoy = date('Y-m-d');
$llegadasPendientes = count(array_filter($reservasHoy ?? [], fn($r) => $r['fecha_entrada'] === $hoy && in_array($r['estado'], ['pendiente', 'confirmada'], true)));
$salidasPendientes = count(array_filter($estadiasActivas ?? [], fn($e) => !empty($e['fecha_salida']) && $e['fecha_salida'] <= $hoy));
$cuentasPorCobrar = count(array_filter($estadiasActivas ?? [], fn($e) => (float)$e['reserva_saldo'] > 0));
$accesos = [
    ['Nueva reserva', 'reservas/crear', 'bi-plus-circle', 'F2', 0, ''],
    ['Check-in', 'recepcion/checkin', 'bi-box-arrow-in-right', 'F4', $llegadasPendientes, 'llegadas pendientes hoy'],
    ['Check-out', 'recepcion/checkout', 'bi-box-arrow-right', '', $salidasPendientes, 'salidas pendientes hoy'],
    ['Cobrar', 'pagos/crear', 'bi-cash-coin', '', $cuentasPorCobrar, 'huéspedes alojados con saldo'],
    ['Consumo', 'servicios/consumo', 'bi-cup-hot', '', 0, ''],
    ['Planificación', 'reservas/planificacion', 'bi-grid-3x3-gap', '', 0, ''],
];
?>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Accesos rápidos de recepción -->
        <nav class="accesos-rapidos mb-4" aria-label="Accesos rápidos">
            <?php foreach ($accesos as [$texto, $ruta, $icono, $tecla, $pendientes, $detalle]): ?>
                <a href="<?= url($ruta) ?>" class="acceso-rapido">
                    <i class="bi <?= $icono ?>" aria-hidden="true"></i>
                    <span><?= e($texto) ?></span>
                    <?php if ($pendientes > 0): ?>
                        <span class="badge rounded-pill text-bg-warning" title="<?= (int)$pendientes . ' ' . e($detalle) ?>">
                            <?= (int)$pendientes ?><span class="visually-hidden"> <?= e($detalle) ?></span>
                        </span>
                    <?php endif; ?>
                    <?php if ($tecla): ?><kbd class="kbd-atajo" aria-hidden="true"><?= $tecla ?></kbd><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Tarjetas de Estadísticas Principales (KPIs) -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Habitaciones -->
            <div class="col-6 col-xl-3">
                <div class="small-box text-bg-primary h-100">
                    <div class="inner">
                        <h3><?= $conteoHab['total'] ?></h3>
                        <p class="mb-1">Total de Habitaciones</p>
                        <small class="d-block"><?= count($habitaciones) ?> unidades operativas registradas</small>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <a href="<?= url('habitaciones') ?>" class="small-box-footer">
                        Ver inventario <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- 2. Disponibles vs Ocupadas -->
            <div class="col-6 col-xl-3">
                <div class="small-box text-bg-success h-100">
                    <div class="inner">
                        <h3><?= $disponibles ?> <span class="fs-6 fw-semibold text-body-secondary">/ <?= $ocupadas ?> Ocup.</span></h3>
                        <p class="mb-1">Habitaciones Disponibles</p>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small>Ocupación: <strong><?= $porcentajeOcupacion ?>%</strong></small>
                            <small><?= $limpieza ?> limpieza &bull; <?= $mantenimiento ?> mant.</small>
                        </div>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <a href="<?= url('habitaciones/estados') ?>" class="small-box-footer">
                        Gestionar estados <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- 3. Movimientos de Hoy -->
            <div class="col-6 col-xl-3">
                <div class="small-box text-bg-warning h-100">
                    <div class="inner">
                        <h3><?= count($reservasHoy) ?></h3>
                        <p class="mb-1">Movimientos de Hoy</p>
                        <small class="d-block">Entradas y salidas programadas hoy</small>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <a href="<?= url('reservas') ?>" class="small-box-footer">
                        Ver agenda del día <i class="bi bi-arrow-right-circle ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- 4. Ingresos del Día y del Mes -->
            <div class="col-6 col-xl-3">
                <div class="small-box text-bg-info h-100">
                    <div class="inner">
                        <h3><?= dinero($ingresosHoy) ?></h3>
                        <p class="mb-1">Ingresos de Hoy</p>
                        <small class="d-block">Acumulado mes: <strong><?= dinero($ingresosMes) ?></strong></small>
                    </div>
                    <div class="small-box-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <a href="<?= url('pagos') ?>" class="small-box-footer">
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
                <div class="d-flex gap-1 align-items-center flex-wrap" id="filtroHabitacionesDashboard">
                    <button type="button" class="btn btn-xs filter-chip btn-filtro-dash active" data-filtro="todos" aria-pressed="true">
                        Todas <span class="filter-chip-count"><?= count($habitaciones) ?></span>
                    </button>
                    <button type="button" class="btn btn-xs filter-chip btn-filtro-dash" data-filtro="disponible" aria-pressed="false">
                        <span class="pulse-dot pulse-dot-disponible"></span>Disponibles <span class="filter-chip-count"><?= $disponibles ?></span>
                    </button>
                    <button type="button" class="btn btn-xs filter-chip btn-filtro-dash" data-filtro="ocupada" aria-pressed="false">
                        <span class="pulse-dot pulse-dot-ocupada"></span>Ocupadas <span class="filter-chip-count"><?= $ocupadas ?></span>
                    </button>
                    <button type="button" class="btn btn-xs filter-chip btn-filtro-dash" data-filtro="limpieza" aria-pressed="false">
                        <span class="pulse-dot pulse-dot-limpieza"></span>Limpieza <span class="filter-chip-count"><?= $limpieza ?></span>
                    </button>
                    <button type="button" class="btn btn-xs filter-chip btn-filtro-dash" data-filtro="mantenimiento" aria-pressed="false">
                        <span class="pulse-dot pulse-dot-mantenimiento"></span>Mantenimiento <span class="filter-chip-count"><?= $mantenimiento ?></span>
                    </button>
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
                <div class="row g-3" id="gridHabitacionesDashboard">
                    <?php foreach ($habitaciones as $h): ?>
                        <?php
                            $estado = $h['estado'];
                            $est = $estadiasPorHab[$h['id']] ?? null;
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
                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 item-hab-card" data-estado="<?= $estado ?>">
                            <div class="card room-card-modern room-card-interactive <?= $stateClass ?> h-100 text-center p-3"
                                 data-id="<?= $h['id'] ?>"
                                 data-numero="<?= htmlspecialchars($h['numero']) ?>"
                                 data-tipo="<?= htmlspecialchars($h['tipo_nombre']) ?>"
                                 data-piso="<?= $h['piso'] ?>"
                                 data-precio="<?= dinero($h['precio_diario']) ?>"
                                 data-estado="<?= $estado ?>"
                                 data-foto="<?= e(foto_habitacion($h['foto'] ?? null)) ?>"
                                 data-caracteristicas="<?= htmlspecialchars($h['caracteristicas'] ?? 'Wi-Fi, TV Cable, Baño Privado') ?>"
                                 data-huesped="<?= htmlspecialchars($est['huesped_nombre'] ?? '') ?>"
                                 data-doc="<?= htmlspecialchars($est['numero_documento'] ?? '') ?>"
                                 data-tel="<?= htmlspecialchars($est['telefono'] ?? '') ?>"
                                 data-codigo="<?= htmlspecialchars($est['codigo_reserva'] ?? '') ?>"
                                 data-entrada="<?= !empty($est['fecha_entrada']) ? fecha_es($est['fecha_entrada']) : '' ?>"
                                 data-salida="<?= !empty($est['fecha_salida']) ? fecha_es($est['fecha_salida']) : '' ?>"
                                 data-saldo="<?= dinero($est['reserva_saldo'] ?? 0) ?>"
                                 data-saldo-raw="<?= (float)($est['reserva_saldo'] ?? 0) ?>"
                                 data-estancia-id="<?= $est['id'] ?? '' ?>"
                                 data-reserva-id="<?= $est['reserva_id'] ?? '' ?>"
                                 title="Clic para inspeccionar habitación">
                                <span class="room-floor-badge position-absolute top-0 end-0 m-2">
                                    Piso <?= htmlspecialchars($h['piso']) ?>
                                </span>
                                
                                <div class="room-icon fs-2 mt-2 mb-1">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div class="room-card-numero">Hab. <?= e($h['numero']) ?></div>
                                <small class="text-sm text-body-secondary text-truncate d-block"><?= e($h['tipo_nombre']) ?></small>
                                <div class="mt-2 text-sm text-nowrap"><strong class="text-body-emphasis"><?= dinero($h['precio_diario']) ?></strong> <span class="text-body-secondary">/ noche</span></div>

                                <div class="mt-2 pt-2 border-top border-light-subtle">
                                    <span class="badge badge-soft-<?= match($estado) {
                                        'disponible' => 'success',
                                        'ocupada' => 'danger',
                                        'limpieza' => 'warning',
                                        default => 'secondary'
                                    } ?> text-uppercase w-100 justify-content-center">
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
                                                        <div class="avatar-circle avatar-primary me-2 flex-shrink-0 avatar-xs">
                                                            <?= $inicial ?>
                                                        </div>
                                                        <div class="text-truncate mw-140">
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
                                                        <small class="text-2xs badge badge-soft-danger py-0 px-1">Resta: <?= dinero($r['saldo']) ?></small>
                                                    <?php else: ?>
                                                        <small class="text-2xs badge badge-soft-success py-0 px-1"><i class="bi bi-check2"></i> Pagado</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= url('reservas/editar/' . $r['id']) ?>" class="btn btn-icon btn-outline-secondary border" title="Ver detalles">
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
                                                    <span class="text-xs badge badge-soft-danger py-0 px-1 mt-1">Saldo: <?= dinero($est['reserva_saldo']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-xs badge badge-soft-success py-0 px-1 mt-1"><i class="bi bi-check-circle me-1"></i>Cuenta al día</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="<?= url('recepcion/checkout?id=' . $est['id']) ?>" class="btn btn-sm btn-outline-danger" title="Check-out de Hab. <?= e($est['habitacion_numero']) ?>">
                                                <i class="bi bi-box-arrow-right me-1"></i> Salida
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= url('recepcion/estadias') ?>" class="btn btn-link text-decoration-none btn-sm">
                            Ver monitoreo completo de estadías <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->

<!-- Modal de Inspección Rápida y Centro de Mando de Habitación -->
<div class="modal fade" id="modalDetalleHabitacion" tabindex="-1" aria-labelledby="modalDetalleHabitacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle avatar-primary flex-shrink-0 avatar-md" id="mHabIconWrapper">
                        <i class="bi bi-door-closed" id="mHabIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="mHabNumeroTitulo">Habitación ---</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge badge-soft-secondary" id="mHabTipo">Tipo</span>
                            <span class="badge" id="mHabEstadoBadge">Estado</span>
                            <span class="text-muted small" id="mHabPiso">Piso -</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3">
                
                <!-- Fotografía Banner de la Habitación -->
                <div class="mb-3 position-relative rounded-3 overflow-hidden border shadow-xs modal-room-photo">
                    <img id="mHabFotoBanner" src="<?= base_url('uploads/habitaciones/simple.jpg') ?>" alt="Foto de la Habitación" class="w-100 h-100 object-fit-cover">
                </div>

                <!-- Sección para Habitación Disponible -->
                <div id="mSeccionDisponible" class="d-none">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Tarifa por Noche</small>
                                <h4 class="fw-bold text-success mb-0" id="mHabPrecio">$0.00</h4>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Lista para Asignar</span>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top small text-muted">
                            <i class="bi bi-info-circle me-1"></i> <span id="mHabCaracteristicas">Wi-Fi, TV por cable, Baño Privado</span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="#" id="mBtnCrearReserva" class="btn btn-primary">
                            <i class="bi bi-calendar-plus me-1"></i> Asignar / Registrar Reserva Directa
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-warning w-100" id="mBtnEnviarLimpieza">
                                <i class="bi bi-stars me-1"></i> Pasar a Limpieza
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100" id="mBtnEnviarMantenimiento">
                                <i class="bi bi-wrench-adjustable me-1"></i> Mantenimiento
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sección para Habitación Ocupada -->
                <div id="mSeccionOcupada" class="d-none">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-circle avatar-danger flex-shrink-0 avatar-md">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Huésped Titular</small>
                                <h6 class="fw-bold mb-0" id="mHuespedNombre">---</h6>
                                <small class="text-muted" id="mHuespedDoc">Doc: ---</small>
                            </div>
                        </div>
                        <div class="row g-2 pt-2 border-top small">
                            <div class="col-6">
                                <span class="text-muted d-block">Check-in:</span>
                                <strong id="mFechaEntrada">--/--/----</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Check-out previsto:</span>
                                <strong id="mFechaSalida">--/--/----</strong>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="text-muted d-block">Código Reserva:</span>
                                <span class="badge badge-soft-secondary" id="mCodigoReserva">---</span>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="text-muted d-block">Saldo Pendiente:</span>
                                <strong id="mSaldoPendiente" class="text-danger">$0.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="#" id="mBtnProcesarCheckout" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Procesar Check-out & Cobro
                        </a>
                        <div class="d-flex gap-2">
                            <!-- Los href se completan con la reserva de la habitación al abrir el modal -->
                            <a href="<?= url('servicios/consumo') ?>" id="mBtnCargarConsumo" class="btn btn-outline-primary w-100">
                                <i class="bi bi-cart-plus me-1"></i> Cargar Consumo
                            </a>
                            <a href="<?= url('recepcion/estadias') ?>" id="mBtnFichaEstadia" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-eye me-1"></i> Ficha de Estadía
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sección para Habitación en Limpieza -->
                <div id="mSeccionLimpieza" class="d-none">
                    <div class="p-3 bg-warning-subtle rounded-3 mb-3 border border-warning-subtle text-center">
                        <div class="fs-1 text-warning mb-2"><i class="bi bi-stars"></i></div>
                        <h6 class="fw-bold mb-1">Habitación en Limpieza</h6>
                        <p class="small text-muted mb-0">Esta habitación se encuentra desinfectándose y ordenándose para el próximo huésped.</p>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" id="mBtnLiberarLimpieza">
                            <i class="bi bi-check2-circle me-1"></i> Finalizar Limpieza (Marcar Disponible)
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="mBtnMantenimientoDesdeLimpieza">
                            <i class="bi bi-wrench-adjustable me-1"></i> Reportar Falla Técnica (Mantenimiento)
                        </button>
                    </div>
                </div>

                <!-- Sección para Habitación en Mantenimiento -->
                <div id="mSeccionMantenimiento" class="d-none">
                    <div class="p-3 bg-secondary-subtle rounded-3 mb-3 border border-secondary-subtle text-center">
                        <div class="fs-1 text-secondary mb-2"><i class="bi bi-wrench-adjustable-circle-fill"></i></div>
                        <h6 class="fw-bold mb-1">Habitación Fuera de Servicio</h6>
                        <p class="small text-muted mb-0">Se encuentra en mantenimiento técnico o reparación de averías.</p>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" id="mBtnLiberarMantenimiento">
                            <i class="bi bi-check2-circle me-1"></i> Concluir Reparación (Marcar Disponible)
                        </button>
                        <button type="button" class="btn btn-outline-warning" id="mBtnLimpiezaDesdeMantenimiento">
                            <i class="bi bi-stars me-1"></i> Pasar a Limpieza
                        </button>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= script_js('vistas/home-index.js') ?>
