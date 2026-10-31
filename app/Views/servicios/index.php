<?php partial('page_header', [
    'titulo'    => 'Servicios Adicionales',
    'icono'     => 'bi-cup-hot',
    'subtitulo' => 'Catálogo de restaurante, lavandería, bar, spa y traslados',
    'acciones'  => [
        ['texto' => 'Nuevo Servicio', 'modal' => '#modalServicio', 'icono' => 'bi-plus-circle'],
        ['texto' => 'Registrar Consumo', 'url' => 'servicios/consumo', 'icono' => 'bi-cart-plus', 'variante' => 'success'],
        ['texto' => 'Ver Consumos', 'url' => 'servicios/porHuesped', 'icono' => 'bi-person-lines-fill', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-stars me-2"></i>Catálogo de Servicios Disponibles</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 datatable-init">
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
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-icon btn-outline-primary" title="Editar" onclick='editarServicio(<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="<?= url('servicios/eliminar/' . $s['id']) ?>" class="btn btn-icon btn-outline-danger" title="Eliminar" data-confirm="¿Eliminar servicio?">
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
                    <label class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="servNombre" class="form-control" placeholder="Ej: Desayuno Buffet Americano, Masaje Relajante" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Categoría <span class="text-danger">*</span></label>
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
                        <label class="form-label">Precio (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="precio" id="servPrecio" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" id="servEstado" class="form-select">
                        <option value="1">Activo / Disponible</option>
                        <option value="0">Deshabilitado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
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
