<?php partial('page_header', [
    'titulo'    => 'Modificar Habitación ' . $habitacion['numero'],
    'icono'     => 'bi-pencil-square',
    'subtitulo' => 'Editar detalles, tipo, tarifa o estado',
    'acciones'  => [
        ['texto' => 'Volver a la Lista', 'url' => 'habitaciones', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('habitaciones/actualizar') ?>" method="POST" enctype="multipart/form-data" class="card border-top border-4 border-primary">
                    <input type="hidden" name="id" value="<?= $habitacion['id'] ?>">
                    <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($habitacion['foto'] ?? '') ?>">

                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-door-closed me-2"></i>Actualizar Habitación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Número de Habitación</label>
                                <input type="text" name="numero" class="form-control form-control-lg fw-bold" value="<?= htmlspecialchars($habitacion['numero']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Piso / Nivel</label>
                                <input type="text" name="piso" class="form-control form-control-lg" value="<?= htmlspecialchars($habitacion['piso']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tipo de Habitación</label>
                                <select name="tipo_id" class="form-select form-select-lg" required>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($t['id'] == $habitacion['tipo_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Precio por Noche (S/.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" step="0.01" name="precio_diario" class="form-control" value="<?= $habitacion['precio_diario'] ?>" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="disponible" <?= ($habitacion['estado'] === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                                    <option value="ocupada" <?= ($habitacion['estado'] === 'ocupada') ? 'selected' : '' ?>>Ocupada</option>
                                    <option value="limpieza" <?= ($habitacion['estado'] === 'limpieza') ? 'selected' : '' ?>>Limpieza</option>
                                    <option value="mantenimiento" <?= ($habitacion['estado'] === 'mantenimiento') ? 'selected' : '' ?>>Mantenimiento</option>
                                </select>
                            </div>

                            <!-- Fotografía de la Habitación -->
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-camera me-1 text-primary"></i>Fotografía de la Habitación</label>
                                <div class="row align-items-center g-3 p-3 bg-light rounded-3 border">
                                    <div class="col-auto">
                                        <?php 
                                            $fotoHab = $habitacion['foto'] ?? '';
                                            $rutaFotoHab = !empty($fotoHab) && file_exists(__DIR__ . '/../../../public/uploads/habitaciones/' . $fotoHab)
                                                ? base_url('uploads/habitaciones/' . $fotoHab)
                                                : base_url('uploads/habitaciones/simple.jpg');
                                        ?>
                                        <div class="bg-white border rounded-3 p-1 shadow-xs d-flex align-items-center justify-content-center" style="width: 130px; height: 95px; overflow: hidden;">
                                            <img id="previewFotoHabEditar" src="<?= $rutaFotoHab ?>" alt="Foto Habitación" class="img-fluid rounded" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <input type="file" name="foto" id="inputFotoHabEditar" class="form-control" accept="image/png, image/jpeg, image/webp">
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Formatos: <strong>JPG, PNG, WebP</strong>. Selecciona un archivo para actualizar la foto de esta habitación.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción / Características</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($habitacion['descripcion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php partial('form_actions', ['cancelar' => 'habitaciones', 'texto' => 'Guardar Cambios']) ?>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('inputFotoHabEditar');
    const previewFoto = document.getElementById('previewFotoHabEditar');

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

    </div>
</div>
