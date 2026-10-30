<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-person-fill-gear text-primary me-2"></i>Gestión de Usuarios</h3>
                <p class="text-muted mb-0 small">Administración de credenciales de acceso para el personal del hotel</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
                </button>
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

        <div class="card shadow-sm">
            <div class="card-header bg-body-tertiary">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-people me-2"></i>Usuarios Registrados</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre Completo</th>
                                <th>Correo Electrónico (Login)</th>
                                <th>Rol / Perfil</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th class="text-end pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge text-bg-primary"><?= htmlspecialchars($u['rol'] ?: 'Administrador') ?></span></td>
                                    <td>
                                        <?= $u['estado'] ? '<span class="badge text-bg-success">Activo</span>' : '<span class="badge text-bg-secondary">Inactivo</span>' ?>
                                    </td>
                                    <td><small class="text-muted"><?= fecha_es($u['ultimo_login'], true) ?></small></td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" onclick='editarUsuario(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <?php if ($u['id'] !== 1 && $u['id'] !== (int)auth_user('id')): ?>
                                                <a href="<?= url('seguridad/eliminarUsuario/' . $u['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('¿Eliminar usuario?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('seguridad/guardarUsuario') ?>" class="modal-content">
            <input type="hidden" name="id" id="userId">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalUserTitle"><i class="bi bi-person-gear me-2"></i>Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="userNombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="userEmail" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Contraseña</label>
                    <input type="password" name="password" id="userPassword" class="form-control" placeholder="Dejar en blanco si no se desea modificar">
                    <div class="form-text">Mínimo 6 caracteres con números y letras recomendados.</div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Rol Asignado</label>
                        <select name="rol" id="userRol" class="form-select">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= htmlspecialchars($r['nombre']) ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" id="userEstado" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarUsuario(u) {
    document.getElementById('modalUserTitle').textContent = 'Editar Usuario: ' + u.nombre;
    document.getElementById('userId').value = u.id;
    document.getElementById('userNombre').value = u.nombre;
    document.getElementById('userEmail').value = u.email;
    document.getElementById('userRol').value = u.rol || 'Administrador';
    document.getElementById('userEstado').value = u.estado;
    document.getElementById('userPassword').value = '';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalUserTitle').textContent = 'Nuevo Usuario';
    document.getElementById('userId').value = '';
    document.getElementById('userNombre').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPassword').value = '';
});
</script>
