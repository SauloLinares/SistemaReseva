// JavaScript de la vista seguridad/roles.php (antes estaba inline; la CSP no lo permite).

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

// Botones "Editar" de la tabla: el registro viene en data-editar (JSON).
// Delegado en el documento porque DataTables reconstruye las filas al paginar o buscar.
document.addEventListener('click', function (e) {
    const boton = e.target.closest('[data-editar]');
    if (boton) {
        editarRol(JSON.parse(boton.dataset.editar));
    }
});
