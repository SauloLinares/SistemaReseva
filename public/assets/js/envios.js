// Envíos seguros: que un cobro no se registre dos veces y que el usuario sepa cuándo
// el servidor no responde. Se carga en todas las páginas del sistema, antes de custom.js.
(function () {
    'use strict';

    // -----------------------------------------------------------------
    // 1. CLAVE ÚNICA POR OPERACIÓN
    // Cada envío POST lleva una clave (_operacion o cabecera X-Operacion). Si el mismo
    // contenido se vuelve a enviar sin que el servidor haya confirmado el anterior (doble
    // clic, corte de red, F5), se reutiliza la clave y el servidor no lo registra de nuevo
    // (app/Core/Operacion.php). Un envío confirmado se olvida: repetirlo a propósito es
    // otra operación con otra clave.
    // -----------------------------------------------------------------
    const ALMACEN = 'operaciones-pendientes';
    const VIGENCIA_MS = 15 * 60 * 1000;

    const leer = function () {
        try { return JSON.parse(sessionStorage.getItem(ALMACEN) || '{}'); } catch (e) { return {}; }
    };
    const guardar = function (pendientes) {
        try { sessionStorage.setItem(ALMACEN, JSON.stringify(pendientes)); } catch (e) { /* modo privado: sin memoria */ }
    };

    // crypto.randomUUID() solo existe en HTTPS; getRandomValues() funciona también en http://
    const nuevaClave = function () {
        const b = crypto.getRandomValues(new Uint8Array(16));
        b[6] = (b[6] & 0x0f) | 0x40;
        b[8] = (b[8] & 0x3f) | 0x80;
        const h = [...b].map(x => x.toString(16).padStart(2, '0')).join('');
        return h.slice(0, 8) + '-' + h.slice(8, 12) + '-' + h.slice(12, 16) + '-' + h.slice(16, 20) + '-' + h.slice(20);
    };

    // Huella del contenido: mismo destino y mismos datos = mismo intento
    const huella = function (destino, entradas) {
        const datos = [];
        for (const [k, v] of entradas) {
            if (k === '_csrf' || k === '_operacion') continue;
            datos.push([k, v instanceof File ? 'archivo:' + v.name + ':' + v.size : String(v)]);
        }
        return destino + '|' + JSON.stringify(datos);
    };

    const claveParaIntento = function (destino, entradas) {
        const h = huella(destino, entradas);
        const pendientes = leer();
        const ahora = Date.now();
        let elegida = null;
        Object.keys(pendientes).forEach(function (clave) {
            if (ahora - pendientes[clave].t > VIGENCIA_MS) delete pendientes[clave];
            else if (pendientes[clave].h === h) elegida = clave;
        });
        if (!elegida) {
            elegida = nuevaClave();
            pendientes[elegida] = { h: h, t: ahora };
        }
        guardar(pendientes);
        return elegida;
    };

    const olvidar = function (claves) {
        const pendientes = leer();
        claves.forEach(c => delete pendientes[c]);
        guardar(pendientes);
    };

    // Lo que el servidor confirmó (footer.php). Solo cuenta si esta página es la respuesta
    // del envío (llegó redirigida): si la respuesta se perdió en un corte y el usuario abrió
    // la página por su cuenta, la clave se conserva y reenviar lo mismo no lo duplica.
    window.envioConfirmado = function () {
        const nav = performance.getEntriesByType('navigation')[0];
        return nav && nav.redirectCount > 0 ? (window.leerJson('operaciones-completadas') || []) : [];
    };
    olvidar(window.envioConfirmado());

    // Formularios POST: la clave se pone justo antes de enviarse (fase de captura, antes que el resto)
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.method.toLowerCase() !== 'post' || form.hasAttribute('data-sin-operacion')) return;

        if (navigator.onLine === false) {
            e.preventDefault();
            e.stopImmediatePropagation();
            conexion.mostrar();
            window.avisar?.('Sin conexión', 'No hay conexión a internet: el formulario no se envió. Lo que escribiste sigue aquí.', 'error');
            return;
        }

        let campo = form.querySelector('input[name="_operacion"]');
        if (campo) campo.remove(); // la huella no debe incluir la clave anterior
        campo = document.createElement('input');
        campo.type = 'hidden';
        campo.name = '_operacion';
        campo.value = claveParaIntento(form.action, new FormData(form, e.submitter || undefined));
        form.appendChild(campo);
    }, true);

    // -----------------------------------------------------------------
    // 2. AVISO DE CONEXIÓN CAÍDA
    // -----------------------------------------------------------------
    const conexion = (function () {
        let barra = null;

        const crear = function () {
            if (barra) return barra;
            barra = document.createElement('div');
            barra.className = 'barra-sin-conexion';
            barra.setAttribute('role', 'alert');
            barra.hidden = true;
            barra.innerHTML = '<i class="bi bi-wifi-off" aria-hidden="true"></i>'
                + '<span>Sin conexión con el servidor · lo que registres ahora no se guardará</span>'
                + '<button type="button" class="btn btn-sm btn-light">Reintentar</button>';
            barra.querySelector('button').addEventListener('click', function () {
                const boton = this;
                boton.disabled = true;
                boton.textContent = 'Probando…';
                probar().finally(function () {
                    boton.disabled = false;
                    boton.textContent = 'Reintentar';
                });
            });
            document.body.prepend(barra);
            return barra;
        };

        // Pide un archivo pequeño del propio sistema: si responde, el servidor está en pie
        const probar = function () {
            return fetchOriginal(window.assetUrl('assets/js/nucleo.js'), { method: 'HEAD', cache: 'no-store' })
                .then(r => { if (r.ok) ocultar(); else mostrar(); })
                .catch(mostrar);
        };
        const mostrar = function () { crear().hidden = false; };
        const ocultar = function () { if (barra) barra.hidden = true; };

        window.addEventListener('offline', mostrar);
        window.addEventListener('online', probar);
        return { mostrar: mostrar, ocultar: ocultar, probar: probar };
    })();
    window.conexion = conexion;

    // -----------------------------------------------------------------
    // 2.1 BARRA FINA DE PROGRESO (arriba de la pantalla)
    // Mientras hay peticiones fetch en curso o la página está cargando otra. Aparece solo
    // si la espera pasa de 150 ms: en lo instantáneo no parpadea.
    // -----------------------------------------------------------------
    const progreso = (function () {
        let barra = null;
        let activas = 0;
        let espera = null;
        let avance = null;

        const crear = function () {
            if (barra) return barra;
            barra = document.createElement('div');
            barra.className = 'barra-progreso';
            barra.setAttribute('role', 'progressbar');
            barra.setAttribute('aria-label', 'Cargando');
            barra.hidden = true;
            document.body.appendChild(barra);
            return barra;
        };
        const mostrar = function () {
            const b = crear();
            b.classList.remove('terminando');
            b.style.transform = 'scaleX(0.08)';
            b.hidden = false;
            let ancho = 0.08;
            clearInterval(avance);
            // Avanza cada vez más lento sin llegar al final: no sabemos cuánto falta
            avance = setInterval(function () {
                ancho += (0.9 - ancho) * 0.08;
                b.style.transform = 'scaleX(' + ancho.toFixed(3) + ')';
            }, 200);
        };
        return {
            iniciar: function () {
                activas++;
                if (activas === 1) espera = setTimeout(mostrar, 150);
            },
            terminar: function () {
                activas = Math.max(0, activas - 1);
                if (activas > 0) return;
                clearTimeout(espera);
                clearInterval(avance);
                if (!barra || barra.hidden) return;
                barra.classList.add('terminando');
                barra.style.transform = 'scaleX(1)';
                setTimeout(function () { barra.hidden = true; barra.classList.remove('terminando'); }, 350);
            },
        };
    })();
    window.progreso = progreso;

    // Al ir a otra página (enlace o formulario): la barra avanza hasta que llega la nueva.
    // Si la navegación no ocurre (ej. una descarga), se apaga sola.
    window.addEventListener('beforeunload', function () {
        progreso.iniciar();
        setTimeout(progreso.terminar, 10000);
    });
    // Al volver con "Atrás" la página sale de la caché con la barra a medias
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) progreso.terminar();
    });

    // -----------------------------------------------------------------
    // 3. fetch(): clave de operación, conexión y sesión vencida en un solo lugar
    // -----------------------------------------------------------------
    const fetchOriginal = window.fetch.bind(window);
    const rutaLogin = function (url) {
        try { return new URL(url).searchParams.get('url') === 'login'; } catch (e) { return false; }
    };

    window.fetch = function (recurso, opciones) {
        opciones = opciones || {};
        const metodo = (opciones.method || (recurso instanceof Request ? recurso.method : 'GET')).toUpperCase();
        const destino = new URL(recurso instanceof Request ? recurso.url : recurso, window.location.href);
        const propio = destino.origin === window.location.origin;
        let clave = null;

        if (propio && metodo !== 'GET' && metodo !== 'HEAD') {
            const cuerpo = opciones.body;
            const entradas = cuerpo instanceof FormData || cuerpo instanceof URLSearchParams
                ? [...cuerpo.entries()]
                : [['cuerpo', cuerpo == null ? '' : String(cuerpo)]];
            clave = claveParaIntento(destino.href, entradas);
            const cabeceras = new Headers(opciones.headers || undefined);
            cabeceras.set('X-Operacion', clave);
            opciones = Object.assign({}, opciones, { headers: cabeceras });
        }

        progreso.iniciar();
        return fetchOriginal(recurso, opciones).finally(progreso.terminar).then(function (respuesta) {
            conexion.ocultar();

            // La sesión venció: el servidor redirigió al login en lugar de responder
            if (propio && respuesta.redirected && rutaLogin(respuesta.url)) {
                sesionVencida();
                return new Promise(function () {}); // la página se va al login; nada más que hacer
            }
            // Un POST sin sesión lo rechaza el control CSRF (403) y avisa que la sesión venció
            if (propio && respuesta.status === 403) {
                return respuesta.clone().json().then(function (datos) {
                    if (datos && datos.sesion_vencida) {
                        sesionVencida();
                        return new Promise(function () {});
                    }
                    return respuesta;
                }, function () { return respuesta; });
            }
            if (clave && (respuesta.headers.get('Content-Type') || '').includes('application/json')) {
                respuesta.clone().json().then(function (datos) {
                    if (datos && datos.success === true) olvidar([clave]);
                }).catch(function () {});
            }
            return respuesta;
        }, function (error) {
            // Sin respuesta (red caída o servidor apagado). Si era un envío, reintentarlo
            // con el mismo contenido es seguro: viaja con la misma clave.
            if (error && error.name !== 'AbortError') conexion.mostrar();
            throw error;
        });
    };

    let avisandoSesion = false;
    function sesionVencida() {
        if (avisandoSesion) return;
        avisandoSesion = true;
        const irAlLogin = () => { window.location.href = window.appUrl('login'); };
        if (typeof Swal === 'undefined') {
            alert('Tu sesión se cerró. Vuelve a iniciar sesión.');
            irAlLogin();
            return;
        }
        Swal.fire({
            icon: 'info',
            title: 'Tu sesión se cerró',
            text: 'Por seguridad, la sesión vence tras un tiempo sin uso. Inicia sesión otra vez para continuar.',
            confirmButtonText: 'Iniciar sesión',
            confirmButtonColor: '#2563eb',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(irAlLogin);
    }
})();
