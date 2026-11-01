// JavaScript de la vista reservas/crear.php (antes estaba inline; la CSP no lo permite).

document.body.classList.add('pos-split-active');

document.addEventListener('DOMContentLoaded', function() {
    // -----------------------------------------------------------------
    // REFERENCIAS A ELEMENTOS DEL DOM
    // -----------------------------------------------------------------
    const entradaInput = document.getElementById('fecha_entrada');
    const salidaInput = document.getElementById('fecha_salida');
    const nochesDisplay = document.getElementById('total_noches');
    const badgeTotalNoches = document.getElementById('badgeTotalNochesResumen');
    const resumenFechaEntrada = document.getElementById('resumenFechaEntrada');
    const resumenFechaSalida = document.getElementById('resumenFechaSalida');
    const statusDisponibilidad = document.getElementById('statusDisponibilidad');
    const numAdultosInput = document.getElementById('numAdultos');
    const numNinosInput = document.getElementById('numNinos');

    // Elementos de Cobro y Capacidad
    const listaHabTags = document.getElementById('listaHabitacionesTags');
    const contadorHabTag = document.getElementById('contadorHabTag');
    const listaSrvTags = document.getElementById('listaServiciosTags');
    const bloqueSrvTags = document.getElementById('bloqueServiciosTags');
    const contadorSrvTag = document.getElementById('contadorSrvTag');
    const badgeTotalServicios = document.getElementById('badgeTotalServicios');
    const subtotalHospedajeDisplay = document.getElementById('subtotalHospedajeDisplay');
    const subtotalServiciosDisplay = document.getElementById('subtotalServiciosDisplay');
    const filaServiciosSubtotal = document.getElementById('filaServiciosSubtotal');
    const inputDescuento = document.getElementById('inputDescuento');
    const filaDescuentoSubtotal = document.getElementById('filaDescuentoSubtotal');
    const descuentoDisplay = document.getElementById('descuentoDisplay');
    const subtotalDetalle = document.getElementById('subtotalDetalle');
    const montoTotalDisplay = document.getElementById('montoTotalDisplay');
    const inputAnticipo = document.getElementById('inputAnticipo');
    const boxAlertaSaldo = document.getElementById('boxAlertaSaldo');
    const iconoSaldo = document.getElementById('iconoSaldo');
    const textoSaldo = document.getElementById('textoSaldo');
    const alertaCapacidad = document.getElementById('alertaCapacidad');
    const iconoCapacidad = document.getElementById('iconoCapacidad');
    const textoCapacidad = document.getElementById('textoCapacidad');
    const selectEstado = document.getElementById('selectEstado');

    const checkHabs = document.querySelectorAll('.check-hab');
    const checkServicios = document.querySelectorAll('.check-servicio');
    const habItems = Array.from(document.querySelectorAll('.hab-card-item'));
    const contenedorHabitaciones = document.getElementById('contenedorHabitaciones');

    // Filtros & Chips
    const inputTexto = document.getElementById('filtroHabitacionTexto');
    const btnLimpiar = document.getElementById('btnLimpiarBuscar');
    const selectOrdenar = document.getElementById('ordenarHabitaciones');
    const chipsTipo = document.querySelectorAll('#chipsFiltroTipo .btn-chip-filter');
    const chipsPiso = document.querySelectorAll('#chipsFiltroPiso .btn-chip-filter');
    const numVisibles = document.getElementById('numVisibles');
    const numSeleccionadas = document.getElementById('numSeleccionadas');
    const noHabMsg = document.getElementById('noHabitacionesMsg');
    const btnRestablecerFiltros = document.getElementById('btnRestablecerFiltros');
    const btnSeleccionarVisibles = document.getElementById('btnSeleccionarVisibles');
    const btnDeseleccionarTodas = document.getElementById('btnDeseleccionarTodas');
    const btnsPresetNoche = document.querySelectorAll('.btn-preset-noche');

    let filtroTipoActivo = '';
    let filtroPisoActivo = '';
    let globalTotalCalculado = 0;
    const habitacionesOcupadasSet = new Set();

    function formatearFecha(isoDate) {
        if (!isoDate) return '--/--/----';
        const parts = isoDate.split('-');
        if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
        return isoDate;
    }

    // -----------------------------------------------------------------
    // 1. MOTOR DE CÁLCULO DE ESTADÍA, CAPACIDAD Y FACTURACIÓN
    // -----------------------------------------------------------------
    function calcular() {
        const d1 = new Date(entradaInput.value + 'T00:00:00');
        const d2 = new Date(salidaInput.value + 'T00:00:00');
        let noches = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
        if (noches < 1 || isNaN(noches)) noches = 1;

        const textoNoches = noches + (noches === 1 ? ' noche' : ' noches');
        nochesDisplay.value = textoNoches;
        badgeTotalNoches.textContent = textoNoches;

        if (resumenFechaEntrada) resumenFechaEntrada.textContent = formatearFecha(entradaInput.value);
        if (resumenFechaSalida) resumenFechaSalida.textContent = formatearFecha(salidaInput.value);

        // Actualizar el costo estimado por estadía en CADA tarjeta de habitación (solo si es más de 1 noche)
        habItems.forEach(item => {
            const precioDiario = parseFloat(item.dataset.precio || 0);
            const totalEstadiaHab = precioDiario * noches;
            const badgeEstadia = item.querySelector('.precio-estadia-hab');
            if (badgeEstadia) {
                if (noches > 1) {
                    badgeEstadia.textContent = `${window.moneda} ${totalEstadiaHab.toFixed(2)} (${noches} nts)`;
                    badgeEstadia.style.display = 'inline-block';
                } else {
                    badgeEstadia.style.display = 'none';
                }
            }
        });

        // Contabilizar habitaciones seleccionadas y capacidad
        let subtotalHabitaciones = 0;
        let countSelected = 0;
        let capacidadTotal = 0;
        let htmlTagsHab = '';

        checkHabs.forEach(cb => {
            const item = cb.closest('.hab-card-item');
            if (cb.checked) {
                countSelected++;
                const precio = parseFloat(cb.dataset.precio || 0);
                const cap = parseInt(cb.dataset.capacidad || 2, 10);
                subtotalHabitaciones += (precio * noches);
                capacidadTotal += cap;
                const num = cb.dataset.numero || '';
                const habId = cb.value;

                if (item) item.classList.add('is-selected');

                htmlTagsHab += `
                    <span class="tag-hab-removible shadow-xs">
                        <i class="bi bi-door-open me-1"></i>Hab. ${num}
                        <button type="button" class="btn-remove-hab ms-1" data-id="${habId}" title="Quitar">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </span>
                `;
            } else {
                if (item) item.classList.remove('is-selected');
            }
        });

        // Contabilizar servicios adicionales
        let subtotalServicios = 0;
        let countServicios = 0;
        let htmlTagsSrv = '';

        checkServicios.forEach(cb => {
            const cardSrv = cb.closest('.service-check-card');
            if (cb.checked) {
                countServicios++;
                const precio = parseFloat(cb.dataset.precio || 0);
                const nombre = cb.dataset.nombre || 'Servicio';
                subtotalServicios += precio;
                if (cardSrv) cardSrv.classList.add('is-active');

                htmlTagsSrv += `
                    <span class="badge badge-soft-success">
                        <i class="bi bi-check2 me-1"></i>${nombre} (${window.moneda} ${precio.toFixed(2)})
                    </span>
                `;
            } else {
                if (cardSrv) cardSrv.classList.remove('is-active');
            }
        });

        // Descuento
        const descuento = Math.max(0, parseFloat(inputDescuento?.value || 0));

        // Totales finales
        const totalBruto = subtotalHabitaciones + subtotalServicios;
        const totalNeto = Math.max(0, totalBruto - descuento);
        globalTotalCalculado = totalNeto;

        // Actualizar UI de contadores y tags
        if (numSeleccionadas) numSeleccionadas.textContent = countSelected;
        if (contadorHabTag) contadorHabTag.textContent = countSelected;
        if (contadorSrvTag) contadorSrvTag.textContent = countServicios;
        if (badgeTotalServicios) badgeTotalServicios.textContent = `${countServicios} extras seleccionados`;

        if (countSelected > 0) {
            listaHabTags.innerHTML = htmlTagsHab;
            listaHabTags.querySelectorAll('.btn-remove-hab').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const targetId = this.dataset.id;
                    const cbTarget = document.getElementById('hab_' + targetId);
                    if (cbTarget) {
                        cbTarget.checked = false;
                        calcular();
                    }
                });
            });
        } else {
            listaHabTags.innerHTML = '<span class="text-muted small fst-italic py-1" id="sinHabitacionesMsg">Ninguna habitación seleccionada</span>';
        }

        // Tags de servicios
        if (countServicios > 0) {
            bloqueSrvTags.style.display = 'block';
            listaSrvTags.innerHTML = htmlTagsSrv;
            filaServiciosSubtotal.style.display = 'flex';
            subtotalServiciosDisplay.textContent = `+${window.moneda} ${subtotalServicios.toFixed(2)}`;
        } else {
            bloqueSrvTags.style.display = 'none';
            filaServiciosSubtotal.style.display = 'none';
        }

        // Fila de descuento
        if (descuento > 0) {
            filaDescuentoSubtotal.style.display = 'flex';
            descuentoDisplay.textContent = `-${window.moneda} ${descuento.toFixed(2)}`;
        } else {
            filaDescuentoSubtotal.style.display = 'none';
        }

        if (subtotalHospedajeDisplay) subtotalHospedajeDisplay.textContent = `${window.moneda} ${subtotalHabitaciones.toFixed(2)}`;
        if (subtotalDetalle) subtotalDetalle.textContent = `${countSelected} hab. × ${noches} ${noches === 1 ? 'noche' : 'noches'}`;
        if (montoTotalDisplay) montoTotalDisplay.textContent = window.moneda + ' ' + totalNeto.toFixed(2);

        // Semáforo de Capacidad
        actualizarSemaforoCapacidad(capacidadTotal, countSelected);

        // Alerta de Saldo Pendiente
        actualizarAlertaSaldo();
    }

    // -----------------------------------------------------------------
    // 2. SEMÁFORO DE CAPACIDAD DE OCUPANTES
    // -----------------------------------------------------------------
    function actualizarSemaforoCapacidad(capacidadTotal, countHab) {
        const adultos = parseInt(numAdultosInput?.value || 1, 10);
        const ninos = parseInt(numNinosInput?.value || 0, 10);
        const totalOcupantes = adultos + ninos;

        if (countHab === 0) {
            alertaCapacidad.className = 'alert alert-secondary py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoCapacidad.className = 'bi bi-people-fill fs-5 text-secondary';
            textoCapacidad.textContent = `Selecciona habitaciones para cubrir ${totalOcupantes} persona(s) (${adultos} ad. + ${ninos} niños).`;
            return;
        }

        if (capacidadTotal >= totalOcupantes) {
            alertaCapacidad.className = 'alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-success-subtle';
            iconoCapacidad.className = 'bi bi-check-circle-fill fs-5 text-success';
            textoCapacidad.innerHTML = `<strong>¡Capacidad cubierta!</strong> ${capacidadTotal} plazas para ${totalOcupantes} ocupantes.`;
        } else {
            const faltante = totalOcupantes - capacidadTotal;
            alertaCapacidad.className = 'alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-warning-subtle';
            iconoCapacidad.className = 'bi bi-exclamation-triangle-fill fs-5 text-warning';
            textoCapacidad.innerHTML = `<strong>Capacidad insuficiente:</strong> Faltan plazas para <strong>${faltante} persona(s)</strong>. Sugerido: añadir otra habitación o cama extra.`;
        }
    }

    if (numAdultosInput) numAdultosInput.addEventListener('input', calcular);
    if (numNinosInput) numNinosInput.addEventListener('input', calcular);
    if (inputDescuento) inputDescuento.addEventListener('input', calcular);

    // -----------------------------------------------------------------
    // 3. VERIFICACIÓN DE DISPONIBILIDAD EN TIEMPO REAL (AJAX)
    // -----------------------------------------------------------------
    function verificarDisponibilidadAjax() {
        if (!entradaInput.value || !salidaInput.value) return;

        if (statusDisponibilidad) statusDisponibilidad.classList.remove('d-none');

        fetch(appUrl('reservas/apiDisponibilidad?entrada=' + entradaInput.value + '&salida=' + salidaInput.value))
            .then(res => res.json())
            .then(data => {
                if (statusDisponibilidad) statusDisponibilidad.classList.add('d-none');
                if (!data.success) return;

                const disponiblesSet = new Set(data.disponibles_ids.map(Number));
                habitacionesOcupadasSet.clear();

                habItems.forEach(item => {
                    const id = parseInt(item.dataset.id, 10);
                    const cb = item.querySelector('.check-hab');
                    const badgeOcupada = item.querySelector('.badge-ocupada-ajax');

                    if (!disponiblesSet.has(id)) {
                        // Ocupada
                        habitacionesOcupadasSet.add(id);
                        item.classList.add('is-occupied');
                        if (badgeOcupada) badgeOcupada.classList.remove('d-none');
                        if (cb) {
                            if (cb.checked) {
                                cb.checked = false; // Desmarcar si quedó seleccionada
                            }
                            cb.disabled = true;
                        }
                    } else {
                        // Disponible
                        item.classList.remove('is-occupied');
                        if (badgeOcupada) badgeOcupada.classList.add('d-none');
                        if (cb) cb.disabled = false;
                    }
                });

                aplicarFiltros();
                calcular();
            })
            .catch(() => {
                if (statusDisponibilidad) statusDisponibilidad.classList.add('d-none');
            });
    }

    // -----------------------------------------------------------------
    // 4. ALERTAS Y PRESETS DE ANTICIPO
    // -----------------------------------------------------------------
    function actualizarAlertaSaldo() {
        const anticipo = parseFloat(inputAnticipo.value || 0);
        const total = globalTotalCalculado;

        if (total === 0) {
            boxAlertaSaldo.className = 'alert alert-secondary py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoSaldo.className = 'bi bi-info-circle-fill fs-5 text-secondary';
            textoSaldo.innerHTML = 'Selecciona al menos una habitación para calcular el cobro.';
            return;
        }

        const saldoRestante = total - anticipo;

        if (anticipo >= total) {
            boxAlertaSaldo.className = 'alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-success-subtle';
            iconoSaldo.className = 'bi bi-check-circle-fill fs-5 text-success';
            textoSaldo.innerHTML = '<strong>¡100% Cubierto!</strong> No quedará saldo pendiente al momento del Check-in.';
            if (selectEstado && selectEstado.value === 'pendiente') {
                selectEstado.value = 'confirmada';
            }
        } else if (anticipo > 0) {
            boxAlertaSaldo.className = 'alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-warning-subtle';
            iconoSaldo.className = 'bi bi-exclamation-circle-fill fs-5 text-warning';
            textoSaldo.innerHTML = `Anticipo abonado: <strong>${window.moneda} ${anticipo.toFixed(2)}</strong>. Saldo a cobrar en Check-in: <strong class="text-danger">${window.moneda} ${saldoRestante.toFixed(2)}</strong>`;
        } else {
            boxAlertaSaldo.className = 'alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-info-subtle';
            iconoSaldo.className = 'bi bi-wallet2 fs-5 text-info';
            textoSaldo.innerHTML = `Sin anticipo. Se cobrará la totalidad de <strong class="text-body">${window.moneda} ${total.toFixed(2)}</strong> en el Check-in.`;
        }
    }

    inputAnticipo.addEventListener('input', actualizarAlertaSaldo);

    document.querySelectorAll('.btn-anticipo-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const pct = parseInt(this.dataset.pct, 10);
            const val = (globalTotalCalculado * (pct / 100));
            inputAnticipo.value = val.toFixed(2);
            actualizarAlertaSaldo();
        });
    });

    // -----------------------------------------------------------------
    // 5. PRESETS DE DURACIÓN DE ESTADÍA
    // -----------------------------------------------------------------
    btnsPresetNoche.forEach(btn => {
        btn.addEventListener('click', function() {
            btnsPresetNoche.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const tipoNoches = this.dataset.noches;
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');

            if (tipoNoches === 'finsemana') {
                const proxViernes = new Date();
                const diaSemana = proxViernes.getDay();
                const diasHastaViernes = (5 - diaSemana + 7) % 7 || 7;
                proxViernes.setDate(proxViernes.getDate() + diasHastaViernes);

                const proxDomingo = new Date(proxViernes);
                proxDomingo.setDate(proxDomingo.getDate() + 2);

                entradaInput.value = proxViernes.toISOString().split('T')[0];
                salidaInput.value = proxDomingo.toISOString().split('T')[0];
            } else {
                const numDias = parseInt(tipoNoches, 10) || 1;
                const dEntrada = new Date(entradaInput.value + 'T00:00:00');
                if (isNaN(dEntrada.getTime())) {
                    entradaInput.value = `${yyyy}-${mm}-${dd}`;
                }
                const baseDate = new Date(entradaInput.value + 'T00:00:00');
                baseDate.setDate(baseDate.getDate() + numDias);
                salidaInput.value = baseDate.toISOString().split('T')[0];
            }

            verificarDisponibilidadAjax();
        });
    });

    // -----------------------------------------------------------------
    // 6. MINI-PERFIL PREVIEW DEL HUÉSPED (CON BADGE VIP INTELIGENTE)
    // -----------------------------------------------------------------
    const selectHuesped = document.getElementById('huesped_id');
    const cardPreviewHuesped = document.getElementById('cardPreviewHuesped');
    const prevNombre = document.getElementById('prevNombre');
    const prevDoc = document.getElementById('prevDoc');
    const prevNacionalidad = document.getElementById('prevNacionalidad');
    const prevTel = document.getElementById('prevTel');
    const prevCorreo = document.getElementById('prevCorreo');
    const prevAvatar = document.getElementById('prevAvatar');
    const prevBadgeVIP = document.getElementById('prevBadgeVIP');

    function actualizarPreviewHuesped() {
        if (!selectHuesped) return;
        const val = selectHuesped.value;
        if (!val) {
            if (cardPreviewHuesped) cardPreviewHuesped.classList.add('d-none');
            return;
        }

        // Consultar los datos directamente desde la instancia de TomSelect o el <option>
        let optData = null;
        if (selectHuesped.tomselect && selectHuesped.tomselect.options && selectHuesped.tomselect.options[val]) {
            optData = selectHuesped.tomselect.options[val];
        }

        const selectedOpt = selectHuesped.selectedIndex >= 0 ? selectHuesped.options[selectHuesped.selectedIndex] : null;
        const nombre = optData?.nombres || selectedOpt?.dataset?.nombres || selectedOpt?.text || '--';
        const tipoDoc = optData?.tipoDoc || selectedOpt?.dataset?.tipoDoc || 'DOC';
        const doc = optData?.doc || selectedOpt?.dataset?.doc || '--';
        const nac = optData?.nacionalidad || selectedOpt?.dataset?.nacionalidad || 'Perú';
        const tel = (optData?.telefono && optData.telefono !== 'No registrado') ? optData.telefono : (selectedOpt?.dataset?.telefono || 'Sin teléfono');
        const correo = (optData?.correo && optData.correo !== 'No registrado') ? optData.correo : (selectedOpt?.dataset?.correo || 'Sin correo');
        const estadiasPrevias = parseInt(optData?.totalReservas ?? selectedOpt?.dataset?.totalReservas ?? 0, 10);

        if (prevNombre) prevNombre.textContent = nombre;
        if (prevDoc) prevDoc.textContent = `${tipoDoc}: ${doc}`;
        if (prevNacionalidad) prevNacionalidad.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${nac}`;
        if (prevTel) prevTel.textContent = tel;
        if (prevCorreo) prevCorreo.textContent = correo;

        // Badge VIP / Frecuencia
        if (prevBadgeVIP) {
            if (estadiasPrevias >= 2) {
                prevBadgeVIP.innerHTML = `<span class="badge badge-soft-warning"><i class="bi bi-star-fill text-warning me-1"></i>Huésped VIP (${estadiasPrevias} visitas)</span>`;
            } else if (estadiasPrevias === 1) {
                prevBadgeVIP.innerHTML = `<span class="badge badge-soft-info"><i class="bi bi-person-check me-1"></i>Cliente Registrado (1 visita previa)</span>`;
            } else {
                prevBadgeVIP.innerHTML = `<span class="badge badge-soft-secondary"><i class="bi bi-person-plus me-1"></i>Primera Visita (Nuevo)</span>`;
            }
        }

        if (prevAvatar) {
            const iniciales = nombre.split(' ').filter(p => p.length > 0).slice(0, 2).map(p => p[0].toUpperCase()).join('');
            prevAvatar.innerHTML = iniciales || '<i class="bi bi-person"></i>';
        }

        if (cardPreviewHuesped) cardPreviewHuesped.classList.remove('d-none');
    }

    if (selectHuesped) {
        selectHuesped.addEventListener('change', actualizarPreviewHuesped);
        // Sincronizar evento de cambio nativo de TomSelect
        if (selectHuesped.tomselect) {
            selectHuesped.tomselect.on('change', actualizarPreviewHuesped);
        } else {
            // Reintento breve por si TomSelect inicializa asíncronamente
            setTimeout(() => {
                if (selectHuesped.tomselect) {
                    selectHuesped.tomselect.on('change', actualizarPreviewHuesped);
                }
            }, 100);
        }
        actualizarPreviewHuesped();
    }

    // -----------------------------------------------------------------
    // 7. FILTRADO MULTI-CRITERIO Y ORDENAMIENTO
    // -----------------------------------------------------------------
    function aplicarFiltros() {
        const txt = (inputTexto.value || '').trim().toLowerCase();

        if (btnLimpiar) {
            btnLimpiar.style.display = txt ? 'inline-block' : 'none';
        }

        let visibles = 0;
        habItems.forEach(item => {
            const habNum = item.dataset.numero || '';
            const habPiso = item.dataset.piso || '';
            const habTipo = item.dataset.tipo || '';
            const esOcupada = item.classList.contains('is-occupied');

            const coincideTexto = !txt || habNum.includes(txt) || habTipo.includes(txt) || `piso ${habPiso}`.includes(txt) || habPiso === txt;
            const coincideTipo = !filtroTipoActivo || habTipo === filtroTipoActivo;
            const coincidePiso = !filtroPisoActivo || habPiso === filtroPisoActivo;

            if (coincideTexto && coincideTipo && coincidePiso) {
                item.style.display = '';
                if (!esOcupada) visibles++;
            } else {
                item.style.display = 'none';
            }
        });

        if (numVisibles) numVisibles.textContent = visibles;
        if (noHabMsg) noHabMsg.style.display = (visibles === 0) ? 'block' : 'none';
    }

    chipsTipo.forEach(chip => {
        chip.addEventListener('click', function() {
            chipsTipo.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            filtroTipoActivo = (this.dataset.tipo || '').toLowerCase();
            aplicarFiltros();
        });
    });

    chipsPiso.forEach(chip => {
        chip.addEventListener('click', function() {
            chipsPiso.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            filtroPisoActivo = (this.dataset.piso || '').toLowerCase();
            aplicarFiltros();
        });
    });

    if (inputTexto) {
        inputTexto.addEventListener('input', aplicarFiltros);
        inputTexto.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                inputTexto.value = '';
                aplicarFiltros();
            }
        });
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            inputTexto.value = '';
            aplicarFiltros();
            inputTexto.focus();
        });
    }

    if (btnRestablecerFiltros) {
        btnRestablecerFiltros.addEventListener('click', function() {
            if (inputTexto) inputTexto.value = '';
            filtroTipoActivo = '';
            filtroPisoActivo = '';
            chipsTipo.forEach((c, idx) => c.classList.toggle('active', idx === 0));
            chipsPiso.forEach((c, idx) => c.classList.toggle('active', idx === 0));
            aplicarFiltros();
        });
    }

    if (selectOrdenar && contenedorHabitaciones) {
        selectOrdenar.addEventListener('change', function() {
            const criterio = this.value;
            const cards = habItems.slice();

            cards.sort((a, b) => {
                const numA = parseInt(a.dataset.numero, 10) || 0;
                const numB = parseInt(b.dataset.numero, 10) || 0;
                const precioA = parseFloat(a.dataset.precio) || 0;
                const precioB = parseFloat(b.dataset.precio) || 0;
                const pisoA = parseInt(a.dataset.piso, 10) || 0;
                const pisoB = parseInt(b.dataset.piso, 10) || 0;

                if (criterio === 'num-asc') return numA - numB;
                if (criterio === 'num-desc') return numB - numA;
                if (criterio === 'precio-asc') return precioA - precioB;
                if (criterio === 'precio-desc') return precioB - precioA;
                if (criterio === 'piso-asc') return (pisoA - pisoB) || (numA - numB);
                return 0;
            });

            cards.forEach(card => contenedorHabitaciones.appendChild(card));
            if (noHabMsg) contenedorHabitaciones.appendChild(noHabMsg);
        });
    }

    if (btnSeleccionarVisibles) {
        btnSeleccionarVisibles.addEventListener('click', function() {
            habItems.forEach(item => {
                if (item.style.display !== 'none' && !item.classList.contains('is-occupied')) {
                    const cb = item.querySelector('.check-hab');
                    if (cb) cb.checked = true;
                }
            });
            calcular();
        });
    }

    if (btnDeseleccionarTodas) {
        btnDeseleccionarTodas.addEventListener('click', function() {
            checkHabs.forEach(cb => cb.checked = false);
            calcular();
        });
    }

    // Clic en toda la tarjeta para activar selección (si no está ocupada)
    habItems.forEach(item => {
        const card = item.querySelector('.card-room-selectable');
        const cb = item.querySelector('.check-hab');
        if (card && cb) {
            card.addEventListener('click', function(e) {
                if (item.classList.contains('is-occupied')) return;
                if (e.target !== cb && !e.target.closest('input')) {
                    cb.checked = !cb.checked;
                    calcular();
                }
            });
        }
    });

    // Clic en tarjetas de servicios adicionales
    document.querySelectorAll('.service-check-card').forEach(card => {
        const cb = card.querySelector('.check-servicio');
        if (cb) {
            card.addEventListener('click', function(e) {
                if (e.target !== cb && !e.target.closest('input')) {
                    cb.checked = !cb.checked;
                    calcular();
                }
            });
            cb.addEventListener('change', calcular);
        }
    });

    // Control de fechas y disparador AJAX
    entradaInput.addEventListener('change', function() {
        if (salidaInput.value <= entradaInput.value) {
            const next = new Date(entradaInput.value + 'T00:00:00');
            next.setDate(next.getDate() + 1);
            salidaInput.value = next.toISOString().split('T')[0];
        }
        verificarDisponibilidadAjax();
    });

    salidaInput.addEventListener('change', function() {
        if (salidaInput.value <= entradaInput.value) {
            const prev = new Date(salidaInput.value + 'T00:00:00');
            prev.setDate(prev.getDate() - 1);
            entradaInput.value = prev.toISOString().split('T')[0];
        }
        verificarDisponibilidadAjax();
    });

    checkHabs.forEach(cb => cb.addEventListener('change', calcular));

    // Ejecutar verificación inicial de disponibilidad
    verificarDisponibilidadAjax();

    // Preseleccionar habitación si viene por parámetro GET
    const urlParams = new URLSearchParams(window.location.search);
    // Disponibilidad envía hab_id; Planificación y el panel, habitacion_id
    const habIdParam = urlParams.get('habitacion_id') || urlParams.get('hab_id');
    if (habIdParam) {
        const checkHab = document.getElementById('hab_' + habIdParam);
        if (checkHab && !checkHab.checked) {
            checkHab.checked = true;
            calcular();
            setTimeout(() => {
                checkHab.closest('.hab-card-item')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    }

    // Preseleccionar huésped si viene por parámetro GET
    const huespedIdParam = urlParams.get('huesped_id');
    if (huespedIdParam && selectHuesped) {
        setTimeout(() => {
            if (selectHuesped.tomselect) {
                selectHuesped.tomselect.setValue(String(huespedIdParam));
            } else {
                selectHuesped.value = huespedIdParam;
            }
            actualizarPreviewHuesped();
        }, 150);
    }

    // -------------------------------------------------------------
    // 8. MODAL RÁPIDO DE REGISTRO DE HUÉSPED
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

            fetch(appUrl('huespedes/apiGuardar'), {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

                if (selectHuesped) {
                    const h = data.huesped;
                    const nombreCompleto = `${h.nombres} ${h.apellidos}`.trim();
                    const opt = document.createElement('option');
                    opt.value = h.id;
                    opt.text = h.texto_select || `${nombreCompleto} (Doc: ${h.numero_documento})`;
                    opt.dataset.nombres = nombreCompleto;
                    opt.dataset.doc = h.numero_documento || '';
                    opt.dataset.tipoDoc = h.tipo_documento || 'DNI';
                    opt.dataset.telefono = h.telefono || 'No registrado';
                    opt.dataset.correo = h.correo || 'No registrado';
                    opt.dataset.nacionalidad = h.nacionalidad || 'Perú';
                    opt.dataset.totalReservas = '0';

                    selectHuesped.add(opt);
                    selectHuesped.value = h.id;

                    if (selectHuesped.tomselect) {
                        selectHuesped.tomselect.addOption({
                            value: String(h.id),
                            text: opt.text,
                            nombres: nombreCompleto,
                            doc: h.numero_documento || '',
                            tipoDoc: h.tipo_documento || 'DNI',
                            telefono: h.telefono || '',
                            correo: h.correo || '',
                            nacionalidad: h.nacionalidad || 'Perú',
                            totalReservas: 0
                        });
                        selectHuesped.tomselect.setValue(String(h.id));
                    }

                    actualizarPreviewHuesped();
                }

                formModalHuesped.reset();
                const modalInstance = bootstrap.Modal.getInstance(modalHuespedEl);
                if (modalInstance) modalInstance.hide();

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
            .catch(() => {
                btnGuardarModal.disabled = false;
                btnGuardarModal.innerHTML = originalBtnHtml;
                alertModalHuesped.textContent = 'Ocurrió un error inesperado al conectar con el servidor.';
                alertModalHuesped.classList.remove('d-none');
            });
        });
    }

    // -------------------------------------------------------------
    // 9. VALIDACIÓN PRE-ENVÍO DEL FORMULARIO PRINCIPAL
    // -------------------------------------------------------------
    const formReserva = document.getElementById('formReserva');
    if (formReserva) {
        formReserva.addEventListener('submit', function(e) {
            // 1. Validar Huésped
            const huespedVal = selectHuesped ? selectHuesped.value : '';
            if (!huespedVal) {
                e.preventDefault();
                e.stopPropagation();
                avisar('Huésped no seleccionado', 'Debes buscar o seleccionar un huésped registrado antes de confirmar la reserva.');
                if (selectHuesped.tomselect) {
                    selectHuesped.tomselect.focus();
                }
                return false;
            }

            // 2. Validar que haya al menos una habitación seleccionada
            const habsSeleccionadas = document.querySelectorAll('.check-hab:checked');
            if (habsSeleccionadas.length === 0) {
                e.preventDefault();
                e.stopPropagation();
                avisar('Ninguna habitación seleccionada', 'Debes elegir al menos una habitación disponible haciendo clic sobre su tarjeta.');
                document.getElementById('contenedorHabitaciones')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 3. Validar Fechas
            if (!entradaInput.value || !salidaInput.value || salidaInput.value <= entradaInput.value) {
                e.preventDefault();
                e.stopPropagation();
                avisar('Fechas de estadía inválidas', 'La fecha de check-out debe ser posterior a la fecha de check-in.');
                return false;
            }
        });
    }

    window.addEventListener('beforeunload', () => {
        document.body.classList.remove('pos-split-active');
    });
});
