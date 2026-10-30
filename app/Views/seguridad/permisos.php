<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-key text-warning me-2"></i>Matriz de Permisos</h3>
                <p class="text-muted mb-0 small">Configura el acceso granular por módulo para cada perfil</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('seguridad/roles') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Roles
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <!-- Selector de Rol -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold mb-0">Seleccionar Rol a Configurar:</label>
                    </div>
                    <div class="col-12 col-md-5">
                        <select class="form-select" onchange="window.location.href='<?= url("seguridad/permisos?rol_id=") ?>' + this.value">
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

            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>Módulos y Acciones Permitidas</h5>
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
                                    <th class="text-center" style="width: 140px;">Ver / Listar</th>
                                    <th class="text-center" style="width: 140px;">Crear / Registrar</th>
                                    <th class="text-center" style="width: 140px;">Modificar / Editar</th>
                                    <th class="text-center" style="width: 140px;">Eliminar / Anular</th>
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
                <div class="card-footer bg-body-tertiary text-end">
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="bi bi-save me-1"></i> Guardar Configuración de Permisos
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
