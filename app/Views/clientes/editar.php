<?php partial('page_header', [
    'titulo' => 'Editar Cliente',
    'icono'  => 'bi-pencil-square',
    'migas'  => ['Clientes' => 'clientes', 'Editar' => null],
]) ?>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 col-lg-8">
                <form action="<?= url('clientes/actualizar') ?>" method="POST" class="card">
                    <!-- Campo oculto con el ID del cliente -->
                    <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id']) ?>" />

                    <?php partial('card_header', ['titulo' => 'Modificar Datos del Cliente #' . $cliente['id'], 'icono' => 'bi-person-vcard']) ?>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombres" name="nombres"
                                       value="<?= htmlspecialchars($cliente['nombres']) ?>" required />
                            </div>

                            <div class="col-md-6">
                                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos"
                                       value="<?= htmlspecialchars($cliente['apellidos']) ?>" required />
                            </div>

                            <div class="col-md-6">
                                <label for="dni" class="form-label">DNI / Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dni" name="dni" maxlength="8"
                                       value="<?= htmlspecialchars($cliente['dni']) ?>" required />
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono"
                                       value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>" />
                            </div>

                            <div class="col-md-8">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo"
                                       value="<?= htmlspecialchars($cliente['correo'] ?? '') ?>" />
                            </div>

                            <div class="col-md-4">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1" <?= ((int)$cliente['estado'] === 1) ? 'selected' : '' ?>>Activo</option>
                                    <option value="0" <?= ((int)$cliente['estado'] === 0) ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php partial('form_actions', ['cancelar' => 'clientes', 'texto' => 'Guardar Cambios']) ?>
                </form>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
