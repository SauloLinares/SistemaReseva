/**
 * ====================================================================
 * Hotel Manager - Frontend JavaScript Suite
 * Suite de Experiencia de Usuario (UX/UI Moderna)
 * ====================================================================
 */

// ---------------------------------------------------------------
// 0. PROTECCIÓN CSRF
// El servidor exige el token de sesión en todo POST. Los formularios ya lo
// traen desde PHP; aquí se cubren las peticiones fetch() y los formularios
// que se crean con JavaScript. Va fuera de DOMContentLoaded para que esté
// listo antes que los scripts de cada vista.
// ---------------------------------------------------------------
(function () {
    'use strict';

    window.csrfToken = function () {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    // fetch(): agrega la cabecera X-CSRF-Token a toda petición no-GET del mismo sitio
    const fetchOriginal = window.fetch;
    window.fetch = function (recurso, opciones) {
        opciones = opciones || {};
        const metodo = (opciones.method || (recurso instanceof Request ? recurso.method : 'GET')).toUpperCase();
        const destino = new URL(recurso instanceof Request ? recurso.url : recurso, window.location.href);

        if (metodo !== 'GET' && metodo !== 'HEAD' && destino.origin === window.location.origin) {
            const cabeceras = new Headers(opciones.headers || (recurso instanceof Request ? recurso.headers : undefined));
            cabeceras.set('X-CSRF-Token', window.csrfToken());
            opciones = Object.assign({}, opciones, { headers: cabeceras });
        }
        return fetchOriginal.call(this, recurso, opciones);
    };

    // Formularios POST creados en el navegador: se les agrega el campo antes de enviarse
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.method.toLowerCase() === 'post' && !form.querySelector('input[name="_csrf"]')) {
            const campo = document.createElement('input');
            campo.type = 'hidden';
            campo.name = '_csrf';
            campo.value = window.csrfToken();
            form.appendChild(campo);
        }
    }, true);

    // Aviso de validación con SweetAlert (o alert() si la librería no cargó)
    window.avisar = function (titulo, texto, icono) {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({ icon: icono || 'warning', title: titulo, text: texto, confirmButtonColor: '#2563eb' });
        }
        alert(texto);
        return Promise.resolve();
    };

    // Envía una acción (ej. eliminar) por POST con el token, en lugar de seguir un enlace GET
    window.enviarPost = function (url) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.hidden = true;
        const campo = document.createElement('input');
        campo.type = 'hidden';
        campo.name = '_csrf';
        campo.value = window.csrfToken();
        form.appendChild(campo);
        document.body.appendChild(form);
        form.submit();
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ---------------------------------------------------------------
    // 1. INICIALIZACIÓN DE SELECTS AUTOCOMPLETABLES (TOMSELECT)
    // ---------------------------------------------------------------

    // Suite Ejecutiva para Selectores de Huéspedes con Ficha Visual
    window.initTomSelectHuesped = function (el) {
        if (!el || typeof TomSelect === 'undefined' || el.classList.contains('tomselected')) {
            return el?.tomselect || null;
        }

        // Extraer opciones con metadatos ricos de los data attributes o data-data del <select>
        const initialOptions = [];
        Array.from(el.options).forEach(function (opt) {
            if (!opt.value) return;
            let meta = {};
            if (opt.dataset.data) {
                try {
                    meta = JSON.parse(opt.dataset.data);
                } catch (e) {
                    meta = {};
                }
            }

            const rawText = opt.text || '';
            let nombres = meta.nombres || opt.dataset.nombres || '';
            let doc = meta.doc || opt.dataset.doc || '';
            let tipoDoc = meta.tipoDoc || opt.dataset.tipoDoc || 'DNI';
            let telefono = meta.telefono || opt.dataset.telefono || '';
            let correo = meta.correo || opt.dataset.correo || '';
            let nacionalidad = meta.nacionalidad || opt.dataset.nacionalidad || '';
            let totalReservas = parseInt(meta.totalReservas !== undefined ? meta.totalReservas : (opt.dataset.totalReservas || 0), 10);

            // Si no se pasaron data attributes específicos, inferir desde el texto "Nombre (Doc: XXXXX - Tel: YYYY)"
            if (!nombres && rawText) {
                const matchDoc = rawText.match(/^(.*?)\s*\((?:Doc:\s*)?([^\–\-]+)(?:[\–\-]\s*Tel:\s*([^\)]+))?\)/i);
                if (matchDoc) {
                    nombres = matchDoc[1].trim();
                    doc = matchDoc[2].trim();
                    if (matchDoc[3]) telefono = matchDoc[3].trim();
                } else {
                    const matchSimple = rawText.match(/^(.*?)\s*\(([^\)]+)\)/);
                    if (matchSimple) {
                        nombres = matchSimple[1].trim();
                        doc = matchSimple[2].trim();
                    } else {
                        nombres = rawText.trim();
                    }
                }
            }

            initialOptions.push({
                value: String(opt.value),
                text: rawText,
                nombres: nombres,
                doc: doc,
                tipoDoc: tipoDoc,
                telefono: telefono,
                correo: correo,
                nacionalidad: nacionalidad,
                totalReservas: totalReservas
            });
        });

        const tsInstance = new TomSelect(el, {
            options: initialOptions,
            valueField: 'value',
            labelField: 'nombres',
            searchField: ['nombres', 'doc', 'telefono', 'correo', 'text'],
            create: false,
            allowEmptyOption: true,
            maxOptions: 50,
            sortField: { field: "nombres", direction: "asc" },
            placeholder: el.getAttribute('placeholder') || '-- Escribe el nombre, DNI o teléfono del huésped --',
            plugins: ['clear_button'],
            render: {
                option: function (data, escape) {
                    if (!data.value) {
                        return '<div class="p-2 text-muted fst-italic small">' + escape(data.text || '-- Seleccionar huésped --') + '</div>';
                    }

                    const nombres = data.nombres || data.text || '';
                    const doc = data.doc || '';
                    const tipoDoc = data.tipoDoc || 'DOC';
                    const tel = (data.telefono && data.telefono !== 'No registrado' && data.telefono !== 'S/N') ? data.telefono : '';
                    const nac = (data.nacionalidad && data.nacionalidad !== 'No especificada') ? data.nacionalidad : '';
                    const totalReservas = parseInt(data.totalReservas || 0, 10);

                    // Iniciales para avatar
                    const partes = nombres.trim().split(/\s+/).filter(Boolean);
                    let iniciales = '';
                    if (partes.length >= 2) {
                        iniciales = (partes[0][0] + partes[1][0]).toUpperCase();
                    } else if (partes.length === 1 && partes[0].length >= 2) {
                        iniciales = partes[0].substring(0, 2).toUpperCase();
                    } else if (partes.length === 1) {
                        iniciales = partes[0][0].toUpperCase();
                    } else {
                        iniciales = '<i class="bi bi-person"></i>';
                    }

                    // Insignia según fidelidad
                    let vipBadge = '';
                    let avatarBg = 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';

                    if (totalReservas >= 2) {
                        vipBadge = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 rounded-pill"><i class="bi bi-star-fill text-warning me-1"></i>VIP (' + totalReservas + ' estancias)</span>';
                        avatarBg = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                    } else if (totalReservas === 1) {
                        vipBadge = '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0.5 rounded-pill"><i class="bi bi-person-check me-1"></i>Cliente Registrado</span>';
                        avatarBg = 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)';
                    } else {
                        vipBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill"><i class="bi bi-sparkles me-1"></i>Nuevo Huésped</span>';
                        avatarBg = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                    }

                    return '<div class="ts-guest-option py-2 px-3">' +
                        '<div class="d-flex align-items-center justify-content-between gap-3">' +
                            '<div class="d-flex align-items-center gap-3 min-w-0">' +
                                '<div class="ts-guest-avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" style="background: ' + avatarBg + ';">' +
                                    iniciales +
                                '</div>' +
                                '<div class="ts-guest-info min-w-0">' +
                                    '<div class="d-flex align-items-center gap-2 flex-wrap mb-1">' +
                                        '<span class="ts-guest-name fw-bold text-truncate">' + escape(nombres) + '</span>' +
                                        vipBadge +
                                    '</div>' +
                                    '<div class="ts-guest-meta d-flex align-items-center gap-2 flex-wrap text-muted small">' +
                                        (doc ? '<span class="badge bg-body-secondary text-body border px-2 py-0.5 font-monospace"><i class="bi bi-card-heading me-1 text-primary"></i>' + escape(tipoDoc) + ': ' + escape(doc) + '</span>' : '') +
                                        (tel ? '<span class="d-inline-flex align-items-center"><i class="bi bi-telephone text-primary me-1"></i>' + escape(tel) + '</span>' : '') +
                                        (nac ? '<span class="d-inline-flex align-items-center"><i class="bi bi-geo-alt text-info me-1"></i>' + escape(nac) + '</span>' : '') +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="ts-guest-action flex-shrink-0 text-muted small d-none d-md-block">' +
                                '<span class="badge bg-body-secondary text-secondary border">Elegir <i class="bi bi-chevron-right ms-1"></i></span>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                },
                item: function (data, escape) {
                    if (!data.value) {
                        return '<span class="text-muted">' + escape(data.text || '-- Seleccionar huésped --') + '</span>';
                    }
                    const nombres = data.nombres || data.text || '';
                    const doc = data.doc || '';
                    const tipoDoc = data.tipoDoc || 'DOC';
                    const totalReservas = parseInt(data.totalReservas || 0, 10);
                    const isVip = totalReservas >= 2;

                    const partes = nombres.trim().split(/\s+/).filter(Boolean);
                    let iniciales = '';
                    if (partes.length >= 2) {
                        iniciales = (partes[0][0] + partes[1][0]).toUpperCase();
                    } else if (partes.length === 1 && partes[0].length >= 2) {
                        iniciales = partes[0].substring(0, 2).toUpperCase();
                    } else if (partes.length === 1) {
                        iniciales = partes[0][0].toUpperCase();
                    } else {
                        iniciales = '<i class="bi bi-person"></i>';
                    }

                    const avatarBg = isVip
                        ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'
                        : 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';

                    return '<div class="ts-guest-selected-item d-flex align-items-center gap-2 py-0.5">' +
                        '<div class="ts-guest-avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" style="background: ' + avatarBg + '; width: 26px; height: 26px; font-size: 0.72rem;">' +
                            iniciales +
                        '</div>' +
                        '<span class="fw-bold text-body text-truncate">' + escape(nombres) + '</span>' +
                        (doc ? '<span class="badge bg-body-secondary text-body border px-2 py-0.5 font-monospace ms-1" style="font-size: 0.75rem;">' + escape(tipoDoc) + ': ' + escape(doc) + '</span>' : '') +
                        (isVip ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 0.72rem;"><i class="bi bi-star-fill text-warning me-1"></i>VIP</span>' : '') +
                    '</div>';
                },
                no_results: function (data, escape) {
                    return '<div class="p-3 text-center">' +
                        '<div class="mb-2 text-muted"><i class="bi bi-person-x fs-2"></i></div>' +
                        '<div class="fw-semibold text-body mb-1">No se encontró al huésped "' + escape(data.input) + '"</div>' +
                        '<p class="text-muted small mb-2">Puedes registrarlo en el sistema con un solo clic.</p>' +
                        '<button type="button" class="btn btn-sm btn-primary shadow-xs" onclick="const modalBtn = document.querySelector(\'[data-bs-target=\\\'#modalNuevoHuesped\\\']\'); if(modalBtn) modalBtn.click();">' +
                            '<i class="bi bi-person-plus me-1"></i> + Registrar Nuevo Huésped' +
                        '</button>' +
                    '</div>';
                }
            }
        });

        // Garantizar que la tarjeta contenedora no tape ni corte el menú desplegable
        tsInstance.on('dropdown_open', function () {
            const card = el.closest('.card');
            if (card) {
                card.style.zIndex = '1050';
                card.style.position = 'relative';
                card.style.overflow = 'visible';
            }
        });
        tsInstance.on('dropdown_close', function () {
            const card = el.closest('.card');
            if (card) {
                card.style.zIndex = '';
            }
        });

        return tsInstance;
    };

    const selectElements = document.querySelectorAll('.select-search, select#huesped_id, select[name="huesped_id"], select#habitacion_id, .select-huesped');
    selectElements.forEach(function (el) {
        if (typeof TomSelect === 'undefined' || el.classList.contains('tomselected')) {
            return;
        }

        // Si es el selector de huéspedes, aplicar la suite ejecutiva
        if (el.id === 'huesped_id' || el.name === 'huesped_id' || el.classList.contains('select-huesped')) {
            window.initTomSelectHuesped(el);
            return;
        }

        // Selects regulares
        const regularTs = new TomSelect(el, {
            create: false,
            allowEmptyOption: true,
            maxOptions: 500,
            sortField: { field: "text", direction: "asc" },
            placeholder: el.getAttribute('placeholder') || '-- Escribe para buscar --',
            plugins: ['clear_button'],
            render: {
                no_results: function () {
                    return '<div class="no-results p-2 text-muted small">No se encontraron coincidencias.</div>';
                }
            }
        });

        regularTs.on('dropdown_open', function () {
            const card = el.closest('.card');
            if (card) {
                card.style.zIndex = '1050';
                card.style.position = 'relative';
                card.style.overflow = 'visible';
            }
        });
        regularTs.on('dropdown_close', function () {
            const card = el.closest('.card');
            if (card) {
                card.style.zIndex = '';
            }
        });
    });

    // ---------------------------------------------------------------
    // 2. TABLAS INTERACTIVAS LIGERAS (SIMPLE-DATATABLES)
    // ---------------------------------------------------------------
    const tables = document.querySelectorAll('.table-datatable, .datatable-init');
    tables.forEach(function (table) {
        if (typeof simpleDatatables !== 'undefined') {
            new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: false,
                perPage: 10,
                perPageSelect: [10, 25, 50, 100],
                labels: {
                    placeholder: "",
                    perPage: "registros",
                    noRows: "No se encontraron registros para mostrar",
                    info: "Mostrando registros del {start} al {end} de un total de {rows}",
                    noResults: "Ningún resultado coincide con la búsqueda"
                }
            });
        }
    });

    // ---------------------------------------------------------------
    // 3. PREVENCIÓN DE DOBLE CLIC Y ESTADOS DE CARGA (LOADING SPINNERS)
    // ---------------------------------------------------------------
    const forms = document.querySelectorAll('form:not(.no-loading)');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            // Verificar si el formulario es válido según HTML5
            if (!form.checkValidity()) {
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                // Evitar múltiples envíos
                if (submitBtn.dataset.submitting === 'true') {
                    e.preventDefault();
                    return;
                }

                submitBtn.dataset.submitting = 'true';
                const originalHtml = submitBtn.innerHTML;

                // Esperar un ciclo para permitir el submit normal y luego deshabilitar visualmente
                setTimeout(function () {
                    submitBtn.classList.add('disabled');
                    submitBtn.setAttribute('disabled', 'disabled');
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';
                }, 10);
            }
        });
    });

    // ---------------------------------------------------------------
    // 4. MODALES DE CONFIRMACIÓN MODERNOS (SWEETALERT2)
    // ---------------------------------------------------------------
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-confirm], a.btn-confirm-action');
        if (trigger) {
            e.preventDefault();
            const form = trigger.tagName === 'A' ? null : (trigger.form || trigger.closest('form'));

            // Validar el formulario ANTES de pedir confirmación (campos required, min, etc.)
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Ejecuta la acción confirmada. requestSubmit() respeta la validación,
            // dispara el evento submit (protección de doble envío) y envía el name/value del botón.
            const ejecutar = function () {
                if (trigger.tagName === 'A' && trigger.href) {
                    // Los enlaces que cambian datos (eliminar, anular...) se envían por POST con token
                    if (trigger.dataset.method === 'get') {
                        window.location.href = trigger.href;
                    } else {
                        window.enviarPost(trigger.href);
                    }
                } else if (form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit(trigger.type === 'submit' ? trigger : undefined);
                    } else {
                        form.submit();
                    }
                }
            };

            const message = trigger.getAttribute('data-confirm') || '¿Estás seguro de realizar esta acción?';
            const isDanger = trigger.classList.contains('btn-danger') || 
                             trigger.classList.contains('btn-outline-danger') || 
                             trigger.classList.contains('text-danger') ||
                             message.toLowerCase().includes('cancelar') || 
                             message.toLowerCase().includes('eliminar');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: isDanger ? '¿Confirmar Acción?' : '¿Continuar?',
                    text: message,
                    icon: isDanger ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: isDanger ? '#dc2626' : '#2563eb',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: isDanger ? 'Sí, proceder' : 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        ejecutar();
                    }
                });
            } else {
                // Fallback tradicional
                if (confirm(message)) {
                    ejecutar();
                }
            }
        }
    });

    // ---------------------------------------------------------------
    // 5. TOASTS NOTIFICACIONES AUTOMÁTICAS
    // ---------------------------------------------------------------
    const flashToast = document.getElementById('sessionFlashToast');
    if (flashToast && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        const toast = new bootstrap.Toast(flashToast, { delay: 6000 });
        toast.show();
    }

    // Helper global para disparar Toasts dinámicamente desde cualquier vista
    window.showToast = function (mensaje, tipo = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const icon = tipo === 'success' ? 'bi-check-circle-fill text-success' :
                     tipo === 'danger'  ? 'bi-exclamation-triangle-fill text-danger' :
                     tipo === 'warning' ? 'bi-exclamation-circle-fill text-warning' : 'bi-info-circle-fill text-primary';

        const toastEl = document.createElement('div');
        toastEl.className = 'toast show shadow-lg border-0 fade';
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="toast-header border-bottom-0 pb-1">
                <i class="bi ${icon} me-2 fs-5"></i>
                <strong class="me-auto">Notificación</strong>
                <small class="text-muted">ahora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body pt-1 text-secondary">
                ${mensaje}
            </div>
        `;
        container.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 5000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    };

    // ---------------------------------------------------------------
    // 6. ATAJOS DE TECLADO GLOBALES (HOTKEYS PARA RECEPCIÓN)
    // ---------------------------------------------------------------
    document.addEventListener('keydown', function (e) {
        // F2 o Ctrl + K -> Ir a Nueva Reserva
        if (e.key === 'F2' || (e.ctrlKey && e.key === 'k')) {
            e.preventDefault();
            const btnReserva = document.querySelector('a[href*="reservas/crear"]');
            if (btnReserva) {
                window.location.href = btnReserva.href;
            }
        }
        // F3 -> Ir a Consulta Rápida de Huésped por DNI en mostrador
        if (e.key === 'F3') {
            e.preventDefault();
            const btnBuscar = document.querySelector('a[href*="huespedes/buscar"]');
            if (btnBuscar) {
                window.location.href = btnBuscar.href;
            } else {
                window.location.href = 'index.php?url=huespedes/buscar';
            }
        }
        // F4 -> Ir a Check-in rápido
        if (e.key === 'F4') {
            e.preventDefault();
            const btnCheckin = document.querySelector('a[href*="recepcion/checkin"]');
            if (btnCheckin) {
                window.location.href = btnCheckin.href;
            }
        }
    });

    // ---------------------------------------------------------------
    // 7. PERSISTENCIA Y SINCRONIZACIÓN DEL ESTADO DEL SIDEBAR
    // ---------------------------------------------------------------
    const toggleSidebarBtn = document.querySelector('[data-lte-toggle="sidebar"]');
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function () {
            // Guardar preferencia tras el toggle de AdminLTE
            setTimeout(function () {
                try {
                    if (document.body.classList.contains('sidebar-collapse')) {
                        localStorage.setItem('lte.sidebar.state', 'collapsed');
                    } else {
                        localStorage.setItem('lte.sidebar.state', 'expanded');
                    }
                } catch (e) {}
            }, 60);
        });
    }

    // Restaurar preferencia guardada al recargar o cambiar de página
    try {
        const savedSidebarState = localStorage.getItem('lte.sidebar.state');
        if (savedSidebarState === 'collapsed' && window.innerWidth >= 992) {
            document.body.classList.add('sidebar-collapse');
        }
    } catch (e) {}

    // ---------------------------------------------------------------
    // 8. INTERRUPTOR TIC-TAC DE TEMA CLARO / OSCURO (SWITCH DIRECTO)
    // ---------------------------------------------------------------
    const btnThemeToggle = document.getElementById('btnThemeToggle');
    if (btnThemeToggle) {
        btnThemeToggle.addEventListener('click', function (e) {
            e.preventDefault();
            const root = document.documentElement;
            const currentTheme = root.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            root.setAttribute('data-bs-theme', newTheme);
            try {
                localStorage.setItem('lte-theme', newTheme);
            } catch (err) {}

            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
        });
    }

    // ---------------------------------------------------------------
    // 9. FORMULARIO CON ERRORES: RESTAURAR LO ESCRITO Y MARCAR CAMPOS
    // El servidor (Controller::volverConErrores) deja en window.formPrevio la
    // ruta del envío, lo que el usuario escribió y el error de cada campo.
    // ---------------------------------------------------------------
    restaurarFormularioPrevio(window.formPrevio);
});

function restaurarFormularioPrevio(previo) {
    if (!previo || !previo.accion) return;

    // El formulario cuyo action apunta a la misma ruta que falló
    const form = [...document.querySelectorAll('form')].find(function (f) {
        try {
            return new URL(f.action, window.location.href).searchParams.get('url') === previo.accion;
        } catch (e) {
            return false;
        }
    });
    if (!form) return;

    const campos = function (nombre) {
        return [...form.elements].filter(el => el.name === nombre || el.name === nombre + '[]');
    };

    // 1. Volver a poner lo que se había escrito
    Object.entries(previo.datos || {}).forEach(function ([nombre, valor]) {
        campos(nombre).forEach(function (el) {
            // Los archivos no se pueden reponer y el id oculto ya viene correcto del servidor
            if (el.type === 'file' || (el.type === 'hidden' && nombre === 'id')) return;
            const valores = Array.isArray(valor) ? valor.map(String) : [String(valor)];
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = valores.includes(el.value);
            } else if (el.tomselect) {
                el.tomselect.setValue(valores); // sin modo silencioso: dispara change en el select
                return;
            } else if (el.multiple) {
                [...el.options].forEach(o => { o.selected = valores.includes(o.value); });
            } else {
                el.value = valores[0];
            }
            // Avisar a los scripts de la vista (ej. recálculo de saldo o totales) como si lo hubiera hecho el usuario
            el.dispatchEvent(new Event(el.tagName === 'SELECT' || el.type === 'checkbox' || el.type === 'radio' ? 'change' : 'input', { bubbles: true }));
        });
    });

    // 2. Marcar los campos con error y mostrar el mensaje debajo
    let primero = null;
    Object.entries(previo.errores || {}).forEach(function ([nombre, mensaje]) {
        const el = campos(nombre)[0];
        if (!el) return;
        const visible = el.tomselect ? el.tomselect.wrapper : el;
        el.classList.add('is-invalid');
        visible.classList.add('is-invalid');

        const aviso = document.createElement('div');
        aviso.className = 'invalid-feedback d-block';
        aviso.id = 'error-' + (el.id || nombre);
        aviso.textContent = mensaje;
        // Debajo del grupo completo si el campo está dentro de un input-group
        const ancla = visible.closest('.input-group') || visible;
        ancla.insertAdjacentElement('afterend', aviso);
        el.setAttribute('aria-invalid', 'true');
        el.setAttribute('aria-describedby', aviso.id);

        // Al corregir el campo se quita la marca
        const limpiar = function () {
            el.classList.remove('is-invalid');
            visible.classList.remove('is-invalid');
            el.removeAttribute('aria-invalid');
            aviso.remove();
        };
        el.addEventListener('input', limpiar, { once: true });
        el.addEventListener('change', limpiar, { once: true });

        primero = primero || el;
    });

    if (primero) {
        (primero.tomselect ? primero.tomselect : primero).focus();
        primero.closest('.card, form')?.scrollIntoView({ block: 'start', behavior: 'smooth' });
    }
}
