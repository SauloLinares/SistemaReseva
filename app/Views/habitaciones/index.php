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

        <!-- Resumen por estado: cada tarjeta filtra la tabla -->
        <?php
        $estadosResumen = [
            'disponible'    => ['texto' => 'Disponibles',      'icono' => 'bi-check-circle'],
            'ocupada'       => ['texto' => 'Ocupadas',         'icono' => 'bi-person-fill'],
            'limpieza'      => ['texto' => 'En Limpieza',      'icono' => 'bi-stars'],
            'mantenimiento' => ['texto' => 'En Mantenimiento', 'icono' => 'bi-tools'],
        ];
        ?>
        <div class="row g-3 mb-3">
            <?php foreach ($estadosResumen as $clave => $info): ?>
                <?php $activo = ($filtroEstado === $clave); ?>
                <div class="col-6 col-md-3">
                    <a href="<?= e(url('habitaciones') . ($activo ? '' : '&estado=' . $clave)) ?>"
                       class="estado-stat estado-stat-<?= $clave ?><?= $activo ? ' active' : '' ?>"
                       aria-current="<?= $activo ? 'true' : 'false' ?>"
                       title="<?= $activo ? 'Quitar filtro' : 'Mostrar solo ' . e(mb_strtolower($info['texto'])) ?>">
                        <span class="estado-stat-numero"><?= (int) ($conteo[$clave] ?? 0) ?></span>
                        <span class="estado-stat-texto"><i class="bi <?= $info['icono'] ?> me-1"></i><?= e($info['texto']) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('habitaciones') ?>" class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <select name="estado" class="form-select form-select-sm" aria-label="Filtrar por estado">
                            <option value="">-- Todos los estados --</option>
                            <option value="disponible" <?= ($filtroEstado === 'disponible') ? 'selected' : '' ?>>Disponibles</option>
                            <option value="ocupada" <?= ($filtroEstado === 'ocupada') ? 'selected' : '' ?>>Ocupadas</option>
                            <option value="limpieza" <?= ($filtroEstado === 'limpieza') ? 'selected' : '' ?>>En Limpieza</option>
                            <option value="mantenimiento" <?= ($filtroEstado === 'mantenimiento') ? 'selected' : '' ?>>En Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="tipo_id" class="form-select form-select-sm" aria-label="Filtrar por tipo">
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
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th class="text-end">Precio / noche</th>
                                <th>Estado</th>
                                <th>Descripción</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($habitaciones as $h): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= e(foto_habitacion($h['foto'] ?? null, true)) ?>" alt="Hab. <?= e($h['numero']) ?>" class="room-thumb" width="48" height="36" loading="lazy" decoding="async">
                                            <span class="fw-semibold text-nowrap">Hab. <?= e($h['numero']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">Piso <?= e($h['piso']) ?></td>
                                    <td class="fw-semibold text-nowrap"><?= e($h['tipo_nombre']) ?></td>
                                    <td class="text-nowrap"><i class="bi bi-people text-body-secondary me-1"></i><?= (int) $h['capacidad'] ?></td>
                                    <td class="text-end text-nowrap fw-semibold"><?= dinero($h['precio_diario']) ?></td>
                                    <td><?= badge_estado_habitacion($h['estado']) ?></td>
                                    <td>
                                        <?php $descripcion = $h['descripcion'] ?: ($h['caracteristicas'] ?? 'Sin descripción adicional'); ?>
                                        <span class="text-body-secondary text-clamp-2" title="<?= e($descripcion) ?>"><?= e($descripcion) ?></span>
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
