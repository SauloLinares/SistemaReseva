// JavaScript de la vista configuracion/respaldo.php. La descarga no cambia de página: el
// botón queda "Preparando…" unos segundos para que no se pida dos veces mientras se arma.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formRespaldo');
    if (!form) return;
    const boton = form.querySelector('[type="submit"]');
    const aviso = form.querySelector('[data-aviso-respaldo]');
    const original = boton.innerHTML;

    form.addEventListener('submit', function () {
        // Se deshabilita después de que el navegador tome el envío
        setTimeout(function () {
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Preparando el respaldo…';
            aviso.textContent = 'La descarga empezará en unos segundos.';
        });
        setTimeout(function () {
            boton.disabled = false;
            boton.innerHTML = original;
            aviso.textContent = 'Si la descarga no empezó, vuelve a intentarlo.';
        }, 6000);
    });
});
