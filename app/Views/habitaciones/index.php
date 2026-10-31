<?php partial('page_header', [
    'titulo'    => 'Gestión de Habitaciones',
    'icono'     => 'bi-door-open',
    'subtitulo' => 'Catálogo de habitaciones, pisos, características y estado operativo',
    'acciones'  => [
        ['texto' => 'Registrar Habitación', 'url' => 'habitaciones/crear', 'icono' => 'bi-plus-square'],
        ['texto' => 'Panel de Estados', 'url' => 'habitaciones/estados', 'icono' => 'bi-layout-text-window', 'variante' => 'outline-primary'],
        ['texto' => 'Tipos', 'url' => 'habitaciones/tipos', 'icono' => 'bi-tags', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Tarjetas de Conteo Rápido -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-success-subtle border-success text-success-emphasis p-3 text-center shadow-xs">
                    <h3 class="fw-bold mb-0"><?= $conteo['disponible'] ?></h3>
                    <small class="fw-semibold"><i class="bi bi-check-circle me-1"></i>Disponibles</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-danger-subtle border-danger text-danger-emphasis p-3 text-center shadow-xs">
                    <h3 class="fw-bold mb-0"><?= $conteo['ocupada'] ?></h3>
                    <small class="fw-semibold"><i class="bi bi-person-fill me-1"></i>Ocupadas</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-warning-subtle border-warning text-warning-emphasis p-3 text-center shadow-xs">
                    <h3 class="fw-bold mb-0"><?= $conteo['limpieza'] ?></h3>
                    <small class="fw-semibold"><i class="bi bi-stars me-1"></i>En Limpieza</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-secondary-subtle border-secondary text-secondary-emphasis p-3 text-center shadow-xs">
                    <h3 class="fw-bold mb-0"><?= $conteo['mantenimiento'] ?></h3>
                    <small class="fw-semibold"><i class="bi bi-tools me-1"></i>En Mantenimiento</small>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('habitaciones') ?>" class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">-- Todos los estados --</option>
                            <option value="disponible" <?= ($filtroEstado === 'disponible') ? 'selected' : '' ?>>Disponibles</option>
                            <option value="ocupada" <?= ($filtroEstado === 'ocupada') ? 'selected' : '' ?>>Ocupadas</option>
                            <option value="limpieza" <?= ($filtroEstado === 'limpieza') ? 'selected' : '' ?>>En Limpieza</option>
                            <option value="mantenimiento" <?= ($filtroEstado === 'mantenimiento') ? 'selected' : '' ?>>En Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="tipo_id" class="form-select form-select-sm">
                            <option value="">-- Todos los tipos --</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($filtroTipo == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                        <a href="<?= url('habitaciones') ?>" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Habitaciones -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-grid-3x3 me-2"></i>Habitaciones (<?= count($habitaciones) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Piso</th>
                                <th>Tipo de Habitación</th>
                                <th>Capacidad</th>
                                <th>Precio por Noche</th>
                                <th>Estado</th>
                                <th>Descripción / Amenidades</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($habitaciones as $h): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php 
                                                $fotoH = $h['foto'] ?? 'simple.jpg';
                                                $rutaImg = file_exists(__DIR__ . '/../../../public/uploads/habitaciones/' . $fotoH)
                                                    ? base_url('uploads/habitaciones/' . $fotoH)
                                                    : base_url('uploads/habitaciones/simple.jpg');
                                            ?>
                                            <img src="<?= $rutaImg ?>" alt="Hab. <?= htmlspecialchars($h['numero']) ?>" class="rounded-2 shadow-xs border flex-shrink-0" style="width: 52px; height: 38px; object-fit: cover;">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 fw-bold px-2 py-1">
                                                Hab. <?= htmlspecialchars($h['numero']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-secondary border fw-semibold">Piso <?= htmlspecialchars($h['piso']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-body-emphasis"><?= htmlspecialchars($h['tipo_nombre']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="bi bi-people-fill me-1"></i><?= $h['capacidad'] ?> pers.
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success fs-6"><?= dinero($h['precio_diario']) ?></td>
                                    <td><?= badge_estado_habitacion($h['estado']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi bi-info-circle text-primary opacity-75 mt-0.5 flex-shrink-0"></i>
                                            <span class="text-body-secondary small lh-sm">
                                                <?= htmlspecialchars($h['descripcion'] ?: ($h['caracteristicas'] ?? 'Sin descripción adicional')) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="table-actions">
                                            <a href="<?= url('habitaciones/editar/' . $h['id']) ?>" class="btn btn-icon btn-outline-primary" title="Modificar Habitación">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="<?= url('habitaciones/eliminar/' . $h['id']) ?>" class="btn btn-icon btn-outline-danger" data-confirm="¿Eliminar habitación <?= htmlspecialchars($h['numero']) ?>?" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
