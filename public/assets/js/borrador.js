// Borradores: lo que se está llenando en un formulario <form data-borrador> se guarda en el
// navegador y se recupera al volver (otra pantalla, recarga, pestaña cerrada por error).
// Es por usuario: en un equipo compartido de recepción nadie ve el borrador de otro.
/* global reponerCampos -- definida en custom.js, que se carga después; se usa al terminar de cargar la página */
(function () {
    'use strict';

    const VIGENCIA_MS = 12 * 60 * 60 * 1000;
    const IGNORAR = /^(_csrf|_operacion)$|password|clave/i;
    const usuario = (window.leerJson('app-config') || {}).usuario || 0;

    const claveDe = function (form) {
        const ruta = new URL(form.action, window.location.href).searchParams.get('url') || form.action;
        return 'borrador:' + usuario + ':' + ruta;
    };
    const leer = function (clave) {
        try {
            const b = JSON.parse(localStorage.getItem(clave) || 'null');
            return b && Date.now() - b.t < VIGENCIA_MS ? b : null;
        } catch (e) { return null; }
    };
    const borrar = function (clave) {
        try { localStorage.removeItem(clave); } catch (e) { /* sin almacenamiento */ }
    };

    // Valores del formulario: { nombre: valor } o { nombre[]: [valores] }
    const capturar = function (form) {
        const datos = {};
        for (const [k, v] of new FormData(form)) {
            if (IGNORAR.test(k) || v instanceof File) continue;
            if (k.endsWith('[]')) (datos[k] = datos[k] || []).push(v);
            else datos[k] = v;
        }
        return datos;
    };

    const aviso = function (form, borrador, clave) {
        const minutos = Math.max(1, Math.round((Date.now() - borrador.t) / 60000));
        const hace = minutos < 60 ? 'hace ' + minutos + ' min' : 'hace ' + Math.round(minutos / 60) + ' h';
        const caja = document.createElement('div');
        caja.className = 'alert alert-info d-flex align-items-center gap-2 py-2 small aviso-borrador';
        caja.setAttribute('role', 'status');
        caja.innerHTML = '<i class="bi bi-clock-history fs-5" aria-hidden="true"></i>'
            + '<span class="flex-grow-1">Recuperamos lo que estabas registrando (' + hace + ').</span>'
            + '<button type="button" class="btn btn-sm btn-outline-secondary">Descartar</button>';
        caja.querySelector('button').addEventListener('click', function () {
            borrar(clave);
            window.location.reload();
        });
        form.prepend(caja);
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Después de los DOMContentLoaded de custom.js y las vistas (TomSelect, cálculos)
        setTimeout(function () {
            // Envíos confirmados por el servidor: sus borradores ya no hacen falta
            const completadas = window.envioConfirmado ? window.envioConfirmado() : [];
            if (completadas.length) {
                try {
                    Object.keys(localStorage).filter(k => k.startsWith('borrador:')).forEach(function (k) {
                        const b = leer(k);
                        if (!b || completadas.includes(b.operacion)) borrar(k);
                    });
                } catch (e) { /* sin almacenamiento */ }
            }

            const previo = window.leerJson('form-previo');
            document.querySelectorAll('form[data-borrador]').forEach(function (form) {
                const clave = claveDe(form);

                // Si el servidor devolvió el formulario con errores, sus datos son los más nuevos
                const ruta = new URL(form.action, window.location.href).searchParams.get('url');
                const borrador = previo && previo.accion === ruta ? null : leer(clave);
                if (borrador && typeof reponerCampos === 'function') {
                    reponerCampos(form, borrador.datos);
                    aviso(form, borrador, clave);
                }

                // Se guarda solo lo que cambia el usuario (no los valores iniciales de la página)
                let pendiente = null;
                const guardarAhora = function () {
                    try { localStorage.setItem(clave, JSON.stringify({ t: Date.now(), datos: capturar(form) })); } catch (e) { /* lleno o privado */ }
                };
                const programar = function (e) {
                    if (!e.isTrusted) return;
                    clearTimeout(pendiente);
                    pendiente = setTimeout(guardarAhora, 400);
                };
                form.addEventListener('input', programar);
                form.addEventListener('change', programar);
                window.addEventListener('pagehide', function () { if (pendiente) { clearTimeout(pendiente); guardarAhora(); } });

                // Al enviarse se recuerda con qué clave de operación: se borra cuando el servidor la confirme
                form.addEventListener('submit', function () {
                    clearTimeout(pendiente);
                    pendiente = null;
                    const op = form.querySelector('input[name="_operacion"]');
                    const b = leer(clave);
                    if (b && op) {
                        b.operacion = op.value;
                        try { localStorage.setItem(clave, JSON.stringify(b)); } catch (e) { /* sin almacenamiento */ }
                    }
                });
            });
        }, 0);
    });
})();
