<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-clock-history text-info me-2"></i>Historial de Huéspedes</h3>
                <p class="text-muted mb-0 small">Selecciona un huésped para ver su historial completo de estadías</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('huespedes') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Directorio de Huéspedes
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-people me-2"></i>Todos los Huéspedes y Estadías</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Huésped</th>
                                <th>Documento</th>
                                <th>Teléfono</th>
                                <th>Total Estadías</th>
                                <th>Última Estadía</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($huespedes as $h): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?></td>
                                    <td><span class="badge text-bg-light border"><?= htmlspecialchars($h['tipo_documento'] . ' ' . $h['numero_documento']) ?></span></td>
                                    <td><?= htmlspecialchars($h['telefono'] ?: '-') ?></td>
                                    <td><span class="badge text-bg-primary fs-6"><?= $h['total_reservas'] ?></span></td>
                                    <td><?= fecha_es($h['ultima_estadia']) ?></td>
                                    <td class="text-end pe-3">
                                        <a href="<?= url('huespedes/historial/' . $h['id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-clock-history me-1"></i> Ver Estadías
                                        </a>
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
