<?php partial('page_header', [
    'titulo'    => 'Estado de Habitaciones',
    'icono'     => 'bi-layout-text-window',
    'subtitulo' => 'Control operativo de housekeeping, recepción y disponibilidad',
    'acciones'  => [
        ['texto' => 'Mantenimiento', 'url' => 'habitaciones/mantenimiento', 'icono' => 'bi-tools', 'variante' => 'outline-secondary'],
        ['texto' => 'Lista', 'url' => 'habitaciones', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Resumen de Estados (Píldoras Métricas) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center border-0" style="background: rgba(16, 185, 129, 0.08); border-left: 4px solid #10b981 !important;">
                    <span class="fs-3 fw-bold text-success"><?= $conteo['disponible'] ?></span>
                    <span class="small fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Disponibles</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center border-0" style="background: rgba(239, 68, 68, 0.08); border-left: 4px solid #ef4444 !important;">
                    <span class="fs-3 fw-bold text-danger"><?= $conteo['ocupada'] ?></span>
                    <span class="small fw-semibold text-danger"><i class="bi bi-person-fill me-1"></i>Ocupadas</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center border-0" style="background: rgba(245, 158, 11, 0.08); border-left: 4px solid #f59e0b !important;">
                    <span class="fs-3 fw-bold text-warning"><?= $conteo['limpieza'] ?></span>
                    <span class="small fw-semibold text-warning"><i class="bi bi-stars me-1"></i>En Limpieza</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card p-3 text-center border-0" style="background: rgba(100, 116, 139, 0.08); border-left: 4px solid #64748b !important;">
                    <span class="fs-3 fw-bold text-secondary"><?= $conteo['mantenimiento'] ?></span>
                    <span class="small fw-semibold text-secondary"><i class="bi bi-tools me-1"></i>En Mantenimiento</span>
                </div>
            </div>
        </div>

        <!-- Grilla Interactiva de Habitaciones con Cambio Rápido -->
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
                    $badgeSoft = match($estado) {
                        'disponible'    => 'badge-soft-success',
                        'ocupada'       => 'badge-soft-danger',
                        'limpieza'      => 'badge-soft-warning',
                        'mantenimiento' => 'badge-soft-secondary',
                        default         => 'badge-soft-light'
                    };
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="card room-card-modern <?= $stateClass ?> h-100 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold fs-5 text-body-emphasis">Hab. <?= htmlspecialchars($h['numero']) ?></span>
                            <span class="badge bg-dark bg-opacity-75 text-white" style="font-size: 0.68rem; border-radius: 6px;">
                                Piso <?= htmlspecialchars($h['piso']) ?>
                            </span>
                        </div>

                        <div class="mb-2">
                            <h6 class="fw-bold mb-0 text-truncate"><?= htmlspecialchars($h['tipo_nombre']) ?></h6>
                            <small class="text-muted"><?= dinero($h['precio_diario']) ?> / noche</small>
                        </div>

                        <div class="mb-3">
                            <span class="badge <?= $badgeSoft ?> text-uppercase">
                                <span class="pulse-dot pulse-dot-<?= $estado ?>"></span>
                                <?= htmlspecialchars($estado) ?>
                            </span>
                        </div>

                        <form method="POST" action="<?= url('habitaciones/cambiarEstado') ?>" class="mt-auto pt-2 border-top border-light-subtle">
                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                            <label class="form-label mb-1">Cambiar a:</label>
                            <div class="input-group input-group-sm">
                                <select name="estado" class="form-select form-select-sm">
                                    <option value="disponible" <?= ($estado === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                                    <option value="ocupada" <?= ($estado === 'ocupada') ? 'selected' : '' ?>>Ocupada</option>
                                    <option value="limpieza" <?= ($estado === 'limpieza') ? 'selected' : '' ?>>Limpieza</option>
                                    <option value="mantenimiento" <?= ($estado === 'mantenimiento') ? 'selected' : '' ?>>Mantenimiento</option>
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit" title="Actualizar">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
