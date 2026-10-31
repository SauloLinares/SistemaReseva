// JavaScript de la vista servicios/index.php (antes estaba inline; la CSP no lo permite).

function editarServicio(s) {
    document.getElementById('modalServTitle').textContent = 'Editar Servicio: ' + s.nombre;
    document.getElementById('servId').value = s.id;
    document.getElementById('servNombre').value = s.nombre;
    document.getElementById('servCategoria').value = s.categoria;
    document.getElementById('servPrecio').value = s.precio;
    document.getElementById('servEstado').value = s.estado;
    document.getElementById('servDescripcion').value = s.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalServicio')).show();
}

document.getElementById('modalServicio').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalServTitle').textContent = 'Nuevo Servicio';
    document.getElementById('servId').value = '';
    document.getElementById('servNombre').value = '';
    document.getElementById('servPrecio').value = '0.00';
    document.getElementById('servDescripcion').value = '';
});

// Botones "Editar" de la tabla: el registro viene en data-editar (JSON).
// Delegado en el documento porque DataTables reconstruye las filas al paginar o buscar.
document.addEventListener('click', function (e) {
    const boton = e.target.closest('[data-editar]');
    if (boton) {
        editarServicio(JSON.parse(boton.dataset.editar));
    }
});
