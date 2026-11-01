// JavaScript de la vista habitaciones/tarifas.php: editar varias tarifas y guardarlas juntas.
// Cada fila muestra cuánto se aparta del precio base de su tipo; las cambiadas se resaltan y
// una barra fija resume los cambios. El servidor vuelve a validar (guardarTarifas).
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formTarifas');
    if (!form) return;

    const campos = [...form.querySelectorAll('input[name^="tarifas["]')];
    const barra = form.querySelector('[data-barra-cambios]');
    const resumen = form.querySelector('[data-resumen-cambios]');
    const soles = n => 'S/. ' + n.toFixed(2);
    const num = v => parseFloat(String(v).replace(',', '.'));
    // Por debajo de este porcentaje del precio base se pide confirmación al guardar
    const MINIMO_RAZONABLE = 0.7;

    const cambiado = c => Math.abs((num(c.value) || 0) - num(c.dataset.original)) >= 0.005;

    function pintarFila(campo) {
        const fila = campo.closest('[data-tarifa-fila]');
        const valor = num(campo.value);
        const base = num(campo.dataset.base);
        const dif = fila.querySelector('[data-diferencia]');

        if (!(valor > 0)) {
            dif.textContent = 'Precio no válido';
            dif.className = 'tarifa-dif tarifa-dif-error';
        } else if (!(base > 0)) {
            dif.textContent = 'El tipo no tiene precio base';
            dif.className = 'tarifa-dif';
        } else {
            const diferencia = valor - base;
            const pct = (diferencia / base) * 100;
            if (Math.abs(diferencia) < 0.005) {
                dif.textContent = 'Igual al precio base';
                dif.className = 'tarifa-dif tarifa-dif-igual';
            } else {
                const signo = diferencia > 0 ? '+' : '−';
                dif.textContent = signo + soles(Math.abs(diferencia)) + ' (' + signo + Math.abs(pct).toFixed(1) + ' %)';
                dif.className = 'tarifa-dif ' + (diferencia > 0 ? 'tarifa-dif-arriba' : 'tarifa-dif-abajo');
            }
        }

        const esCambio = cambiado(campo);
        fila.classList.toggle('tarifa-cambiada', esCambio);
        fila.querySelector('[data-deshacer]').hidden = !esCambio;
    }

    function actualizarBarra() {
        const cambios = campos.filter(cambiado).length;
        barra.hidden = cambios === 0;
        resumen.textContent = cambios === 1 ? '1 tarifa cambiada' : cambios + ' tarifas cambiadas';
    }

    const refrescar = function (campo) {
        pintarFila(campo);
        actualizarBarra();
    };

    campos.forEach(pintarFila);
    actualizarBarra();
    form.addEventListener('input', function (e) {
        if (campos.includes(e.target)) refrescar(e.target);
    });

    // Deshacer una fila / descartar todo
    form.addEventListener('click', function (e) {
        const deshacer = e.target.closest('[data-deshacer]');
        if (deshacer) {
            const campo = deshacer.closest('[data-tarifa-fila]').querySelector('input');
            campo.value = campo.dataset.original;
            refrescar(campo);
            campo.focus();
        }
        if (e.target.closest('[data-descartar]')) {
            campos.forEach(c => { c.value = c.dataset.original; pintarFila(c); });
            actualizarBarra();
        }

        // Acciones de un tipo: solo sobre sus filas visibles (respetan la búsqueda y el piso)
        const grupo = e.target.closest('[data-grupo]');
        if (!grupo) return;
        const visibles = [...grupo.querySelectorAll('[data-tarifa-fila]')].filter(f => !f.hidden).map(f => f.querySelector('input'));

        const igualar = e.target.closest('[data-aplicar-base]');
        if (igualar) {
            visibles.forEach(c => { c.value = num(igualar.dataset.aplicarBase).toFixed(2); pintarFila(c); });
            actualizarBarra();
        }
        if (e.target.closest('[data-aplicar-porcentaje]')) {
            const entrada = grupo.querySelector('[data-porcentaje]');
            const pct = num(entrada.value);
            if (!pct || pct <= -90 || pct > 300) {
                window.avisar('Porcentaje no válido', 'Escribe cuánto subir (ej. 10) o bajar (ej. -15) las tarifas de este tipo.');
                entrada.focus();
                return;
            }
            // Sobre la tarifa que había al abrir la pantalla: aplicar dos veces no acumula
            visibles.forEach(c => { c.value = (Math.round(num(c.dataset.original) * (1 + pct / 100) * 100) / 100).toFixed(2); pintarFila(c); });
            actualizarBarra();
        }
    });

    // Buscar por número y filtrar por piso
    const buscar = document.getElementById('tarifasBuscar');
    const piso = document.getElementById('tarifasPiso');
    const sinResultados = form.querySelector('[data-sin-resultados]');
    function filtrar() {
        const texto = buscar.value.trim().toLowerCase();
        let total = 0;
        form.querySelectorAll('[data-grupo]').forEach(function (grupo) {
            let enGrupo = 0;
            grupo.querySelectorAll('[data-tarifa-fila]').forEach(function (fila) {
                const ok = (!texto || fila.dataset.numero.toLowerCase().includes(texto)) && (!piso.value || fila.dataset.piso === piso.value);
                fila.hidden = !ok;
                if (ok) enGrupo++;
            });
            grupo.hidden = enGrupo === 0;
            total += enGrupo;
        });
        sinResultados.hidden = total > 0;
    }
    buscar.addEventListener('input', filtrar);
    piso.addEventListener('change', filtrar);

    // Guardar: se envían solo las tarifas cambiadas; confirmar si alguna queda muy por debajo del base
    let confirmado = false;
    let guardando = false;
    form.addEventListener('submit', function (e) {
        const cambiados = campos.filter(cambiado);
        const bajos = cambiados.filter(c => num(c.dataset.base) > 0 && num(c.value) < num(c.dataset.base) * MINIMO_RAZONABLE);
        if (bajos.length && !confirmado) {
            e.preventDefault();
            const lista = bajos.map(c => c.closest('[data-tarifa-fila]').dataset.numero).join(', ');
            Swal.fire({
                icon: 'warning',
                title: '¿Precios tan bajos?',
                text: 'Las habitaciones ' + lista + ' quedarían a menos del ' + Math.round(MINIMO_RAZONABLE * 100) + ' % del precio base de su tipo.',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Revisar',
                confirmButtonColor: '#2563eb',
            }).then(function (r) {
                if (!r.isConfirmed) return;
                confirmado = true;
                form.requestSubmit();
            });
            return;
        }
        campos.forEach(c => { c.disabled = !cambiado(c); });
        const boton = form.querySelector('[type="submit"]');
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Guardando…';
        guardando = true;
    });

    // Avisar si se sale con cambios sin guardar
    window.addEventListener('beforeunload', function (e) {
        if (!guardando && campos.some(cambiado)) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    // Al volver con "Atrás" tras guardar, los campos no deben quedar deshabilitados
    window.addEventListener('pageshow', function () {
        campos.forEach(c => { c.disabled = false; });
    });
});
