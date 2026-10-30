<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Gestión de Productos</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= url() ?>">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Productos</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <!-- Mensajes Flash de Sesión -->
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Tarjeta Principal con Tabla de Productos -->
        <div class="card card-outline card-primary shadow-sm">
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
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 120px;">Código</th>
                                <th>Nombre y Descripción</th>
                                <th>Categoría</th>
                                <th class="text-end">Precio (S/.)</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width: 130px;">Acciones</th>
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
                                                <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                                    <?= htmlspecialchars($p['descripcion']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-secondary">
                                                <?= htmlspecialchars($p['categoria'] ?: 'Sin categoría') ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            S/. <?= number_format((float)$p['precio'], 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$p['stock'] <= 5): ?>
                                                <span class="badge text-bg-danger" title="¡Stock crítico!">
                                                    <i class="bi bi-exclamation-triangle"></i> <?= (int)$p['stock'] ?>
                                                </span>
                                            <?php elseif ((int)$p['stock'] <= 15): ?>
                                                <span class="badge text-bg-warning">
                                                    <?= (int)$p['stock'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge text-bg-success">
                                                    <?= (int)$p['stock'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int)$p['estado'] === 1): ?>
                                                <span class="badge text-bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= url('productos/editar/' . $p['id']) ?>" 
                                                   class="btn btn-outline-warning" 
                                                   title="Editar Producto">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="<?= url('productos/eliminar/' . $p['id']) ?>" 
                                                   class="btn btn-outline-danger" 
                                                   title="Eliminar Producto"
                                                   onclick="return confirm('¿Está seguro de eliminar el producto <?= htmlspecialchars($p['nombre']) ?>?');">
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
            <div class="card-footer bg-light text-muted small">
                Mostrando <?= count($productos ?? []) ?> producto(s) en total.
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
