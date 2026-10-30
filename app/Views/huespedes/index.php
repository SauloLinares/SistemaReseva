<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-people text-success me-2"></i>Gestión de Huéspedes</h3>
                <p class="text-muted mb-0 small">Directorio de clientes, historial de visitas y registro</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('huespedes/crear') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-person-plus me-1"></i> Registrar Huésped
                </a>
                <a href="<?= url('huespedes/buscar') ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="bi bi-search me-1"></i> Buscar por DNI
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

        <!-- Tarjeta de Búsqueda Rápida -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('huespedes') ?>" class="row g-2 align-items-center">
                    <div class="col-12 col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
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
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-card-list me-2"></i>Huéspedes Registrados (<?= count($huespedes) ?>)
                </h5>
            </div>
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
                                            <?= $h['estado'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= url('huespedes/historial/' . $h['id']) ?>" class="btn btn-outline-info" title="Ver Historial de Estadías">
                                                    <i class="bi bi-clock-history"></i>
                                                </a>
                                                <a href="<?= url('huespedes/editar/' . $h['id']) ?>" class="btn btn-outline-primary" title="Editar Huésped">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="<?= url('reservas/crear?huesped_id=' . $h['id']) ?>" class="btn btn-outline-success" title="Crear Reserva Directa">
                                                    <i class="bi bi-calendar-plus"></i>
                                                </a>
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
