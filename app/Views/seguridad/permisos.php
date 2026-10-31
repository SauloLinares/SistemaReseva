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

        <?php
            // Mapear permisos existentes en array rápido
            $permisosMap = [];
            foreach ($permisos as $p) {
                $permisosMap[$p['modulo'] . '_' . $p['accion']] = true;
                if ($p['modulo'] === 'todos') {
                    $permisosMap['todos_todos'] = true;
                }
            }
            $esAdmin = ($rolSeleccionadoId === 1 || isset($permisosMap['todos_todos']));
        ?>

        <form method="POST" action="<?= url('seguridad/guardarPermisos') ?>">
            <input type="hidden" name="rol_id" value="<?= $rolSeleccionadoId ?>">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Módulos y Acciones Permitidas</h5>
                    <button type="submit" class="btn btn-primary btn-sm">
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
                                        <td class="text-center">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="permisos[<?= $key ?>][]" value="ver" <?= ($esAdmin || isset($permisosMap[$key . '_ver']) || isset($permisosMap[$key . '_gestionar'])) ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="permisos[<?= $key ?>][]" value="crear" <?= ($esAdmin || isset($permisosMap[$key . '_crear']) || isset($permisosMap[$key . '_gestionar'])) ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="permisos[<?= $key ?>][]" value="editar" <?= ($esAdmin || isset($permisosMap[$key . '_editar']) || isset($permisosMap[$key . '_gestionar'])) ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-inline-block">
                                                <input class="form-check-input" type="checkbox" name="permisos[<?= $key ?>][]" value="eliminar" <?= ($esAdmin || isset($permisosMap[$key . '_eliminar'])) ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar Configuración de Permisos
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
