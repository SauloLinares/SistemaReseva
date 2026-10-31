<?php partial('page_header', [
    'titulo'    => 'Mi perfil',
    'icono'     => 'bi-person-circle',
    'subtitulo' => 'Tus datos de contacto y tu foto. El rol y el estado de tu cuenta los asigna el administrador.',
    'migas'     => ['Mi perfil' => null],
]) ?>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <form action="<?= url('perfil/guardar') ?>" method="POST" enctype="multipart/form-data" class="card">
                    <?php partial('card_header', ['titulo' => 'Mis datos', 'icono' => 'bi-person-vcard']) ?>

                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div id="vistaFoto"><?= avatar_usuario($usuario['foto'] ?? null, $usuario['nombre'] ?? '', 'avatar-xl') ?></div>
                            <div class="flex-grow-1">
                                <label for="foto" class="form-label">Foto</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/png, image/jpeg, image/webp" aria-describedby="ayudaFoto">
                                <div id="ayudaFoto" class="form-text">PNG, JPG o WEBP de hasta 2 MB. Se ve en la cabecera del sistema.</div>
                                <?php if (!empty($usuario['foto'])): ?>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="quitar_foto" value="1" id="quitar_foto">
                                        <label class="form-check-label" for="quitar_foto">Quitar mi foto (se mostrarán mis iniciales)</label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="nombre" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" id="nombre" name="nombre" class="form-control" value="<?= e($usuario['nombre'] ?? '') ?>" autocomplete="name" required>
                            </div>
                            <div class="col-12 col-md-7">
                                <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= e($usuario['email'] ?? '') ?>" autocomplete="email" aria-describedby="ayudaEmail" required>
                                <div id="ayudaEmail" class="form-text">Es tu usuario para ingresar al sistema.</div>
                            </div>
                            <div class="col-12 col-md-5">
                                <label for="telefono" class="form-label">Teléfono / Celular</label>
                                <input type="tel" id="telefono" name="telefono" class="form-control" value="<?= e($usuario['telefono'] ?? '') ?>" inputmode="tel" autocomplete="tel" placeholder="+51 987 654 321">
                            </div>
                        </div>
                    </div>

                    <?php partial('form_actions', ['cancelar' => '', 'texto' => 'Guardar mis datos']) ?>
                </form>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <?php partial('card_header', ['titulo' => 'Mi cuenta', 'icono' => 'bi-shield-lock']) ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-body-secondary">Rol</span>
                            <span class="fw-semibold"><?= e($usuario['rol'] ?? '') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-body-secondary">Último ingreso</span>
                            <span><?= !empty($usuario['ultimo_login']) ? fecha_es($usuario['ultimo_login'], true) : '—' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-body-secondary">Contraseña cambiada</span>
                            <span><?= !empty($usuario['clave_cambiada_en']) ? fecha_es($usuario['clave_cambiada_en'], true) : 'Nunca' ?></span>
                        </li>
                    </ul>
                    <div class="card-footer">
                        <a href="<?= url('perfil/clave') ?>" class="btn btn-outline-primary w-100">
                            <i class="bi bi-key me-1"></i> Cambiar contraseña
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= script_js('vistas/perfil-index.js') ?>
