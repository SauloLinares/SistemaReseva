<?php partial('page_header', [
    'titulo'    => 'Gestión de Huéspedes',
    'icono'     => 'bi-people',
    'subtitulo' => 'Directorio de clientes, historial de visitas y registro',
    'migas'     => ['Huéspedes' => null],
    'acciones'  => [
        ['texto' => 'Registrar Huésped', 'url' => 'huespedes/crear', 'icono' => 'bi-person-plus'],
        ['texto' => 'Buscar por DNI', 'url' => 'huespedes/buscar', 'icono' => 'bi-search', 'variante' => 'outline-primary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Tarjeta de Búsqueda Rápida -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('huespedes') ?>" class="row g-2 align-items-center">
                    <div class="col-12 col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Buscar por DNI, pasaporte, nombres, apellidos o teléfono..." value="<?= htmlspecialchars($busqueda ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i> Buscar
                        </button>
                        <?php if (!empty($busqueda)): ?>
                            <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary">Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Huéspedes -->
        <div class="card">
            <?php partial('card_header', ['titulo' => 'Huéspedes Registrados (' . count($huespedes) . ')', 'icono' => 'bi-card-list']) ?>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Documento</th>
                                <th>Huésped</th>
                                <th>Contacto</th>
                                <th>Nacionalidad</th>
                                <th>Total Reservas</th>
                                <th>Última Estadía</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($huespedes)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No se encontraron huéspedes registrados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($huespedes as $h): ?>
                                    <tr>
                                        <td><?= $h['id'] ?></td>
                                        <td>
                                            <span class="badge text-bg-light border fw-bold">
                                                <?= htmlspecialchars($h['tipo_documento']) ?>: <?= htmlspecialchars($h['numero_documento']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($h['direccion'] ?: 'Sin dirección') ?></small>
                                        </td>
                                        <td>
                                            <div><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($h['telefono'] ?: '-') ?></div>
                                            <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($h['correo'] ?: '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-secondary-subtle text-secondary-emphasis">
                                                <?= htmlspecialchars($h['nacionalidad'] ?: 'Perú') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= url('huespedes/historial/' . $h['id']) ?>" class="badge text-bg-primary text-decoration-none" title="Ver historial">
                                                <?= $h['total_reservas'] ?> reservas
                                            </a>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= fecha_es($h['ultima_estadia']) ?></small>
                                        </td>
                                        <td>
                                            <?= $h['estado'] ? '<span class="badge badge-status-switch active">Activo</span>' : '<span class="badge badge-status-switch inactive">Inactivo</span>' ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php partial('table_actions', [
                                                'extras' => [
                                                    ['url' => 'huespedes/historial/' . $h['id'], 'icono' => 'bi-clock-history', 'titulo' => 'Ver Historial de Estadías'],
                                                    ['url' => 'reservas/crear?huesped_id=' . $h['id'], 'icono' => 'bi-calendar-plus', 'titulo' => 'Crear Reserva Directa', 'variante' => 'outline-success'],
                                                ],
                                                'editar' => 'huespedes/editar/' . $h['id'],
                                            ]) ?>
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
