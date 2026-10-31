<?php partial('page_header', [
    'titulo'    => 'Roles de Usuario',
    'icono'     => 'bi-shield-check',
    'subtitulo' => 'Perfiles de acceso y responsabilidades en el sistema',
    'acciones'  => [
        ['texto' => 'Nuevo Rol', 'modal' => '#modalRol', 'icono' => 'bi-plus-circle'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-4">
            <?php foreach ($roles as $r): ?>
                <div class="col-12 col-md-4">
                    <div class="card h-100 border-top border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($r['nombre']) ?></h5>
                                <span class="badge text-bg-light border"><?= $r['total_usuarios'] ?> usuarios</span>
                            </div>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($r['descripcion'] ?: 'Sin descripción') ?></p>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <a href="<?= url('seguridad/permisos?rol_id=' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-key me-1"></i> Matriz de Permisos
                                </a>
                                <button type="button" class="btn btn-icon btn-outline-primary" onclick='editarRol(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<!-- Modal Rol -->
<div class="modal fade" id="modalRol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('seguridad/guardarRol') ?>" class="modal-content">
            <input type="hidden" name="id" id="rolId">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRolTitle"><i class="bi bi-shield-plus me-2"></i>Rol de Acceso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="rolNombre" class="form-control" placeholder="Ej: Auditor, Cajero, Recepcionista" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" id="rolDescripcion" class="form-control" rows="3" placeholder="Funciones y alcance del rol..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-sm btn-primary">Guardar Rol</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarRol(r) {
    document.getElementById('modalRolTitle').textContent = 'Editar Rol: ' + r.nombre;
    document.getElementById('rolId').value = r.id;
    document.getElementById('rolNombre').value = r.nombre;
    document.getElementById('rolDescripcion').value = r.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalRol')).show();
}

document.getElementById('modalRol').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalRolTitle').textContent = 'Nuevo Rol';
    document.getElementById('rolId').value = '';
    document.getElementById('rolNombre').value = '';
    document.getElementById('rolDescripcion').value = '';
});
</script>
