<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-cup-hot text-primary me-2"></i>Servicios Adicionales</h3>
                <p class="text-muted mb-0 small">Catálogo de restaurante, lavandería, bar, spa y traslados</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button type="button" class="btn btn-primary btn-sm shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalServicio">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Servicio
                </button>
                <a href="<?= url('servicios/consumo') ?>" class="btn btn-success btn-sm shadow-sm me-1">
                    <i class="bi bi-cart-plus me-1"></i> Registrar Consumo
                </a>
                <a href="<?= url('servicios/porHuesped') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-person-lines-fill me-1"></i> Ver Consumos
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-stars me-2"></i>Catálogo de Servicios Disponibles</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Servicio</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Precio Unitario</th>
                                <th>Total Consumos</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $s): ?>
                                <tr>
                                    <td><?= $s['id'] ?></td>
                                    <td><strong class="fs-6 text-body-emphasis"><?= htmlspecialchars($s['nombre']) ?></strong></td>
                                    <td>
                                        <span class="badge text-bg-light border fw-semibold">
                                            <?= htmlspecialchars($s['categoria']) ?>
                                        </span>
                                    </td>
                                    <td><small class="text-muted"><?= htmlspecialchars($s['descripcion'] ?: '-') ?></small></td>
                                    <td><span class="fw-bold fs-6 text-success"><?= dinero($s['precio']) ?></span></td>
                                    <td><span class="badge text-bg-secondary"><?= $s['total_pedidos'] ?> pedidos</span></td>
                                    <td>
                                        <?= $s['estado'] ? '<span class="badge text-bg-success">Disponible</span>' : '<span class="badge text-bg-secondary">Deshabilitado</span>' ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" onclick='editarServicio(<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="<?= url('servicios/eliminar/' . $s['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar servicio?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
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

<!-- Modal Servicio -->
<div class="modal fade" id="modalServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('servicios/guardar') ?>" class="modal-content">
            <input type="hidden" name="id" id="servId">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalServTitle"><i class="bi bi-cup-hot-fill me-2"></i>Servicio Adicional</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre del Servicio <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="servNombre" class="form-control" placeholder="Ej: Desayuno Buffet Americano, Masaje Relajante" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                        <select name="categoria" id="servCategoria" class="form-select" required>
                            <option value="Restaurante">Restaurante</option>
                            <option value="Bar">Bar</option>
                            <option value="Lavandería">Lavandería</option>
                            <option value="Room Service">Room Service</option>
                            <option value="Spa">Spa & Bienestar</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Precio (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="precio" id="servPrecio" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="estado" id="servEstado" class="form-select">
                        <option value="1">Activo / Disponible</option>
                        <option value="0">Deshabilitado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea name="descripcion" id="servDescripcion" class="form-control" rows="2" placeholder="Detalles de lo que incluye..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar Servicio</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarServicio(s) {
    document.getElementById('modalServTitle').textContent = 'Editar Servicio: ' + s.nombre;
    document.getElementById('servId').value = s.id;
    document.getElementById('servNombre').value = s.nombre;
    document.getElementById('servCategoria').value = s.categoria;
    document.getElementById('servPrecio').value = s.precio;
    document.getElementById('servEstado').value = s.estado;
    document.getElementById('servDescripcion').value = s.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalServicio')).show();
}

document.getElementById('modalServicio').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalServTitle').textContent = 'Nuevo Servicio';
    document.getElementById('servId').value = '';
    document.getElementById('servNombre').value = '';
    document.getElementById('servPrecio').value = '0.00';
    document.getElementById('servDescripcion').value = '';
});
</script>
