<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-door-open text-warning me-2"></i>Gestión de Habitaciones</h3>
                <p class="text-muted mb-0 small">Catálogo de habitaciones, pisos, características y estado operativo</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('habitaciones/crear') ?>" class="btn btn-warning btn-sm shadow-sm me-1 text-dark fw-semibold">
                    <i class="bi bi-plus-square me-1"></i> Registrar Habitación
                </a>
                <a href="<?= url('habitaciones/estados') ?>" class="btn btn-outline-primary btn-sm shadow-sm me-1">
                    <i class="bi bi-layout-text-window me-1"></i> Panel de Estados
                </a>
                <a href="<?= url('habitaciones/tipos') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-tags me-1"></i> Tipos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

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
        <div class="card shadow-sm mb-4">
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
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-grid-3x3 me-2"></i>Habitaciones (<?= count($habitaciones) ?>)</h5>
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
                                        <span class="fs-5 fw-bold text-primary">Hab. <?= htmlspecialchars($h['numero']) ?></span>
                                    </td>
                                    <td><span class="badge text-bg-light border">Piso <?= htmlspecialchars($h['piso']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($h['tipo_nombre']) ?></strong></td>
                                    <td><i class="bi bi-person me-1"></i><?= $h['capacidad'] ?> pers.</td>
                                    <td class="fw-bold text-success"><?= dinero($h['precio_diario']) ?></td>
                                    <td><?= badge_estado_habitacion($h['estado']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($h['descripcion'] ?: ($h['caracteristicas'] ?? '-')) ?></small></td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= url('habitaciones/editar/' . $h['id']) ?>" class="btn btn-outline-primary" title="Modificar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="<?= url('habitaciones/eliminar/' . $h['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar habitación <?= $h['numero'] ?>?');" title="Eliminar">
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
