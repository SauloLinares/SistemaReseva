<?php partial('page_header', [
    'titulo'    => 'Tarifas por Noche',
    'icono'     => 'bi-cash-stack',
    'subtitulo' => 'Ajuste y actualización de precios diarios por habitación',
    'acciones'  => [
        ['texto' => 'Precios Base por Tipo', 'url' => 'habitaciones/tipos', 'icono' => 'bi-tags', 'variante' => 'outline-primary'],
        ['texto' => 'Volver a Habitaciones', 'url' => 'habitaciones', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Tarifario Actual de Habitaciones</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Número</th>
                                <th>Piso</th>
                                <th>Tipo de Habitación</th>
                                <th>Tarifa Base Tipo</th>
                                <th>Tarifa Actual de la Habitación</th>
                                <th class="text-end pe-4">Actualizar Tarifa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($habitaciones as $h): ?>
                                <tr>
                                    <td class="fw-bold fs-5 text-primary">Hab. <?= htmlspecialchars($h['numero']) ?></td>
                                    <td>Piso <?= htmlspecialchars($h['piso']) ?></td>
                                    <td><span class="badge text-bg-light border"><?= htmlspecialchars($h['tipo_nombre']) ?></span></td>
                                    <td><?= dinero($h['precio_base'] ?? 0) ?></td>
                                    <td>
                                        <span class="fs-5 fw-bold text-success"><?= dinero($h['precio_diario']) ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <form method="POST" action="<?= url('habitaciones/guardarTarifa') ?>" class="d-inline-flex align-items-center gap-2">
                                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                            <div class="input-group input-group-sm" style="width: 170px;">
                                                <span class="input-group-text">S/.</span>
                                                <input type="number" step="0.01" name="precio_diario" class="form-control text-end fw-bold" value="<?= $h['precio_diario'] ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Guardar tarifa">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
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
