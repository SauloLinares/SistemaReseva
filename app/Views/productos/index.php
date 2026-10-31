<?php partial('page_header', [
    'titulo' => 'Gestión de Productos',
    'icono'  => 'bi-box-seam',
    'migas'  => ['Productos' => null],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Tarjeta Principal con Tabla de Productos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="bi bi-box-seam me-2"></i> Listado de Inventario de Productos
                </h3>
                <div class="card-tools ms-auto">
                    <a href="<?= url('productos/crear') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Producto
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center col-w-xs">#</th>
                                <th class="col-w-sm">Código</th>
                                <th>Nombre y Descripción</th>
                                <th>Categoría</th>
                                <th class="text-end">Precio (S/.)</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center col-w-md">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $p): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= htmlspecialchars($p['id']) ?></td>
                                        <td>
                                            <span class="badge bg-body-secondary border font-monospace text-body">
                                                <?= htmlspecialchars($p['codigo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-body-emphasis"><?= htmlspecialchars($p['nombre']) ?></div>
                                            <?php if (!empty($p['descripcion'])): ?>
                                                <small class="text-muted d-block text-truncate mw-250">
                                                    <?= htmlspecialchars($p['descripcion']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-secondary">
                                                <?= htmlspecialchars($p['categoria'] ?: 'Sin categoría') ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            S/. <?= number_format((float)$p['precio'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$p['stock'] <= 5): ?>
                                                <span class="badge badge-soft-danger" title="¡Stock crítico!">
                                                    <i class="bi bi-exclamation-triangle"></i> <?= (int)$p['stock'] ?>
                                                </span>
                                            <?php elseif ((int)$p['stock'] <= 15): ?>
                                                <span class="badge badge-soft-warning">
                                                    <?= (int)$p['stock'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-success">
                                                    <?= (int)$p['stock'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$p['estado'] === 1): ?>
                                                <span class="badge badge-status-switch active">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-status-switch inactive">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="table-actions">
                                                <a href="<?= url('productos/editar/' . $p['id']) ?>" 
                                                   class="btn btn-icon btn-outline-primary" 
                                                   title="Editar Producto">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="<?= url('productos/eliminar/' . $p['id']) ?>" 
                                                   class="btn btn-icon btn-outline-danger" 
                                                   title="Eliminar Producto"
                                                   data-confirm="¿Está seguro de eliminar el producto <?= htmlspecialchars($p['nombre']) ?>?">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No se encontraron productos registrados en la base de datos.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted small">
                Mostrando <?= count($productos ?? []) ?> producto(s) en total.
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
