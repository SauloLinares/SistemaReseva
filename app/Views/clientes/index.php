<?php partial('page_header', [
    'titulo'   => 'Gestión de Clientes',
    'icono'    => 'bi-people',
    'migas'    => ['Clientes' => null],
    'acciones' => [
        ['texto' => 'Nuevo Cliente', 'url' => 'clientes/crear', 'icono' => 'bi-person-plus'],
    ],
]) ?>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Tarjeta Principal con Tabla de Clientes -->
        <div class="card">
            <?php partial('card_header', ['titulo' => 'Listado de Clientes Registrados', 'icono' => 'bi-card-list']) ?>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center col-w-xs">#</th>
                                <th>Nombres y Apellidos</th>
                                <th>DNI</th>
                                <th>Correo Electrónico</th>
                                <th>Teléfono</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center col-w-md">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $c): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= htmlspecialchars($c['id']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></strong>
                                        </td>
                                        <td><code><?= htmlspecialchars($c['dni']) ?></code></td>
                                        <td><?= htmlspecialchars($c['correo'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($c['telefono'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php if ((int)$c['estado'] === 1): ?>
                                                <span class="badge badge-status-switch active">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-status-switch inactive">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php partial('table_actions', [
                                                'editar'    => 'clientes/editar/' . $c['id'],
                                                'eliminar'  => 'clientes/eliminar/' . $c['id'],
                                                'confirmar' => '¿Está seguro de eliminar al cliente ' . $c['nombres'] . '?',
                                            ]) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No se encontraron clientes registrados en la base de datos.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <span class="text-muted small">
                    Total de registros: <strong><?= count($clientes ?? []) ?></strong>
                </span>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
