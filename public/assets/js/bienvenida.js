// Bienvenida tras iniciar sesión (layouts/header.php, solo la primera pantalla): la barra avanza
// con pasos de texto durante unos segundos, para que se note que el sistema se está preparando,
// y luego la tarjeta se desvanece y deja ver el panel.
(function () {
    'use strict';

    const capa = document.getElementById('bienvenida');
    if (!capa) return;

    const DURACION_MS = 2800; // de 0 % a 100 %
    const PAUSA_FINAL_MS = 450; // "¡Listo!" antes de desvanecerse
    const PASOS = [
        [0, 'Verificando tus permisos…'],
        [30, 'Cargando los datos del hotel…'],
        [65, 'Preparando el panel de control…'],
        [100, '¡Listo!'],
    ];

    const avance = capa.querySelector('[data-bienvenida-avance]');
    const porcentaje = capa.querySelector('[data-bienvenida-porcentaje]');
    const paso = capa.querySelector('[data-bienvenida-paso]');
    document.body.classList.add('bienvenida-activa');

    // Avance parejo, apenas más lento al empezar y al terminar: se ve avanzar todo el tiempo
    const suavizar = t => (t + (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2)) / 2;
    const inicio = performance.now();

    function cuadro(ahora) {
        const t = Math.min(1, (ahora - inicio) / DURACION_MS);
        const pct = Math.round(suavizar(t) * 100);
        avance.style.width = pct + '%';
        porcentaje.textContent = pct;
        const texto = PASOS.filter(([desde]) => pct >= desde).pop()[1];
        if (paso.textContent !== texto) paso.textContent = texto;

        if (t < 1) {
            requestAnimationFrame(cuadro);
            return;
        }
        capa.classList.add('lista');
        setTimeout(function () {
            capa.classList.add('saliendo');
            // Solo el desvanecimiento de la capa (la tarjeta también tiene su transición)
            capa.addEventListener('transitionend', e => { if (e.target === capa) cerrar(); });
            setTimeout(cerrar, 600); // por si no hay transición (movimiento reducido)
        }, PAUSA_FINAL_MS);
    }

    function cerrar() {
        if (!capa.isConnected) return;
        capa.remove();
        document.body.classList.remove('bienvenida-activa');
    }

    requestAnimationFrame(cuadro);
})();
