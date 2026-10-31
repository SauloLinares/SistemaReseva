// JavaScript de la vista habitaciones/tipos.php (antes estaba inline; la CSP no lo permite).

function editarTipo(t) {
    document.getElementById('modalTipoTitle').textContent = 'Editar: ' + t.nombre;
    document.getElementById('tipoId').value = t.id;
    document.getElementById('tipoNombre').value = t.nombre;
    document.getElementById('tipoCapacidad').value = t.capacidad;
    document.getElementById('tipoPrecioBase').value = t.precio_base;
    document.getElementById('tipoCaracteristicas').value = t.caracteristicas || '';
    document.getElementById('tipoDescripcion').value = t.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalTipo')).show();
}

document.getElementById('modalTipo').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTipoTitle').textContent = 'Nuevo Tipo de Habitación';
    document.getElementById('tipoId').value = '';
    document.getElementById('tipoNombre').value = '';
    document.getElementById('tipoCapacidad').value = '1';
    document.getElementById('tipoPrecioBase').value = '0.00';
    document.getElementById('tipoCaracteristicas').value = '';
    document.getElementById('tipoDescripcion').value = '';
});

// Botones "Editar" de la tabla: el registro viene en data-editar (JSON).
// Delegado en el documento porque DataTables reconstruye las filas al paginar o buscar.
document.addEventListener('click', function (e) {
    const boton = e.target.closest('[data-editar]');
    if (boton) {
        editarTipo(JSON.parse(boton.dataset.editar));
    }
});
