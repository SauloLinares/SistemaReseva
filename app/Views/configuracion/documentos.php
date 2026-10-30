<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text text-primary me-2"></i>Tipos de Documento</h3>
                <p class="text-muted mb-0 small">Documentos de identidad admitidos para el registro de huéspedes</p>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-card-checklist me-2"></i>Documentos Validados en el Sistema</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código / Sigla</th>
                                <th>Nombre Completo del Documento</th>
                                <th>Longitud Típica</th>
                                <th>Ámbito / Aplicación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $d): ?>
                                <tr>
                                    <td><span class="badge text-bg-primary fs-6"><?= htmlspecialchars($d['codigo']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($d['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($d['longitud']) ?></td>
                                    <td><?= htmlspecialchars($d['pais']) ?></td>
                                    <td><span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Habilitado</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
