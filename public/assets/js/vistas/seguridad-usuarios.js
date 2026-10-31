// JavaScript de la vista seguridad/usuarios.php (antes estaba inline; la CSP no lo permite).

function editarUsuario(u) {
    document.getElementById('modalUserTitle').textContent = 'Editar Usuario: ' + u.nombre;
    document.getElementById('userId').value = u.id;
    document.getElementById('userNombre').value = u.nombre;
    document.getElementById('userEmail').value = u.email;
    document.getElementById('userRol').value = u.rol || 'Administrador';
    document.getElementById('userEstado').value = u.estado;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false; // al editar, vacía = no cambiarla
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

document.getElementById('modalUsuario').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalUserTitle').textContent = 'Nuevo Usuario';
    document.getElementById('userId').value = '';
    document.getElementById('userNombre').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = true;
});

// Botones "Editar" de la tabla: el registro viene en data-editar (JSON).
// Delegado en el documento porque DataTables reconstruye las filas al paginar o buscar.
document.addEventListener('click', function (e) {
    const boton = e.target.closest('[data-editar]');
    if (boton) {
        editarUsuario(JSON.parse(boton.dataset.editar));
    }
});
