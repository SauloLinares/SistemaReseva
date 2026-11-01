<?php partial('page_header', [
    'titulo'    => 'Cambiar contraseña',
    'icono'     => 'bi-key',
    'subtitulo' => $obligatorio ? 'Crea tu propia contraseña para empezar a usar el sistema' : 'Elige una contraseña que solo tú conozcas',
    'migas'     => $obligatorio ? ['Cambiar contraseña' => null] : ['Mi perfil' => 'perfil', 'Cambiar contraseña' => null],
]) ?>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-9 col-lg-6">
                <?php if ($obligatorio): ?>
                    <div class="alert alert-warning d-flex gap-2" role="alert">
                        <i class="bi bi-shield-exclamation fs-5" aria-hidden="true"></i>
                        <div>
                            Tu contraseña actual te la asignó el administrador (o es la de demostración).
                            Por seguridad, crea una propia antes de continuar.
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= url('perfil/guardarClave') ?>" method="POST" class="card" id="formClave" data-minimo="<?= (int) $minimo ?>">
                    <?php partial('card_header', ['titulo' => 'Nueva contraseña', 'icono' => 'bi-shield-lock']) ?>

                    <div class="card-body">
                        <!-- Para que el gestor de contraseñas del navegador sepa de qué cuenta es -->
                        <input type="email" name="usuario_email" value="<?= e(auth_user('email') ?? '') ?>" autocomplete="username" hidden readonly>

                        <div class="mb-3">
                            <label for="clave_actual" class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="clave_actual" name="clave_actual" class="form-control" autocomplete="current-password" required>
                                <button type="button" class="btn btn-outline-secondary" data-ver-clave="clave_actual" aria-label="Mostrar contraseña actual"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="clave_nueva" class="form-label">Contraseña nueva <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="clave_nueva" name="clave_nueva" class="form-control" autocomplete="new-password" minlength="<?= (int) $minimo ?>" aria-describedby="reglasClave" required>
                                <button type="button" class="btn btn-outline-secondary" data-ver-clave="clave_nueva" aria-label="Mostrar contraseña nueva"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <ul class="reglas-clave list-unstyled small mb-3" id="reglasClave">
                            <li data-regla="largo"><i class="bi bi-circle" aria-hidden="true"></i> Al menos <?= (int) $minimo ?> caracteres</li>
                            <li data-regla="mezcla"><i class="bi bi-circle" aria-hidden="true"></i> Letras y números</li>
                            <li data-regla="distinta"><i class="bi bi-circle" aria-hidden="true"></i> Distinta de la actual</li>
                        </ul>

                        <div class="mb-1">
                            <label for="clave_confirmacion" class="form-label">Repite la contraseña nueva <span class="text-danger">*</span></label>
                            <input type="password" id="clave_confirmacion" name="clave_confirmacion" class="form-control" autocomplete="new-password" required>
                            <div class="form-text" id="avisoCoincide" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end gap-2">
                        <?php if ($obligatorio): ?>
                            <button type="submit" form="formCerrarSesion" class="btn btn-outline-secondary me-auto" formnovalidate>
                                <i class="bi bi-box-arrow-right me-1"></i> Salir
                            </button>
                        <?php else: ?>
                            <a href="<?= url('perfil') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Guardar contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= script_js('vistas/perfil-clave.js') ?>
