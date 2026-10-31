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

        <div class="row">
            <div class="col-12 col-lg-8">
                <form action="<?= url('habitaciones/actualizar') ?>" method="POST" enctype="multipart/form-data" class="card">
                    <input type="hidden" name="id" value="<?= $habitacion['id'] ?>">
                    <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($habitacion['foto'] ?? '') ?>">

                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-door-closed me-2"></i>Actualizar Habitación</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="numero" class="form-label">Número de Habitación</label>
                                <input id="numero" type="text" name="numero" class="form-control form-control-lg fw-bold" value="<?= htmlspecialchars($habitacion['numero']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="piso" class="form-label">Piso / Nivel</label>
                                <input id="piso" type="text" name="piso" class="form-control form-control-lg" value="<?= htmlspecialchars($habitacion['piso']) ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="tipo_id" class="form-label">Tipo de Habitación</label>
                                <select id="tipo_id" name="tipo_id" class="form-select form-select-lg" required>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($t['id'] == $habitacion['tipo_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="precio_diario" class="form-label">Precio por Noche (S/.)</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input id="precio_diario" type="number" step="0.01" name="precio_diario" class="form-control" value="<?= $habitacion['precio_diario'] ?>" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select id="estado" name="estado" class="form-select">
                                    <option value="disponible" <?= ($habitacion['estado'] === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                                    <option value="ocupada" <?= ($habitacion['estado'] === 'ocupada') ? 'selected' : '' ?>>Ocupada</option>
                                    <option value="limpieza" <?= ($habitacion['estado'] === 'limpieza') ? 'selected' : '' ?>>Limpieza</option>
                                    <option value="mantenimiento" <?= ($habitacion['estado'] === 'mantenimiento') ? 'selected' : '' ?>>Mantenimiento</option>
                                </select>
                            </div>

                            <!-- Fotografía de la Habitación -->
                            <div class="col-12">
                                <label for="inputFotoHabEditar" class="form-label"><i class="bi bi-camera me-1 text-primary"></i>Fotografía de la Habitación</label>
                                <div class="row align-items-center g-3 p-3 bg-light rounded-3 border">
                                    <div class="col-auto">
                                        <?php 
                                            $fotoHab = $habitacion['foto'] ?? '';
                                            $rutaFotoHab = !empty($fotoHab) && file_exists(__DIR__ . '/../../../public/uploads/habitaciones/' . $fotoHab)
                                                ? base_url('uploads/habitaciones/' . $fotoHab)
                                                : base_url('uploads/habitaciones/simple.jpg');
                                        ?>
                                        <div class="bg-white border rounded-3 p-1 shadow-xs d-flex align-items-center justify-content-center foto-preview">
                                            <img id="previewFotoHabEditar" src="<?= $rutaFotoHab ?>" alt="Foto Habitación" class="img-fluid rounded w-100 h-100 object-fit-cover">
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
                                <label for="descripcion" class="form-label">Descripción / Características</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($habitacion['descripcion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php partial('form_actions', ['cancelar' => 'habitaciones', 'texto' => 'Guardar Cambios']) ?>
                </form>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/habitaciones-editar.js') ?>

    </div>
</div>
