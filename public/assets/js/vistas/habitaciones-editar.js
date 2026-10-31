// JavaScript de la vista habitaciones/editar.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('inputFotoHabEditar');
    const previewFoto = document.getElementById('previewFotoHabEditar');

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
