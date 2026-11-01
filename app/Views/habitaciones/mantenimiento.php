<?php partial('page_header', [
    'titulo'    => 'Mantenimiento y Limpieza',
    'icono'     => 'bi-tools',
    'subtitulo' => 'Incidencias, reparaciones, protocolos de limpieza y desinfección',
    'acciones'  => [
        ['texto' => 'Nueva Orden de Mantenimiento / Limpieza', 'modal' => '#modalMant', 'icono' => 'bi-plus-circle'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-task me-2"></i>Historial de Tareas y Partes de Mantenimiento</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Habitación</th>
                                <th>Tipo</th>
                                <th>Descripción de la Tarea / Incidencia</th>
                                <th>Responsable</th>
                                <th>Fecha Inicio</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mantenimientos)): ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No hay registros de mantenimiento o limpieza activos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($mantenimientos as $m): ?>
                                    <tr>
                                        <td><?= $m['id'] ?></td>
                                        <td>
                                            <span class="fw-bold fs-6 text-primary">Hab. <?= htmlspecialchars($m['habitacion_numero']) ?></span>
                                            <small class="text-muted d-block"><?= htmlspecialchars($m['tipo_nombre']) ?> (Piso <?= $m['piso'] ?>)</small>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($m['tipo'] === 'limpieza') ? 'badge-soft-warning' : 'badge-soft-secondary' ?> text-uppercase">
                                                <?= htmlspecialchars($m['tipo']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($m['descripcion']) ?></td>
                                        <td><?= htmlspecialchars($m['responsable'] ?: 'No asignado') ?></td>
                                        <td><small class="text-muted"><?= fecha_es($m['fecha_inicio'], true) ?></small></td>
                                        <td><?= dinero($m['costo']) ?></td>
                                        <td>
                                            <?= match($m['estado']) {
                                                'pendiente'  => '<span class="badge badge-soft-warning"><i class="bi bi-clock me-1"></i>Pendiente</span>',
                                                'en_proceso' => '<span class="badge badge-soft-info"><i class="bi bi-arrow-repeat me-1"></i>En Proceso</span>',
                                                'finalizado' => '<span class="badge badge-soft-success"><i class="bi bi-check-all me-1"></i>Finalizado</span>',
                                                default      => htmlspecialchars($m['estado'])
                                            } ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if ($m['estado'] !== 'finalizado'): ?>
                                                <a href="<?= url('habitaciones/finalizarMantenimiento/' . $m['id']) ?>" class="btn btn-sm btn-success" title="Finalizar tarea y habilitar habitación" data-confirm="¿Confirmar que el mantenimiento/limpieza ha finalizado? La habitación pasará a estado DISPONIBLE.">
                                                    <i class="bi bi-check-circle me-1"></i> Finalizar
                                                </a>
                                            <?php else: ?>
                                                <small class="text-muted">Cerrado <?= fecha_es($m['fecha_fin'], true) ?></small>
                                            <?php endif; ?>
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

<!-- Modal Registrar Mantenimiento -->
<div class="modal fade" id="modalMant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('habitaciones/guardarMantenimiento') ?>" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-tools me-2 text-body-secondary"></i>Nueva Tarea de Mantenimiento / Limpieza</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="habitacion_id" class="form-label">Habitación <span class="text-danger">*</span></label>
                    <select id="habitacion_id" name="habitacion_id" class="form-select" required>
                        <?php foreach ($habitaciones as $h): ?>
                            <option value="<?= $h['id'] ?>">
                                Hab. <?= htmlspecialchars($h['numero']) ?> - <?= htmlspecialchars($h['tipo_nombre']) ?> (Piso <?= $h['piso'] ?> - Actual: <?= $h['estado'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select id="tipo" name="tipo" class="form-select">
                            <option value="limpieza">Limpieza y Desinfección</option>
                            <option value="mantenimiento">Mantenimiento Preventivo</option>
                            <option value="reparacion">Reparación / Avería</option>
                            <option value="inspeccion">Inspección de Calidad</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="estado" class="form-label">Estado Inicial</label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso" selected>En Proceso</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="responsable" class="form-label">Responsable / Encargado</label>
                    <input id="responsable" type="text" name="responsable" class="form-control" placeholder="Ej: Juan Pérez / Empresa externa">
                </div>
                <div class="mb-3">
                    <label for="costo" class="form-label">Costo Estimado (<?= e(moneda()) ?>)</label>
                    <input id="costo" type="number" step="0.01" name="costo" class="form-control" value="0.00">
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción del Trabajo <span class="text-danger">*</span></label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Describe la falla, requerimiento o protocolo a ejecutar..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar e Iniciar</button>
            </div>
        </form>
    </div>
</div>
