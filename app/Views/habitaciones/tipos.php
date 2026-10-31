<?php partial('page_header', [
    'titulo'    => 'Tipos de Habitación',
    'icono'     => 'bi-tags',
    'subtitulo' => 'Categorías, capacidades y precios base estándar',
    'acciones'  => [
        ['texto' => 'Nuevo Tipo', 'modal' => '#modalTipo', 'icono' => 'bi-plus-circle'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-4">
            <?php foreach ($tipos as $t): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($t['nombre']) ?></h5>
                                <span class="badge badge-soft-secondary"><?= $t['total_habitaciones'] ?> habs.</span>
                            </div>
                            <div class="mb-2">
                                <?php partial('interruptor_estado', ['ruta' => 'habitaciones/cambiarEstadoTipo', 'id' => $t['id'], 'activo' => (bool)$t['estado'], 'nombre' => $t['nombre']]) ?>
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
                                <div class="table-actions">
                                    <button type="button" class="btn btn-icon btn-outline-primary" 
                                            data-editar="<?= e(json_encode($t, JSON_UNESCAPED_UNICODE)) ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if ($t['total_habitaciones'] == 0): ?>
                                        <a href="<?= url('habitaciones/eliminarTipo/' . $t['id']) ?>" class="btn btn-icon btn-outline-danger" title="Eliminar" data-confirm="¿Eliminar tipo?">
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
            <div class="modal-header">
                <h5 class="modal-title" id="modalTipoTitle"><i class="bi bi-tag-fill me-2 text-primary"></i>Tipo de Habitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="tipoNombre" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="tipoNombre" class="form-control" placeholder="Ej: Simple, Matrimonial, Suite Deluxe" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="tipoCapacidad" class="form-label">Capacidad (Personas) <span class="text-danger">*</span></label>
                        <input type="number" name="capacidad" id="tipoCapacidad" class="form-control" value="1" min="1" max="10" required>
                    </div>
                    <div class="col-6">
                        <label for="tipoPrecioBase" class="form-label">Precio Base por Noche <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="precio_base" id="tipoPrecioBase" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="tipoCaracteristicas" class="form-label">Características y Amenidades</label>
                    <input type="text" name="caracteristicas" id="tipoCaracteristicas" class="form-control" placeholder="Ej: Wi-Fi 5G, Smart TV 55', Tina, Vista al jardín">
                </div>
                <div class="mb-3">
                    <label for="tipoDescripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="tipoDescripcion" class="form-control" rows="2" placeholder="Detalles de la categoría..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?= script_js('vistas/habitaciones-tipos.js') ?>
