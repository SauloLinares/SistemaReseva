// Cobro con uno o varios métodos de pago (partials/cobro_lineas.php): agregar y quitar
// métodos, total, efectivo recibido y vuelto. El servidor vuelve a validar todo
// (PagoController::leerCobro).
(function () {
    'use strict';

    const MAX_LINEAS = 4;
    const soles = n => window.moneda + ' ' + (Math.round(n * 100) / 100).toFixed(2);
    const num = v => parseFloat(String(v).replace(',', '.')) || 0;

    function iniciar(cobro) {
        if (cobro.dataset.listo) return;
        cobro.dataset.listo = '1';
        const lista = cobro.querySelector('[data-lineas-lista]');
        const molde = lista.querySelector('[data-linea]').cloneNode(true);
        const efectivo = cobro.querySelector('[data-efectivo]');
        const recibido = cobro.querySelector('[data-recibido]');
        const vuelto = cobro.querySelector('[data-vuelto]');
        const total = cobro.querySelector('[data-total]');
        const agregar = cobro.querySelector('[data-agregar-linea]');

        const lineas = () => [...lista.querySelectorAll('[data-linea]')];

        // Monto objetivo (saldo): al dividir, la línea nueva propone lo que falta
        cobro.objetivo = num(lineas()[0].querySelector('[data-linea-monto]').value);

        function renumerar() {
            lineas().forEach(function (linea, i) {
                linea.querySelector('[data-linea-metodo]').name = 'lineas[' + i + '][metodo]';
                linea.querySelector('[data-linea-monto]').name = 'lineas[' + i + '][monto]';
                // La primera no se quita; su botón solo reserva el espacio para que las filas se alineen
                linea.querySelector('[data-quitar-linea]').classList.toggle('invisible', i === 0);
                linea.querySelector('[data-quitar-linea]').hidden = lineas().length === 1;
            });
            agregar.hidden = lineas().length >= MAX_LINEAS;
        }

        function recalcular() {
            let suma = 0;
            let enEfectivo = 0;
            lineas().forEach(function (linea) {
                const m = num(linea.querySelector('[data-linea-monto]').value);
                suma += m;
                if (linea.querySelector('[data-linea-metodo]').value === 'Efectivo') enEfectivo += m;
            });
            total.textContent = soles(suma);

            efectivo.hidden = enEfectivo <= 0;
            recibido.placeholder = enEfectivo > 0 ? enEfectivo.toFixed(2) : 'Opcional';
            if (recibido.value === '' || enEfectivo <= 0) {
                vuelto.textContent = '—';
                vuelto.className = 'fs-5 fw-bold';
            } else {
                const diferencia = num(recibido.value) - enEfectivo;
                vuelto.textContent = diferencia >= 0 ? soles(diferencia) : 'Falta ' + soles(-diferencia);
                vuelto.className = 'fs-5 fw-bold ' + (diferencia >= 0 ? 'text-success' : 'text-danger');
            }
        }

        function nuevaLinea(monto) {
            if (lineas().length >= MAX_LINEAS) return null;
            const linea = molde.cloneNode(true);
            const usados = lineas().map(l => l.querySelector('[data-linea-metodo]').value);
            const select = linea.querySelector('[data-linea-metodo]');
            const libre = [...select.options].find(o => !usados.includes(o.value));
            if (libre) select.value = libre.value;
            const input = linea.querySelector('[data-linea-monto]');
            input.removeAttribute('id');
            input.removeAttribute('data-atajo');
            input.value = monto > 0 ? monto.toFixed(2) : '';
            lista.appendChild(linea);
            renumerar();
            recalcular();
            return linea;
        }

        agregar.addEventListener('click', function () {
            const suma = lineas().reduce((t, l) => t + num(l.querySelector('[data-linea-monto]').value), 0);
            const linea = nuevaLinea(Math.max(0, cobro.objetivo - suma));
            linea?.querySelector('[data-linea-monto]').focus();
        });
        lista.addEventListener('click', function (e) {
            const quitar = e.target.closest('[data-quitar-linea]');
            if (!quitar) return;
            quitar.closest('[data-linea]').remove();
            renumerar();
            recalcular();
        });
        // Al escribir el monto de otro método, el primero se queda con lo que falta del saldo
        lista.addEventListener('input', function (e) {
            const linea = e.target.closest('[data-linea]');
            if (!e.target.matches('[data-linea-monto]') || !linea || linea === lineas()[0] || cobro.objetivo <= 0) return;
            const otros = lineas().slice(1).reduce((t, l) => t + num(l.querySelector('[data-linea-monto]').value), 0);
            const resto = Math.max(0, cobro.objetivo - otros);
            lineas()[0].querySelector('[data-linea-monto]').value = resto > 0 ? resto.toFixed(2) : '';
        });
        cobro.addEventListener('input', recalcular);
        cobro.addEventListener('change', recalcular);

        // Otro script cambia el saldo (ej. al elegir otra reserva): vuelve a una sola línea
        cobro.fijarMonto = function (monto) {
            cobro.objetivo = num(monto);
            lineas().slice(1).forEach(l => l.remove());
            lineas()[0].querySelector('[data-linea-monto]').value = cobro.objetivo > 0 ? cobro.objetivo.toFixed(2) : '';
            renumerar();
            recalcular();
        };
        // Para restaurar un envío con errores: crear las líneas que hagan falta
        cobro.asegurarLineas = function (cantidad) {
            while (lineas().length < Math.min(cantidad, MAX_LINEAS)) nuevaLinea(0);
        };

        renumerar();
        recalcular();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-cobro]').forEach(iniciar);

        // Si el servidor devolvió el formulario con errores, preparar tantas líneas como se enviaron
        // (custom.js rellena los valores después)
        const previo = window.leerJson('form-previo');
        const enviadas = previo && previo.datos && previo.datos.lineas ? Object.keys(previo.datos.lineas).length : 0;
        if (enviadas > 1) {
            document.querySelectorAll('[data-cobro]').forEach(c => c.asegurarLineas(enviadas));
        }
    });
})();
