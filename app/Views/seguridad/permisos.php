<?php partial('page_header', [
    'titulo'    => 'Matriz de Permisos',
    'icono'     => 'bi-key',
    'subtitulo' => 'Configura el acceso granular por módulo para cada perfil',
    'acciones'  => [
        ['texto' => 'Volver a Roles', 'url' => 'seguridad/roles', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Selector de Rol -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3">
                        <label for="rolConfigurar" class="form-label mb-0">Seleccionar Rol a Configurar:</label>
                    </div>
                    <div class="col-12 col-md-5">
                        <select id="rolConfigurar" class="form-select" data-navegar="<?= e(url('seguridad/permisos?rol_id=')) ?>">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($r['id'] == $rolSeleccionadoId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($esAdmin): ?>
            <div class="alert alert-info d-flex gap-2 align-items-start">
                <i class="bi bi-shield-check fs-5" aria-hidden="true"></i>
                <div>El <strong>Administrador</strong> tiene siempre todos los permisos y no se pueden quitar: así el sistema nunca queda sin alguien que lo administre.</div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('seguridad/guardarPermisos') ?>">
            <input type="hidden" name="rol_id" value="<?= $rolSeleccionadoId ?>">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Módulos y Acciones Permitidas</h5>
                    <button type="submit" class="btn btn-primary btn-sm" <?= $esAdmin ? 'hidden' : '' ?>>
                        <i class="bi bi-save me-1"></i> Guardar Permisos
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Módulo Funcional</th>
                                    <th class="text-center col-w-md">Ver / Listar</th>
                                    <th class="text-center col-w-md">Crear / Registrar</th>
                                    <th class="text-center col-w-md">Modificar / Editar</th>
                                    <th class="text-center col-w-md">Eliminar / Anular</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modulos as $key => $nombreModulo): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-body-emphasis"><?= htmlspecialchars($nombreModulo) ?></strong>
                                            <small class="text-muted d-block">Identificador: <code><?= htmlspecialchars($key) ?></code></small>
                                        </td>
                                        <?php foreach (['ver' => 'Ver', 'crear' => 'Crear', 'editar' => 'Modificar', 'eliminar' => 'Eliminar'] as $accion => $texto): ?>
                                            <td class="text-center">
                                                <div class="form-check d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="permisos[<?= e($key) ?>][]" value="<?= $accion ?>" aria-label="<?= $texto ?>: <?= e($nombreModulo) ?>"
                                                        <?= ($esAdmin || isset($efectivos['todos']) || isset($efectivos[$key . '.' . $accion])) ? 'checked' : '' ?> <?= $esAdmin ? 'disabled' : '' ?>>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end" <?= $esAdmin ? 'hidden' : '' ?>>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar Configuración de Permisos
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
