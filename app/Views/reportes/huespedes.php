<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-people-fill text-primary me-2"></i>Reporte de Huéspedes Frecuentes</h3>
                <p class="text-muted mb-0 small">Fidelización de clientes, historial de visitas y gasto acumulado</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimir Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-trophy-fill text-warning me-2"></i>Ranking de Clientes por Visitas e Ingresos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Huésped</th>
                                <th>Documento</th>
                                <th>Contacto</th>
                                <th>Nacionalidad</th>
                                <th>Total Estadías</th>
                                <th>Última Visita</th>
                                <th class="text-end pe-3">Gasto Total Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($huespedes as $h): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= ($i <= 3) ? 'text-bg-warning text-dark' : 'text-bg-light border' ?>">
                                            #<?= $i++ ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?></div>
                                    </td>
                                    <td><span class="badge text-bg-light border"><?= htmlspecialchars($h['tipo_documento'] . ' ' . $h['numero_documento']) ?></span></td>
                                    <td>
                                        <small class="d-block"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($h['telefono'] ?: '-') ?></small>
                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($h['correo'] ?: '-') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($h['nacionalidad'] ?: 'Perú') ?></td>
                                    <td><span class="badge text-bg-primary fs-6"><?= $h['total_reservas'] ?> reservas</span></td>
                                    <td><small class="text-muted"><?= fecha_es($h['ultima_visita']) ?></small></td>
                                    <td class="text-end pe-3 fw-bold text-success fs-6"><?= dinero($h['total_gastado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
