<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-credit-card text-primary me-2"></i>Métodos de Pago</h3>
                <p class="text-muted mb-0 small">Formas de pago autorizadas en recepción, caja y facturación</p>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-wallet-fill me-2"></i>Canales de Pago Habilitados</h5>
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
