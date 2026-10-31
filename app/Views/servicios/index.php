<?php partial('page_header', [
    'titulo'    => 'Servicios Adicionales',
    'icono'     => 'bi-cup-hot',
    'subtitulo' => 'Catálogo de restaurante, lavandería, bar, spa y traslados',
    'acciones'  => [
        ['texto' => 'Registrar Consumo', 'url' => 'servicios/consumo', 'icono' => 'bi-cart-plus', 'variante' => 'outline-secondary'],
        ['texto' => 'Ver Consumos', 'url' => 'servicios/porHuesped', 'icono' => 'bi-person-lines-fill', 'variante' => 'outline-secondary'],
        ['texto' => 'Nuevo Servicio', 'modal' => '#modalServicio', 'icono' => 'bi-plus-lg'],
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
                                <th>Servicio</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">Consumos</th>
                                <th>Estado</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $s): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($s['nombre']) ?></td>
                                    <td class="text-nowrap text-body-secondary"><?= e($s['categoria']) ?></td>
                                    <td>
                                        <?php $desc = $s['descripcion'] ?: '-'; ?>
                                        <span class="text-body-secondary text-clamp-2" title="<?= e($desc) ?>"><?= e($desc) ?></span>
                                    </td>
                                    <td class="text-end text-nowrap fw-semibold"><?= dinero($s['precio']) ?></td>
                                    <td class="text-center"><?= (int) $s['total_pedidos'] ?></td>
                                    <td>
                                        <?php partial('interruptor_estado', ['ruta' => 'servicios/cambiarEstado', 'id' => $s['id'], 'activo' => (bool)$s['estado'], 'nombre' => $s['nombre'], 'textos' => ['Disponible', 'Deshabilitado']]) ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-icon btn-outline-primary" title="Editar" data-editar="<?= e(json_encode($s, JSON_UNESCAPED_UNICODE)) ?>">
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
            <div class="modal-header">
                <h5 class="modal-title" id="modalServTitle"><i class="bi bi-cup-hot-fill me-2 text-primary"></i>Servicio Adicional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="servNombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="servNombre" class="form-control" placeholder="Ej: Desayuno Buffet Americano, Masaje Relajante" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="servCategoria" class="form-label">Categoría <span class="text-danger">*</span></label>
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
                        <label for="servPrecio" class="form-label">Precio (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="precio" id="servPrecio" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="servEstado" class="form-label">Estado</label>
                    <select name="estado" id="servEstado" class="form-select">
                        <option value="1">Activo / Disponible</option>
                        <option value="0">Deshabilitado</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="servDescripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="servDescripcion" class="form-control" rows="2" placeholder="Detalles de lo que incluye..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Servicio</button>
            </div>
        </form>
    </div>
</div>

<?= script_js('vistas/servicios-index.js') ?>
