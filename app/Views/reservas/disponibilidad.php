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
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill small" onclick="aplicarRangoFecha(1)">
                        Hoy a Mañana (1 n.)
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill small" onclick="aplicarRangoFecha('finde')">
                        Fin de Semana
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill small" onclick="aplicarRangoFecha(7)">
                        Semana (7 d.)
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= url('reservas/disponibilidad') ?>" class="row g-3 align-items-end" id="formDisponibilidad">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Fecha de Entrada</label>
                        <input type="date" name="entrada" class="form-control" value="<?= htmlspecialchars($entrada) ?>" required>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Fecha de Salida</label>
                        <input type="date" name="salida" class="form-control" value="<?= htmlspecialchars($salida) ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Tipo de Habitación (Opcional)</label>
                        <select name="tipo_id" class="form-select">
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
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="aplicarRangoFecha(1)">
                            <i class="bi bi-arrow-repeat me-1"></i> Consultar Hoy a Mañana
                        </button>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($habitacionesDisponibles as $h): ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="card h-100 border shadow-xs room-card-modern is-available">
                                    <div class="card-header d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="pulse-dot pulse-dot-disponible"></span>
                                            <span class="fw-bold fs-5 text-body-emphasis">Hab. <?= htmlspecialchars($h['numero']) ?></span>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.72rem; border-radius: 6px;">
                                            Piso <?= htmlspecialchars($h['piso']) ?>
                                        </span>
                                    </div>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title text-body-emphasis mb-1"><?= htmlspecialchars($h['tipo_nombre']) ?></h6>
                                            <div class="text-muted small mb-2">
                                                <i class="bi bi-people me-1 text-primary"></i>Hasta <?= $h['capacidad'] ?> personas
                                            </div>
                                            <div class="small text-muted mb-3">
                                                <i class="bi bi-check2-circle text-success me-1"></i><?= htmlspecialchars($h['caracteristicas'] ?: 'Wi-Fi, TV, Baño Privado') ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-4 fw-bold text-success mb-2">
                                                <?= dinero($h['precio_diario']) ?> <small class="fs-6 text-muted fw-normal">/ noche</small>
                                            </div>
                                            <a href="<?= url('reservas/crear?hab_id=' . $h['id'] . '&entrada=' . $entrada . '&salida=' . $salida) ?>" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-calendar-plus me-1"></i> Reservar Habitación
                                            </a>
                                        </div>
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

<script>
function aplicarRangoFecha(tipo) {
    const hoy = new Date();
    const formatear = d => d.toISOString().split('T')[0];
    const entradaInput = document.querySelector('input[name="entrada"]');
    const salidaInput = document.querySelector('input[name="salida"]');
    
    if (tipo === 'finde') {
        const dia = hoy.getDay();
        const diasHastaViernes = (5 - dia + 7) % 7 || 7;
        const viernes = new Date(hoy);
        viernes.setDate(hoy.getDate() + (dia === 5 ? 0 : diasHastaViernes));
        const domingo = new Date(viernes);
        domingo.setDate(viernes.getDate() + 2);
        entradaInput.value = formatear(viernes);
        salidaInput.value = formatear(domingo);
    } else {
        const dias = parseInt(tipo, 10) || 1;
        const salida = new Date(hoy);
        salida.setDate(hoy.getDate() + dias);
        entradaInput.value = formatear(hoy);
        salidaInput.value = formatear(salida);
    }
    
    document.getElementById('formDisponibilidad').submit();
}
</script>
