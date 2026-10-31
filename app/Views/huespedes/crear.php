<?php partial('page_header', [
    'titulo'    => 'Registrar Nuevo Huésped',
    'icono'     => 'bi-person-plus',
    'subtitulo' => 'Ingresa la información personal y datos de contacto del cliente',
    'migas'     => ['Huéspedes' => 'huespedes', 'Nuevo' => null],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 col-lg-8">
                <form action="<?= url('huespedes/guardar') ?>" method="POST" class="card">
                    <?php partial('card_header', ['titulo' => 'Ficha del Huésped', 'icono' => 'bi-person-vcard']) ?>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="tipo_documento" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                                <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                                    <option value="DNI">DNI (Perú)</option>
                                    <option value="Pasaporte">Pasaporte (Extranjero)</option>
                                    <option value="Carnet Extranjería">Carnet de Extranjería</option>
                                    <option value="Otro">Otro Documento</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="numero_documento" class="form-label">Número de Documento <span class="text-danger">*</span></label>
                                <input type="text" id="numero_documento" name="numero_documento" class="form-control" placeholder="Ej: 71829341 o PAS902188" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Ej: Carlos Alberto" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" id="apellidos" name="apellidos" class="form-control" placeholder="Ej: Mendoza Quispe" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" id="correo" name="correo" class="form-control" placeholder="cliente@correo.com">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="telefono" class="form-label">Teléfono / Celular</label>
                                <input type="text" id="telefono" name="telefono" class="form-control" placeholder="+51 987 654 321">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                <input type="text" id="nacionalidad" name="nacionalidad" class="form-control" value="Perú" placeholder="Ej: Perú, España, Colombia...">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select id="estado" name="estado" class="form-select">
                                    <option value="1" selected>Activo (Habilitado para reservar)</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección de Domicilio / Ciudad</label>
                                <input type="text" id="direccion" name="direccion" class="form-control" placeholder="Av. Principal 123, Ciudad">
                            </div>

                            <div class="col-12">
                                <label for="notas" class="form-label">Notas o Preferencias Especiales</label>
                                <textarea id="notas" name="notas" class="form-control" rows="2" placeholder="Ej: Habitación en piso bajo, vegetariano, cliente frecuente VIP..."></textarea>
                            </div>
                        </div>
                    </div>

                    <?php partial('form_actions', ['cancelar' => 'huespedes', 'texto' => 'Guardar Huésped']) ?>
                </form>
            </div>
        </div>

    </div>
</div>
