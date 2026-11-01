// Ayudantes que usan custom.js y los scripts de cada vista. Se carga en <head>,
// así está disponible antes que cualquier script de la página.
(function () {
    'use strict';

    // Lee un bloque <script type="application/json" id="..."> que deja el servidor
    window.leerJson = function (id) {
        const el = document.getElementById(id);
        if (!el) return null;
        try { return JSON.parse(el.textContent); } catch (e) { return null; }
    };

    // Equivalente de url() de PHP: appUrl('reservas/editar/3'), appUrl('pagos/crear?reserva_id=1')
    const config = window.leerJson('app-config') || {};

    // Símbolo de moneda de Configuración → Parámetros. Algunos montos se arman con innerHTML:
    // se quitan los caracteres que podrían formar HTML.
    window.moneda = String(config.moneda || '').replace(/[<>&"']/g, '') || 'S/.';

    window.appUrl = function (ruta) {
        ruta = String(ruta || '').replace(/^\/+/, '');
        if (ruta === '') return config.baseUrl || '';
        const [camino, consulta] = ruta.split('?');
        return (config.urlRutas || 'index.php?url=') + camino + (consulta ? '&' + consulta : '');
    };
    window.assetUrl = function (ruta) {
        return (config.baseUrl || '') + '/' + String(ruta).replace(/^\/+/, '');
    };
})();
