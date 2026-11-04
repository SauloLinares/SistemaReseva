// JavaScript de la vista configuracion/hotel.php (antes estaba inline; la CSP no lo permite).
// Vista previa del logo elegido y botón "Quitar logo" (se aplica al guardar el formulario).

document.addEventListener('DOMContentLoaded', function() {
    const inputLogo = document.getElementById('inputLogoHotel');
    const previewLogo = document.getElementById('previewLogoHotel');
    const vacio = document.getElementById('previewLogoVacio');
    const btnQuitar = document.getElementById('btnQuitarLogo');
    const quitar = document.getElementById('quitarLogo');
    const aviso = document.getElementById('avisoQuitarLogo');

    if (!inputLogo || !previewLogo) return;

    function mostrarLogo(src) {
        previewLogo.src = src;
        previewLogo.hidden = false;
        vacio?.setAttribute('hidden', '');
    }

    inputLogo.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(evt) {
            mostrarLogo(evt.target.result);
            // Un logo nuevo reemplaza al anterior: ya no se trata de quitarlo
            if (quitar) quitar.value = '0';
            if (aviso) aviso.hidden = true;
            if (btnQuitar) btnQuitar.hidden = false;
        };
        reader.readAsDataURL(file);
    });

    if (btnQuitar && quitar) {
        btnQuitar.addEventListener('click', function() {
            inputLogo.value = '';
            previewLogo.hidden = true;
            previewLogo.removeAttribute('src');
            vacio?.removeAttribute('hidden');
            // Solo hace falta avisar si el hotel ya tenía un logo guardado
            const habiaGuardado = btnQuitar.dataset.guardado !== '0';
            quitar.value = habiaGuardado ? '1' : '0';
            if (aviso) aviso.hidden = !habiaGuardado;
            btnQuitar.hidden = true;
        });
    }
});
