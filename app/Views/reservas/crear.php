<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>Registrar Nueva Reserva</h3>
                <p class="text-muted mb-0 small">Formulario para crear y programar una reserva hotelera</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('reservas') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la Lista
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash']['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form action="<?= url('reservas/guardar') ?>" method="POST" id="formReserva">
            <div class="row g-4">
                <!-- Columna Izquierda: Datos del Huésped y Fechas -->
                <div class="col-12 col-lg-8">
                    <!-- 1. Datos del Huésped -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-person-circle text-primary me-2"></i>1. Seleccionar o Registrar Huésped
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary shadow-xs" data-bs-toggle="modal" data-bs-target="#modalNuevoHuesped">
                                <i class="bi bi-person-plus me-1"></i> + Nuevo Huésped
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Huésped Titular <span class="text-danger">*</span></label>
                                    <select name="huesped_id" id="huesped_id" class="form-select select-search" required>
                                        <option value="">-- Seleccionar huésped registrado --</option>
                                        <?php foreach ($huespedes as $h): ?>
                                            <option value="<?= $h['id'] ?>">
                                                <?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?> (Doc: <?= htmlspecialchars($h['tipo_documento'] . ' ' . $h['numero_documento']) ?> - Tel: <?= htmlspecialchars($h['telefono'] ?: 'S/N') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Si el cliente es nuevo, regístralo rápidamente con el botón <strong>"+ Nuevo Huésped"</strong> sin salir de esta pantalla.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Fechas de Estadía y Huéspedes -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-body-tertiary">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-calendar-range text-info me-2"></i>2. Fechas de Estadía y Ocupantes
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Fecha de Entrada (Check-in) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_entrada" id="fecha_entrada" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Fecha de Salida (Check-out) <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_salida" id="fecha_salida" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Total Noches</label>
                                    <input type="text" id="total_noches" class="form-control bg-body-secondary fw-bold text-center" value="1 noche" readonly>
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Adultos</label>
                                    <input type="number" name="adultos" class="form-control" value="1" min="1" max="10">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Niños</label>
                                    <input type="number" name="ninos" class="form-control" value="0" min="0" max="6">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Canal / Origen</label>
                                    <select name="origen" class="form-select">
                                        <option value="manual">Manual (Recepción)</option>
                                        <option value="telefonica">Telefónica</option>
                                        <option value="web">Página Web</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Selección de Habitaciones Disponibles -->
                    <?php
                        $pisos = array_unique(array_filter(array_column($habitaciones, 'piso')));
                        sort($pisos);
                        $tiposHab = array_unique(array_filter(array_column($habitaciones, 'tipo_nombre')));
                        sort($tiposHab);
                    ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="bi bi-door-open-fill text-warning me-2"></i>3. Seleccionar Habitación(es) <span class="text-danger">*</span>
                                </h5>
                                <small class="text-muted">Elige una o varias habitaciones para la reserva</small>
                            </div>
                            <span class="badge badge-soft-info"><i class="bi bi-shield-check me-1"></i>Fechas verificadas</span>
                        </div>
                        <div class="card-body">
                            <!-- Barra de Búsqueda y Filtros en Tiempo Real -->
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                            <input type="text" id="filtroHabitacionTexto" class="form-control form-control-sm border-start-0" placeholder="Buscar por número (ej: 101), tipo o piso...">
                                            <button type="button" class="btn btn-outline-secondary border" id="btnLimpiarBuscar" title="Limpiar" style="display: none;">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <select id="filtroHabitacionPiso" class="form-select form-select-sm">
                                            <option value="">-- Todos los pisos --</option>
                                            <?php foreach ($pisos as $p): ?>
                                                <option value="<?= htmlspecialchars($p) ?>">Piso <?= htmlspecialchars($p) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <select id="filtroHabitacionTipo" class="form-select form-select-sm">
                                            <option value="">-- Todos los tipos --</option>
                                            <?php foreach ($tiposHab as $t): ?>
                                                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Barra de contadores y selección múltiple rápida -->
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light-subtle flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-soft-primary" id="contadorHabitacionesVisibles">
                                            <i class="bi bi-door-closed me-1"></i><span id="numVisibles"><?= count($habitaciones) ?></span> disponibles
                                        </span>
                                        <span class="badge badge-soft-success" id="contadorHabitacionesSeleccionadas">
                                            <i class="bi bi-check-circle me-1"></i><span id="numSeleccionadas">0</span> seleccionadas
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" id="btnSeleccionarVisibles" style="font-size: 0.75rem;">
                                            <i class="bi bi-check-all me-1"></i> Seleccionar visibles
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" id="btnDeseleccionarTodas" style="font-size: 0.75rem;">
                                            <i class="bi bi-x-circle me-1"></i> Deseleccionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Grilla de Habitaciones -->
                            <div class="row g-3" id="contenedorHabitaciones">
                                <?php foreach ($habitaciones as $hab): ?>
                                    <div class="col-12 col-md-6 col-xl-4 hab-card-item" 
                                         data-numero="<?= htmlspecialchars(strtolower($hab['numero'])) ?>"
                                         data-piso="<?= htmlspecialchars(strtolower($hab['piso'])) ?>"
                                         data-tipo="<?= htmlspecialchars(strtolower($hab['tipo_nombre'])) ?>"
                                         data-precio="<?= $hab['precio_diario'] ?>">
                                        <div class="card-room-selectable h-100 shadow-xs">
                                            <div class="d-flex align-items-start gap-2">
                                                <div class="pt-1">
                                                    <input class="form-check-input check-hab" type="checkbox" name="habitaciones[]" value="<?= $hab['id'] ?>" id="hab_<?= $hab['id'] ?>" data-precio="<?= $hab['precio_diario'] ?>" data-numero="<?= htmlspecialchars($hab['numero']) ?>">
                                                </div>
                                                <label class="form-check-label w-100 cursor-pointer" for="hab_<?= $hab['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="fs-5 text-primary">Hab. <?= htmlspecialchars($hab['numero']) ?></strong>
                                                        <span class="badge bg-dark bg-opacity-75 text-white" style="font-size: 0.65rem;">Piso <?= htmlspecialchars($hab['piso']) ?></span>
                                                    </div>
                                                    <div class="text-muted small fw-semibold"><?= htmlspecialchars($hab['tipo_nombre']) ?></div>
                                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light-subtle">
                                                        <span class="fw-bold text-success fs-6"><?= dinero($hab['precio_diario']) ?></span>
                                                        <small class="text-muted" style="font-size: 0.72rem;">por noche</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Mensaje cuando la búsqueda no arroja resultados -->
                                <div id="noHabitacionesMsg" class="col-12 text-center py-4 text-muted" style="display: none;">
                                    <i class="bi bi-search fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-0 fw-semibold">No se encontraron habitaciones con los filtros aplicados.</p>
                                    <button type="button" class="btn btn-link btn-sm mt-1" id="btnRestablecerFiltros">Restablecer filtros de búsqueda</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Resumen de Costo y Pago Inicial -->
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-receipt-cutoff me-2"></i>Resumen y Cobro
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Habitaciones:</span>
                                <span id="resumenHabitaciones" class="fw-semibold">Ninguna seleccionada</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Duración:</span>
                                <span id="resumenNoches" class="fw-semibold">1 noche</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3 align-items-center">
                                <span class="fs-5 fw-bold">Monto Total:</span>
                                <span class="fs-4 fw-bold text-primary" id="montoTotalDisplay">S/. 0.00</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Anticipo / Adelanto (S/.)</label>
                                <input type="number" step="0.01" name="anticipo" id="inputAnticipo" class="form-control form-control-lg" value="0.00" min="0">
                                <div class="form-text">Si el cliente abona ahora, se registrará un recibo de caja.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Método de Pago</label>
                                <select name="metodo_pago" class="form-select">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                    <option value="Yape/Plin">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado de la Reserva</label>
                                <select name="estado" class="form-select">
                                    <option value="confirmada" selected>Confirmada</option>
                                    <option value="pendiente">Pendiente de pago</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notas / Requerimientos</label>
                                <textarea name="notas" class="form-control" rows="2" placeholder="Ej: Cama adicional, llegada tardía..."></textarea>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                    <i class="bi bi-check-circle-fill me-2"></i>Confirmar Reserva
                                </button>
                                <a href="<?= url('reservas') ?>" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const entradaInput = document.getElementById('fecha_entrada');
    const salidaInput = document.getElementById('fecha_salida');
    const nochesDisplay = document.getElementById('total_noches');
    const resumenNoches = document.getElementById('resumenNoches');
    const resumenHab = document.getElementById('resumenHabitaciones');
    const montoTotalDisplay = document.getElementById('montoTotalDisplay');
    const checkHabs = document.querySelectorAll('.check-hab');
    const habItems = document.querySelectorAll('.hab-card-item');

    // Elementos de búsqueda y filtros
    const inputTexto = document.getElementById('filtroHabitacionTexto');
    const selectPiso = document.getElementById('filtroHabitacionPiso');
    const selectTipo = document.getElementById('filtroHabitacionTipo');
    const btnLimpiar = document.getElementById('btnLimpiarBuscar');
    const numVisibles = document.getElementById('numVisibles');
    const numSeleccionadas = document.getElementById('numSeleccionadas');
    const noHabMsg = document.getElementById('noHabitacionesMsg');
    const btnSeleccionarVisibles = document.getElementById('btnSeleccionarVisibles');
    const btnDeseleccionarTodas = document.getElementById('btnDeseleccionarTodas');
    const btnRestablecerFiltros = document.getElementById('btnRestablecerFiltros');

    // 1. Cálculo de precios y totales
    function calcular() {
        const d1 = new Date(entradaInput.value);
        const d2 = new Date(salidaInput.value);
        let noches = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
        if (noches < 1 || isNaN(noches)) noches = 1;

        nochesDisplay.value = noches + (noches === 1 ? ' noche' : ' noches');
        resumenNoches.textContent = noches + (noches === 1 ? ' noche' : ' noches');

        let total = 0;
        let habsSeleccionadas = [];
        let countSelected = 0;

        checkHabs.forEach(cb => {
            const item = cb.closest('.hab-card-item');
            if (cb.checked) {
                countSelected++;
                const precio = parseFloat(cb.dataset.precio || 0);
                total += (precio * noches);
                habsSeleccionadas.push(cb.dataset.numero);
                if (item) item.classList.add('is-selected');
            } else {
                if (item) item.classList.remove('is-selected');
            }
        });

        if (numSeleccionadas) numSeleccionadas.textContent = countSelected;
        resumenHab.textContent = habsSeleccionadas.length > 0 ? habsSeleccionadas.join(', ') : 'Ninguna seleccionada';
        montoTotalDisplay.textContent = 'S/. ' + total.toFixed(2);
    }

    // 2. Filtrado dinámico en tiempo real de habitaciones
    function filtrarHabitaciones() {
        const txt = (inputTexto.value || '').trim().toLowerCase();
        const piso = (selectPiso.value || '').trim().toLowerCase();
        const tipo = (selectTipo.value || '').trim().toLowerCase();

        if (btnLimpiar) {
            btnLimpiar.style.display = txt ? 'inline-block' : 'none';
        }

        let visibles = 0;
        habItems.forEach(item => {
            const habNum = item.dataset.numero || '';
            const habPiso = item.dataset.piso || '';
            const habTipo = item.dataset.tipo || '';

            const coincideTexto = !txt || habNum.includes(txt) || habTipo.includes(txt) || habPiso.includes(txt);
            const coincidePiso = !piso || habPiso === piso;
            const coincideTipo = !tipo || habTipo === tipo;

            if (coincideTexto && coincidePiso && coincideTipo) {
                item.style.display = '';
                visibles++;
            } else {
                item.style.display = 'none';
            }
        });

        if (numVisibles) numVisibles.textContent = visibles;
        if (noHabMsg) noHabMsg.style.display = (visibles === 0) ? 'block' : 'none';
    }

    // Eventos de filtros
    if (inputTexto) inputTexto.addEventListener('input', filtrarHabitaciones);
    if (selectPiso) selectPiso.addEventListener('change', filtrarHabitaciones);
    if (selectTipo) selectTipo.addEventListener('change', filtrarHabitaciones);

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            inputTexto.value = '';
            filtrarHabitaciones();
            inputTexto.focus();
        });
    }

    if (btnRestablecerFiltros) {
        btnRestablecerFiltros.addEventListener('click', function() {
            if (inputTexto) inputTexto.value = '';
            if (selectPiso) selectPiso.value = '';
            if (selectTipo) selectTipo.value = '';
            filtrarHabitaciones();
        });
    }

    // Seleccionar todas las que están actualmente visibles
    if (btnSeleccionarVisibles) {
        btnSeleccionarVisibles.addEventListener('click', function() {
            habItems.forEach(item => {
                if (item.style.display !== 'none') {
                    const cb = item.querySelector('.check-hab');
                    if (cb) cb.checked = true;
                }
            });
            calcular();
        });
    }

    // Deseleccionar todas
    if (btnDeseleccionarTodas) {
        btnDeseleccionarTodas.addEventListener('click', function() {
            checkHabs.forEach(cb => cb.checked = false);
            calcular();
        });
    }

    // Permitir clic en toda la tarjeta para activar/desactivar checkbox
    habItems.forEach(item => {
        const card = item.querySelector('.card-room-selectable');
        const cb = item.querySelector('.check-hab');
        if (card && cb) {
            card.addEventListener('click', function(e) {
                if (e.target !== cb && !e.target.closest('input')) {
                    cb.checked = !cb.checked;
                    calcular();
                }
            });
        }
    });

    // Control de fechas
    entradaInput.addEventListener('change', function() {
        if (salidaInput.value <= entradaInput.value) {
            const next = new Date(entradaInput.value);
            next.setDate(next.getDate() + 1);
            salidaInput.value = next.toISOString().split('T')[0];
        }
        calcular();
    });

    salidaInput.addEventListener('change', calcular);
    checkHabs.forEach(cb => cb.addEventListener('change', calcular));

    // Inicializar cálculos
    calcular();

    // -------------------------------------------------------------
    // GESTIÓN DEL MODAL DE REGISTRO RÁPIDO DE HUÉSPED VÍA AJAX
    // -------------------------------------------------------------
    const formModalHuesped = document.getElementById('formModalHuesped');
    const btnGuardarModal = document.getElementById('btnGuardarHuespedModal');
    const modalHuespedEl = document.getElementById('modalNuevoHuesped');
    const alertModalHuesped = document.getElementById('alertModalHuesped');

    if (formModalHuesped && btnGuardarModal) {
        formModalHuesped.addEventListener('submit', function(e) {
            e.preventDefault();
            alertModalHuesped.classList.add('d-none');
            alertModalHuesped.textContent = '';

            const formData = new FormData(formModalHuesped);
            const originalBtnHtml = btnGuardarModal.innerHTML;

            btnGuardarModal.disabled = true;
            btnGuardarModal.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            fetch('<?= url("huespedes/apiGuardar") ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnGuardarModal.disabled = false;
                btnGuardarModal.innerHTML = originalBtnHtml;

                if (!data.success) {
                    alertModalHuesped.textContent = data.mensaje || 'Error al guardar el huésped.';
                    alertModalHuesped.classList.remove('d-none');
                    return;
                }

                // Inyectar en el selector de huésped
                const selectHuesped = document.getElementById('huesped_id');
                if (selectHuesped) {
                    if (selectHuesped.tomselect) {
                        selectHuesped.tomselect.addOption({
                            value: data.huesped.id,
                            text: data.huesped.texto_select
                        });
                        selectHuesped.tomselect.setValue(data.huesped.id);
                    } else {
                        const nuevaOpcion = new Option(data.huesped.texto_select, data.huesped.id, true, true);
                        selectHuesped.add(nuevaOpcion);
                        selectHuesped.value = data.huesped.id;
                    }
                }

                // Limpiar formulario y cerrar modal
                formModalHuesped.reset();
                const modalInstance = bootstrap.Modal.getInstance(modalHuespedEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Notificación visual de éxito
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: '¡Huésped registrado y seleccionado!',
                        text: data.huesped.nombre_completo,
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                }
            })
            .catch(err => {
                btnGuardarModal.disabled = false;
                btnGuardarModal.innerHTML = originalBtnHtml;
                alertModalHuesped.textContent = 'Ocurrió un error inesperado al conectar con el servidor.';
                alertModalHuesped.classList.remove('d-none');
            });
        });
    }
});
</script>

<!-- MODAL: REGISTRO RÁPIDO DE HUÉSPED (POPUP SIN RECARGAR PÁGINA) -->
<div class="modal fade" id="modalNuevoHuesped" tabindex="-1" aria-labelledby="modalNuevoHuespedLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalNuevoHuespedLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Huésped
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formModalHuesped">
                <div class="modal-body p-4">
                    <!-- Mensaje de alerta en caso de error -->
                    <div class="alert alert-danger d-none py-2 px-3 small" id="alertModalHuesped" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold small">Tipo de Documento <span class="text-danger">*</span></label>
                            <select name="tipo_documento" class="form-select" required>
                                <option value="DNI" selected>DNI (Documento Nacional)</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Carnet Extranjeria">Carné de Extranjería</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold small">Número de Documento <span class="text-danger">*</span></label>
                            <input type="text" name="numero_documento" class="form-control" placeholder="Ej: 71829341" required autofocus>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombres" class="form-control" placeholder="Ej: Juan Carlos" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez Rodríguez" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Teléfono / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="telefono" class="form-control" placeholder="+51 987 654 321">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo" class="form-control" placeholder="cliente@correo.com">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Nacionalidad</label>
                            <input type="text" name="nacionalidad" class="form-control" value="Perú" placeholder="Ej: Perú, Chile, Argentina">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small">Dirección / Procedencia</label>
                            <input type="text" name="direccion" class="form-control" placeholder="Ciudad o dirección de origen">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary fw-semibold" id="btnGuardarHuespedModal">
                        <i class="bi bi-check-circle me-1"></i> Guardar y Seleccionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
