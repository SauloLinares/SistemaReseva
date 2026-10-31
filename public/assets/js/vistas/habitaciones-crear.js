// JavaScript de la vista habitaciones/crear.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo_id');
    const precioInput = document.getElementById('precio_diario');
    const inputFoto = document.getElementById('inputFotoHab');
    const previewFoto = document.getElementById('previewFotoHab');

    function actualizarPrecioBase() {
        const selected = tipoSelect.options[tipoSelect.selectedIndex];
        if (selected) {
            precioInput.value = selected.dataset.precio || '0.00';
        }
    }

    tipoSelect.addEventListener('change', actualizarPrecioBase);
    actualizarPrecioBase();

    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewFoto.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
