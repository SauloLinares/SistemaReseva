<?php partial('page_header', [
    'titulo'    => 'Modificar Huésped',
    'icono'     => 'bi-pencil-square',
    'subtitulo' => 'Editar datos de ' . $huesped['nombres'] . ' ' . $huesped['apellidos'],
    'migas'     => ['Huéspedes' => 'huespedes', 'Editar' => null],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 col-lg-8">
                <form action="<?= url('huespedes/actualizar') ?>" method="POST" class="card">
                    <input type="hidden" name="id" value="<?= $huesped['id'] ?>">

                    <?php partial('card_header', ['titulo' => 'Actualizar Ficha del Huésped', 'icono' => 'bi-person-vcard']) ?>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="tipo_documento" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                                <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                                    <option value="DNI" <?= ($huesped['tipo_documento'] === 'DNI') ? 'selected' : '' ?>>DNI (Perú)</option>
                                    <option value="Pasaporte" <?= ($huesped['tipo_documento'] === 'Pasaporte') ? 'selected' : '' ?>>Pasaporte</option>
                                    <option value="Carnet Extranjería" <?= ($huesped['tipo_documento'] === 'Carnet Extranjería') ? 'selected' : '' ?>>Carnet Extranjería</option>
                                    <option value="Otro" <?= ($huesped['tipo_documento'] === 'Otro') ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="numero_documento" class="form-label">Número de Documento <span class="text-danger">*</span></label>
                                <input type="text" id="numero_documento" name="numero_documento" class="form-control" value="<?= htmlspecialchars($huesped['numero_documento']) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" id="nombres" name="nombres" class="form-control" value="<?= htmlspecialchars($huesped['nombres']) ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" id="apellidos" name="apellidos" class="form-control" value="<?= htmlspecialchars($huesped['apellidos']) ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" id="correo" name="correo" class="form-control" value="<?= htmlspecialchars($huesped['correo'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="telefono" class="form-label">Teléfono / Celular</label>
                                <input type="text" id="telefono" name="telefono" class="form-control" value="<?= htmlspecialchars($huesped['telefono'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                <input type="text" id="nacionalidad" name="nacionalidad" class="form-control" value="<?= htmlspecialchars($huesped['nacionalidad'] ?? 'Perú') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select id="estado" name="estado" class="form-select">
                                    <option value="1" <?= $huesped['estado'] ? 'selected' : '' ?>>Activo</option>
                                    <option value="0" <?= !$huesped['estado'] ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="form-control" value="<?= htmlspecialchars($huesped['direccion'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea id="notas" name="notas" class="form-control" rows="2"><?= htmlspecialchars($huesped['notas'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <?php partial('form_actions', ['cancelar' => 'huespedes', 'texto' => 'Guardar Cambios']) ?>
                </form>
            </div>
        </div>

    </div>
</div>
