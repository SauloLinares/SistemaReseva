<?php partial('page_header', [
    'titulo'    => 'Listado de Reservas',
    'icono'     => 'bi-calendar-check',
    'subtitulo' => 'Administración, filtros y control de reservas hoteleras',
    'acciones'  => [
        ['texto' => 'Registrar Reserva', 'url' => 'reservas/crear', 'icono' => 'bi-plus-circle'],
        ['texto' => 'Disponibilidad', 'url' => 'reservas/disponibilidad', 'icono' => 'bi-calendar-range', 'variante' => 'outline-info'],
        ['texto' => 'Calendario', 'url' => 'reservas/calendario', 'icono' => 'bi-calendar3', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Tarjeta de Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?= url('reservas') ?>" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="filtroEstado" class="form-label">Estado de Reserva</label>
                        <select name="estado" id="filtroEstado" class="form-select form-select-sm">
                            <option value="">-- Todos los estados --</option>
                            <option value="confirmada" <?= ($filtroEstado === 'confirmada') ? 'selected' : '' ?>>Confirmada</option>
                            <option value="pendiente" <?= ($filtroEstado === 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="en_estadia" <?= ($filtroEstado === 'en_estadia') ? 'selected' : '' ?>>En Estadía</option>
                            <option value="finalizada" <?= ($filtroEstado === 'finalizada') ? 'selected' : '' ?>>Finalizada</option>
                            <option value="cancelada" <?= ($filtroEstado === 'cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="filtroDesde" class="form-label">Desde (fecha entrada)</label>
                        <input type="date" name="desde" id="filtroDesde" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroDesde ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="filtroHasta" class="form-label">Hasta (fecha salida)</label>
                        <input type="date" name="hasta" id="filtroHasta" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroHasta ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                        <a href="<?= url('reservas') ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Reservas -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check me-2"></i>Reservas Registradas (<?= count($reservas) ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Huésped</th>
                                <th>Habitaciones</th>
                                <th>Entrada / Salida</th>
                                <th class="text-center">Noches</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Saldo</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No se encontraron reservas con los criterios especificados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservas as $r): ?>
                                    <tr>
                                        <td class="text-nowrap">
                                            <span class="fw-semibold text-primary font-monospace"><?= e($r['codigo_reserva']) ?></span>
                                            <small class="d-block text-body-secondary text-capitalize"><?= e($r['origen']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-nowrap"><?= e($r['huesped_nombre']) ?></div>
                                            <small class="d-block text-body-secondary text-nowrap">
                                                <?= e($r['numero_documento']) ?><?php if (!empty($r['huesped_telefono'])): ?> · <?= e($r['huesped_telefono']) ?><?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="text-nowrap">
                                            <i class="bi bi-door-closed text-body-secondary me-1"></i><?= e($r['habitaciones_asignadas'] ?: 'Sin asignar') ?>
                                        </td>
                                        <td class="text-nowrap">
                                            <div><i class="bi bi-box-arrow-in-right text-success me-1" title="Entrada"></i><?= fecha_es($r['fecha_entrada']) ?></div>
                                            <div><i class="bi bi-box-arrow-right text-danger me-1" title="Salida"></i><?= fecha_es($r['fecha_salida']) ?></div>
                                        </td>
                                        <td class="text-center"><?= (int) $r['noches'] ?></td>
                                        <td class="text-end text-nowrap fw-semibold"><?= dinero($r['total']) ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php if ((float)$r['saldo'] > 0): ?>
                                                <span class="fw-semibold text-danger"><?= dinero($r['saldo']) ?></span>
                                            <?php else: ?>
                                                <span class="text-success"><i class="bi bi-check2-circle me-1"></i>Pagado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= badge_estado_reserva($r['estado']) ?></td>
                                        <td class="text-end pe-3">
                                            <div class="table-actions">
                                                <a href="<?= url('reservas/editar/' . $r['id']) ?>" class="btn btn-icon btn-outline-primary" title="Editar / Ver Detalle">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <?php if ($r['estado'] === 'confirmada' || $r['estado'] === 'pendiente'): ?>
                                                    <button type="button" class="btn btn-icon btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelar<?= $r['id'] ?>" title="Cancelar reserva">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Modal Cancelación -->
                                            <div class="modal fade" id="modalCancelar<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog text-start">
                                                    <form method="POST" action="<?= url('reservas/cancelar') ?>" class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Cancelar Reserva <?= htmlspecialchars($r['codigo_reserva']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                            <p>¿Estás seguro de cancelar esta reserva para el huésped <strong><?= htmlspecialchars($r['huesped_nombre']) ?></strong>?</p>
                                                            <div class="mb-3">
                                                                <label for="motivoCancelacion<?= $r['id'] ?>" class="form-label">Motivo de Cancelación</label>
                                                                <textarea name="motivo_cancelacion" id="motivoCancelacion<?= $r['id'] ?>" class="form-control" rows="2" placeholder="Ej: Cancelación del viaje por motivos laborales..." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Volver</button>
                                                            <button type="submit" class="btn btn-danger">Confirmar Cancelación</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

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
</div>
