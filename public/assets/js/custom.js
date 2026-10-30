/**
 * ====================================================================
 * Hotel Manager - Frontend JavaScript Suite
 * Suite de Experiencia de Usuario (UX/UI Moderna)
 * ====================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ---------------------------------------------------------------
    // 1. INICIALIZACIÓN DE SELECTS AUTOCOMPLETABLES (TOMSELECT)
    // ---------------------------------------------------------------
    const selectElements = document.querySelectorAll('.select-search, select#huesped_id, select[name="huesped_id"], select#habitacion_id');
    selectElements.forEach(function (el) {
        if (typeof TomSelect !== 'undefined' && !el.classList.contains('tomselected')) {
            new TomSelect(el, {
                create: false,
                allowEmptyOption: true,
                maxOptions: 500,
                sortField: { field: "text", direction: "asc" },
                placeholder: el.getAttribute('placeholder') || '-- Escribe para buscar por nombre o documento --',
                plugins: ['clear_button'],
                render: {
                    no_results: function () {
                        return '<div class="no-results p-2 text-muted small">No se encontraron coincidencias.</div>';
                    }
                }
            });
        }
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
                    placeholder: "Buscar en la tabla...",
                    perPage: "por página",
                    noRows: "No se encontraron registros para mostrar",
                    info: "Mostrando {start} a {end} de {rows} registros",
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
                        if (trigger.tagName === 'A' && trigger.href) {
                            window.location.href = trigger.href;
                        } else if (trigger.form) {
                            trigger.form.submit();
                        } else if (trigger.closest('form')) {
                            trigger.closest('form').submit();
                        }
                    }
                });
            } else {
                // Fallback tradicional
                if (confirm(message)) {
                    if (trigger.tagName === 'A' && trigger.href) {
                        window.location.href = trigger.href;
                    } else if (trigger.closest('form')) {
                        trigger.closest('form').submit();
                    }
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
        // F4 -> Ir a Check-in rápido
        if (e.key === 'F4') {
            e.preventDefault();
            const btnCheckin = document.querySelector('a[href*="recepcion/checkin"]');
            if (btnCheckin) {
                window.location.href = btnCheckin.href;
            }
        }
    });
});
