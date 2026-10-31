<?php partial('page_header', [
    'titulo'    => 'Métodos de Pago',
    'icono'     => 'bi-credit-card',
    'subtitulo' => 'Formas de pago autorizadas en recepción, caja y facturación',
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-wallet-fill me-2"></i>Canales de Pago Habilitados</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Método de Pago</th>
                                <th>Tipo / Red</th>
                                <th>Comisión Pasarela</th>
                                <th>Estado Operativo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metodos as $m): ?>
                                <tr>
                                    <td><strong><i class="bi bi-check2-circle text-success me-2"></i><?= htmlspecialchars($m['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($m['tipo']) ?></td>
                                    <td><?= htmlspecialchars($m['comision']) ?></td>
                                    <td><span class="badge text-bg-success"><?= htmlspecialchars($m['estado']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
