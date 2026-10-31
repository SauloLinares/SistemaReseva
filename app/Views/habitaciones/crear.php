<?php partial('page_header', [
    'titulo'    => 'Registrar Habitación',
    'icono'     => 'bi-plus-square',
    'subtitulo' => 'Añadir nueva habitación al inventario hotelero',
    'acciones'  => [
        ['texto' => 'Volver a la Lista', 'url' => 'habitaciones', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('habitaciones/guardar') ?>" method="POST" enctype="multipart/form-data" class="card border-top border-4 border-warning">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-door-closed me-2"></i>Datos de la Habitación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Número de Habitación <span class="text-danger">*</span></label>
                                <input type="text" name="numero" class="form-control form-control-lg fw-bold" placeholder="Ej: 101, 204B" required autofocus>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Piso / Nivel <span class="text-danger">*</span></label>
                                <input type="text" name="piso" class="form-control form-control-lg" value="1" placeholder="Ej: 1, 2, PB" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tipo de Habitación <span class="text-danger">*</span></label>
                                <select name="tipo_id" id="tipo_id" class="form-select form-select-lg" required>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>" data-precio="<?= $t['precio_base'] ?>">
                                            <?= htmlspecialchars($t['nombre']) ?> (Base: <?= dinero($t['precio_base']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Precio por Noche (S/.) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" step="0.01" name="precio_diario" id="precio_diario" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Estado Inicial</label>
                                <select name="estado" class="form-select">
                                    <option value="disponible" selected>Disponible</option>
                                    <option value="ocupada">Ocupada</option>
                                    <option value="limpieza">Limpieza</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>

                            <!-- Fotografía de la Habitación -->
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-camera me-1 text-primary"></i>Fotografía de la Habitación</label>
                                <div class="row align-items-center g-3 p-3 bg-light rounded-3 border">
                                    <div class="col-auto">
                                        <div class="bg-white border rounded-3 p-1 shadow-xs d-flex align-items-center justify-content-center" style="width: 120px; height: 85px; overflow: hidden;">
                                            <img id="previewFotoHab" src="<?= base_url('uploads/habitaciones/simple.jpg') ?>" alt="Vista previa" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="file" name="foto" id="inputFotoHab" class="form-control" accept="image/png, image/jpeg, image/webp">
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Formatos: <strong>JPG, PNG, WebP</strong>. Si no adjuntas una foto, se asignará automáticamente la imagen de muestra de su tipo de habitación.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción / Características Específicas</label>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Ej: Vista a la piscina, cama matrimonial, frigobar, baño con tina..."></textarea>
                            </div>
                        </div>
                    </div>
                    <?php partial('form_actions', ['cancelar' => 'habitaciones', 'texto' => 'Guardar Habitación']) ?>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo_id');
    const precioInput = document.getElementById('precio_diario');
    const inputFoto = document.getElementById('inputFotoHab');
    const previewFoto = document.getElementById('previewFotoHab');

    function actualizarPrecioBase() {
        const selected = tipoSelect.options[tipoSelect.selectedIndex];
        if (selected) {
            precioInput.value = selected.dataset.precio || '0.00';
        }
    }

    tipoSelect.addEventListener('change', actualizarPrecioBase);
    actualizarPrecioBase();

    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewFoto.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
