<?php partial('page_header', [
    'titulo'    => 'Consultar Disponibilidad',
    'icono'     => 'bi-calendar-range',
    'subtitulo' => 'Verificación de disponibilidad de habitaciones sin superposición de fechas',
    'acciones'  => [
        ['texto' => 'Nueva Reserva', 'url' => 'reservas/crear', 'icono' => 'bi-plus-circle'],
        ['texto' => 'Ver Calendario', 'url' => 'reservas/calendario', 'icono' => 'bi-calendar3', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Formulario de Consulta -->
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0"><i class="bi bi-search me-2"></i>Parámetros de Búsqueda</h5>
                <div class="d-flex flex-wrap gap-1 align-items-center">
                    <span class="text-muted small me-1"><i class="bi bi-lightning-charge text-warning"></i> Atajos:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill small" data-rango="1">
                        Hoy a Mañana (1 n.)
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill small" data-rango="finde">
                        Fin de Semana
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill small" data-rango="7">
                        Semana (7 d.)
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= url('reservas/disponibilidad') ?>" class="row g-3 align-items-end" id="formDisponibilidad">
                    <div class="col-12 col-md-3">
                        <label for="entrada" class="form-label">Fecha de Entrada</label>
                        <input id="entrada" type="date" name="entrada" class="form-control" value="<?= htmlspecialchars($entrada) ?>" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="salida" class="form-label">Fecha de Salida</label>
                        <input id="salida" type="date" name="salida" class="form-control" value="<?= htmlspecialchars($salida) ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="tipo_id" class="form-label">Tipo de Habitación (Opcional)</label>
                        <select id="tipo_id" name="tipo_id" class="form-select">
                            <option value="">-- Todos los tipos --</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($tipoId == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?> (Capacidad: <?= $t['capacidad'] ?> pers.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Consultar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Banner explicativo de disponibilidad inteligente -->
        <div class="alert alert-info d-flex align-items-center shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
            <div class="small">
                <strong>Cálculo inteligente de disponibilidad en tiempo real:</strong>
                Una habitación se considera disponible si no presenta reservas activas superpuestas en el rango consultado y se encuentra operativa sin órdenes de mantenimiento vigentes.
            </div>
        </div>

        <!-- Resultados de la Disponibilidad -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-door-open-fill text-success me-2"></i>Habitaciones Disponibles: <?= count($habitacionesDisponibles) ?> de <?= $totalHabitaciones ?>
                </h5>
                <span class="badge badge-soft-success px-3 py-2 fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i><?= fecha_es($entrada) ?> al <?= fecha_es($salida) ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($habitacionesDisponibles)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-danger display-4 mb-3 d-block opacity-75"></i>
                        <h5 class="fw-bold">No hay habitaciones disponibles para las fechas seleccionadas</h5>
                        <p class="text-muted small">Intenta buscar en otro rango de fechas o seleccionar otra categoría de habitación.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-rango="1">
                            <i class="bi bi-arrow-repeat me-1"></i> Consultar Hoy a Mañana
                        </button>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($habitacionesDisponibles as $h): ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
                                <?php
                                    $capacidad = (int) $h['capacidad'];
                                    $caracteristicas = $h['caracteristicas'] ?: 'Wi-Fi, TV, Baño Privado';
                                ?>
                                <!-- Mismo patrón de tarjeta que el panel y Estado de Habitaciones -->
                                <div class="card room-card-modern room-state-disponible h-100 p-3 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="room-card-numero text-nowrap">Hab. <?= e($h['numero']) ?></span>
                                        <span class="room-floor-badge">Piso <?= e($h['piso']) ?></span>
                                    </div>
                                    <div class="fw-semibold"><?= e($h['tipo_nombre']) ?></div>
                                    <div class="text-sm text-body-secondary mb-2">
                                        <i class="bi bi-people me-1"></i>Hasta <?= $capacidad ?> <?= $capacidad === 1 ? 'persona' : 'personas' ?>
                                    </div>
                                    <p class="text-sm text-body-secondary text-clamp-2 mw-100 mb-3" title="<?= e($caracteristicas) ?>"><?= e($caracteristicas) ?></p>
                                    <div class="mt-auto">
                                        <div class="mb-2 text-nowrap">
                                            <strong class="fs-5 text-body-emphasis"><?= dinero($h['precio_diario']) ?></strong>
                                            <span class="text-body-secondary text-sm">/ noche</span>
                                        </div>
                                        <a href="<?= e(url('reservas/crear?hab_id=' . $h['id'] . '&entrada=' . urlencode($entrada) . '&salida=' . urlencode($salida))) ?>" class="btn btn-primary btn-sm w-100">
                                            <i class="bi bi-calendar-plus me-1"></i>Reservar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/reservas-disponibilidad.js') ?>
