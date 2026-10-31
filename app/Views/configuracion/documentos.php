<?php partial('page_header', [
    'titulo'    => 'Tipos de Documento',
    'icono'     => 'bi-file-earmark-text',
    'subtitulo' => 'Documentos de identidad admitidos para el registro de huéspedes',
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-card-checklist me-2"></i>Documentos Validados en el Sistema</h5>
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
