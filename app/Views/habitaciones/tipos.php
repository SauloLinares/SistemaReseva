<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-tags text-primary me-2"></i>Tipos de Habitación</h3>
                <p class="text-muted mb-0 small">Categorías, capacidades y precios base estándar</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTipo">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Tipo
                </button>
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

        <div class="row g-4">
            <?php foreach ($tipos as $t): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-top border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($t['nombre']) ?></h5>
                                <span class="badge text-bg-light border"><?= $t['total_habitaciones'] ?> habs.</span>
                            </div>
                            <div class="text-muted small mb-3">
                                <?= htmlspecialchars($t['descripcion'] ?: 'Sin descripción') ?>
                            </div>
                            <ul class="list-unstyled small mb-3">
                                <li><i class="bi bi-person-fill text-muted me-2"></i>Capacidad: <strong><?= $t['capacidad'] ?> personas</strong></li>
                                <li><i class="bi bi-stars text-muted me-2"></i><?= htmlspecialchars($t['caracteristicas'] ?: 'Estándar') ?></li>
                            </ul>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div>
                                    <small class="text-muted d-block">Precio Base:</small>
                                    <span class="fs-5 fw-bold text-success"><?= dinero($t['precio_base']) ?></span>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick='editarTipo(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if ($t['total_habitaciones'] == 0): ?>
                                        <a href="<?= url('habitaciones/eliminarTipo/' . $t['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar tipo?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<!-- Modal Crear / Editar Tipo -->
<div class="modal fade" id="modalTipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('habitaciones/guardarTipo') ?>" class="modal-content">
            <input type="hidden" name="id" id="tipoId">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTipoTitle"><i class="bi bi-tag-fill me-2"></i>Tipo de Habitación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre de la Categoría <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="tipoNombre" class="form-control" placeholder="Ej: Simple, Matrimonial, Suite Deluxe" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Capacidad (Personas) <span class="text-danger">*</span></label>
                        <input type="number" name="capacidad" id="tipoCapacidad" class="form-control" value="1" min="1" max="10" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Precio Base por Noche <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="precio_base" id="tipoPrecioBase" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Características y Amenidades</label>
                    <input type="text" name="caracteristicas" id="tipoCaracteristicas" class="form-control" placeholder="Ej: Wi-Fi 5G, Smart TV 55', Tina, Vista al jardín">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea name="descripcion" id="tipoDescripcion" class="form-control" rows="2" placeholder="Detalles de la categoría..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarTipo(t) {
    document.getElementById('modalTipoTitle').textContent = 'Editar: ' + t.nombre;
    document.getElementById('tipoId').value = t.id;
    document.getElementById('tipoNombre').value = t.nombre;
    document.getElementById('tipoCapacidad').value = t.capacidad;
    document.getElementById('tipoPrecioBase').value = t.precio_base;
    document.getElementById('tipoCaracteristicas').value = t.caracteristicas || '';
    document.getElementById('tipoDescripcion').value = t.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalTipo')).show();
}

document.getElementById('modalTipo').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTipoTitle').textContent = 'Nuevo Tipo de Habitación';
    document.getElementById('tipoId').value = '';
    document.getElementById('tipoNombre').value = '';
    document.getElementById('tipoCapacidad').value = '1';
    document.getElementById('tipoPrecioBase').value = '0.00';
    document.getElementById('tipoCaracteristicas').value = '';
    document.getElementById('tipoDescripcion').value = '';
});
</script>
