<?php partial('page_header', [
    'titulo'    => 'Métodos de Pago',
    'icono'     => 'bi-credit-card',
    'subtitulo' => 'Formas de pago que acepta el sistema y cuánto se cobró con cada una',
    'extra'     => '<form method="GET" action="' . e(url('configuracion/metodosPago')) . '" class="d-inline-flex gap-2">'
                 . '<input type="month" name="mes" class="form-control form-control-sm" aria-label="Mes" value="' . e($mes) . '" max="' . date('Y-m') . '" data-autoenvio>'
                 . '</form>',
]) ?>

<?php
    // Qué hace el sistema con cada método (cierre de caja: app/Models/Caja.php)
    $detalle = [
        'Efectivo'               => ['bi-cash-stack', 'success', 'Entra al efectivo esperado del arqueo de caja.'],
        'Tarjeta de Crédito'     => ['bi-credit-card', 'primary', 'Se suma a «Tarjetas» en el cierre de caja; no al efectivo.'],
        'Tarjeta de Débito'      => ['bi-credit-card-2-front', 'info', 'Se suma a «Tarjetas» en el cierre de caja; no al efectivo.'],
        'Transferencia Bancaria' => ['bi-bank', 'secondary', 'Se suma a «Transferencias» en el cierre de caja.'],
        'Yape/Plin'              => ['bi-phone', 'warning', 'Se suma a «Billeteras digitales» en el cierre de caja.'],
    ];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    [$anio, $numMes] = explode('-', $mes);
    $nombreMes = $meses[(int)$numMes - 1] . ' de ' . $anio;

    $total = array_sum(array_column($resumen, 'cobrado'));
    $operaciones = array_sum(array_column($resumen, 'operaciones'));
    $devuelto = array_sum(array_column($resumen, 'devuelto'));
    // Método con más cobrado (si hay empate, todos los que empatan)
    $mayor = max(array_column($resumen, 'cobrado'));
    $principales = $total > 0 ? array_keys(array_filter($resumen, fn($r) => $r['cobrado'] === $mayor)) : [];
?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Resumen del mes -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4">
                <div class="cash-stat">
                    <span class="cash-stat-label">Cobrado en <?= e($nombreMes) ?></span>
                    <span class="cash-stat-valor" data-total-cobrado><?= dinero($total) ?></span>
                    <?php if ($devuelto > 0): ?><span class="cash-stat-nota">Devoluciones: <?= dinero($devuelto) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="cash-stat">
                    <span class="cash-stat-label">Cobros registrados</span>
                    <span class="cash-stat-valor"><?= $operaciones ?></span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="cash-stat">
                    <span class="cash-stat-label"><?= count($principales) > 1 ? 'Métodos con más cobrado (empate)' : 'Método con más cobrado' ?></span>
                    <span class="cash-stat-valor fs-6 text-wrap lh-sm"><?= $principales ? e(implode(' y ', $principales)) : '—' ?></span>
                    <?php if ($principales): ?><span class="cash-stat-nota"><?= round($mayor * 100 / $total) ?>% de lo cobrado<?= count($principales) > 1 ? ' cada uno' : '' ?></span><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <?php partial('card_header', ['titulo' => 'Formas de pago aceptadas', 'icono' => 'bi-wallet2']) ?>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tablaMetodos">
                        <thead>
                            <tr>
                                <th>Método de pago</th>
                                <th class="text-end">Cobros</th>
                                <th class="text-end">Cobrado</th>
                                <th class="text-end col-w-md">Participación</th>
                                <th>Usado por última vez</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumen as $metodo => $r): ?>
                                <?php [$icono, $color, $nota] = $detalle[$metodo] ?? ['bi-wallet2', 'secondary', '']; ?>
                                <?php $pct = $total > 0 ? round($r['cobrado'] * 100 / $total) : 0; ?>
                                <tr class="<?= $r['operaciones'] === 0 ? 'fila-sin-actividad' : '' ?>" data-metodo="<?= e($metodo) ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-<?= $color ?>-subtle text-<?= $color ?> avatar-sm flex-shrink-0">
                                                <i class="bi <?= $icono ?>" aria-hidden="true"></i>
                                            </span>
                                            <div class="metodo-pago-nombre">
                                                <strong><?= e($metodo) ?></strong>
                                                <small class="d-block text-body-secondary"><?= e($nota) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end"><?= $r['operaciones'] ?></td>
                                    <td class="text-end fw-semibold text-nowrap">
                                        <?= dinero($r['cobrado']) ?>
                                        <?php if ($r['devuelto'] > 0): ?><small class="d-block text-danger">− <?= dinero($r['devuelto']) ?> devuelto</small><?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?= $pct ?>%
                                        <div class="barra-ranking" aria-hidden="true"><span style="width: <?= $pct ?>%"></span></div>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if ($r['ultimo_uso']): ?>
                                            <?= fecha_es($r['ultimo_uso']) ?>
                                        <?php else: ?>
                                            <span class="text-body-secondary">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer small text-body-secondary">
                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                Estos son los métodos que acepta el sistema al cobrar. Un cobro dividido (parte en efectivo y parte con tarjeta)
                cuenta en cada método por lo que se pagó con él. Agregar un método nuevo requiere un cambio en la base de datos:
                pídelo a quien da soporte al sistema.
            </div>
        </div>

    </div>
</div>
