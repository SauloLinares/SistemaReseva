<?php partial('page_header', [
    'titulo' => 'Registrar Cliente',
    'icono'  => 'bi-person-plus',
    'migas'  => ['Clientes' => 'clientes', 'Nuevo' => null],
]) ?>

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <form action="<?= url('clientes/guardar') ?>" method="POST" class="card">
                    <?php partial('card_header', ['titulo' => 'Datos del Nuevo Cliente', 'icono' => 'bi-person-vcard']) ?>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required placeholder="Ej: Juan Carlos" />
                            </div>

                            <div class="col-md-6">
                                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required placeholder="Ej: Pérez Gomez" />
                            </div>

                            <div class="col-md-6">
                                <label for="dni" class="form-label">DNI / Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dni" name="dni" maxlength="8" required placeholder="Ej: 71234567" />
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej: 987654321" />
                            </div>

                            <div class="col-md-8">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" placeholder="cliente@correo.com" />
                            </div>

                            <div class="col-md-4">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1" selected>Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php partial('form_actions', ['cancelar' => 'clientes', 'texto' => 'Guardar Cliente']) ?>
                </form>
            </div>
        </div>

    </div>
</div>
<!--end::App Content-->
