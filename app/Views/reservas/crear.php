<script>
    document.body.classList.add('pos-split-active');
</script>

<?php partial('page_header', [
    'titulo'    => 'Registrar Nueva Reserva',
    'icono'     => 'bi-calendar-plus',
    'subtitulo' => 'Formulario tipo terminal POS con disponibilidad en tiempo real y extras',
    'acciones'  => [
        ['texto' => 'Volver a la Lista', 'url' => 'reservas', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content pos-split-wrapper">
    <div class="container-fluid">

        <form action="<?= url('reservas/guardar') ?>" method="POST" id="formReserva" class="pos-split-form">
            <div class="row g-3 pos-split-row">
                <!-- Columna Izquierda: Datos del Huésped, Fechas, Habitaciones y Servicios (Scroll Independiente) -->
                <div class="col-12 col-lg-7 col-xl-8 pos-split-left">
                    <!-- 1. Datos del Huésped -->
                    <div class="card mb-3 card-guest-select">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-circle text-primary me-2"></i>1. Seleccionar o Registrar Huésped
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoHuesped">
                                <i class="bi bi-person-plus me-1"></i> + Nuevo Huésped
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="huesped_id" class="form-label">Huésped Titular <span class="text-danger">*</span></label>
                                    <select name="huesped_id" id="huesped_id" class="form-select select-search select-huesped" required>
                                        <option value="" data-nombres="" data-doc="" data-tipo-doc="" data-telefono="" data-correo="" data-nacionalidad="" data-total-reservas="0">-- Escribe para buscar por nombre, documento o teléfono --</option>
                                        <?php foreach ($huespedes as $h): ?>
                                            <option value="<?= $h['id'] ?>"
                                                data-nombres="<?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?>"
                                                data-doc="<?= htmlspecialchars($h['numero_documento']) ?>"
                                                data-tipo-doc="<?= htmlspecialchars($h['tipo_documento']) ?>"
                                                data-telefono="<?= htmlspecialchars($h['telefono'] ?: 'No registrado') ?>"
                                                data-correo="<?= htmlspecialchars($h['correo'] ?: 'No registrado') ?>"
                                                data-nacionalidad="<?= htmlspecialchars($h['nacionalidad'] ?: 'No especificada') ?>"
                                                data-total-reservas="<?= (int)($h['total_reservas'] ?? 0) ?>"
                                                data-data='<?= json_encode([
                                                    "value" => (string)$h['id'],
                                                    "nombres" => $h['nombres'] . ' ' . $h['apellidos'],
                                                    "doc" => $h['numero_documento'],
                                                    "tipoDoc" => $h['tipo_documento'],
                                                    "telefono" => $h['telefono'] ?: '',
                                                    "correo" => $h['correo'] ?: '',
                                                    "nacionalidad" => $h['nacionalidad'] ?: 'Perú',
                                                    "totalReservas" => (int)($h['total_reservas'] ?? 0)
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                                <?= htmlspecialchars($h['nombres'] . ' ' . $h['apellidos']) ?> (Doc: <?= htmlspecialchars($h['tipo_documento'] . ' ' . $h['numero_documento']) ?> - Tel: <?= htmlspecialchars($h['telefono'] ?: 'S/N') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Búsqueda inteligente con ficha interactiva, avatar e insignias de cliente frecuente/VIP.</div>
                                </div>

                                <!-- Mini Preview Card del Huésped Seleccionado con Insignia VIP -->
                                <div class="col-12">
                                    <div id="cardPreviewHuesped" class="guest-preview-card d-none">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center shadow-xs avatar-md" id="prevAvatar">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <h6 class="mb-0 fw-bold text-body" id="prevNombre">--</h6>
                                                        <span id="prevBadgeVIP"></span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                                        <span class="badge badge-soft-secondary" id="prevDoc">DNI: --</span>
                                                        <span class="badge badge-soft-info" id="prevNacionalidad"><i class="bi bi-geo-alt me-1"></i>--</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-sm-end gap-1 small text-muted">
                                                <div><i class="bi bi-telephone text-primary me-1"></i><span id="prevTel" class="text-body fw-medium">--</span></div>
                                                <div><i class="bi bi-envelope text-primary me-1"></i><span id="prevCorreo" class="text-body fw-medium">--</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Fechas de Estadía y Huéspedes -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-range text-info me-2"></i>2. Fechas de Estadía y Ocupantes
                            </h5>
                            <span id="statusDisponibilidad" class="badge badge-soft-success d-none">
                                <span class="spinner-border spinner-border-sm me-1 spinner-xs"></span>Verificando disponibilidad...
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6 col-xl-4">
                                    <label for="fecha_entrada" class="form-label text-nowrap">Check-in <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_entrada" id="fecha_entrada" class="form-control" value="<?= e($entradaInicial) ?>" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-6 col-xl-4">
                                    <label for="fecha_salida" class="form-label text-nowrap">Check-out <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_salida" id="fecha_salida" class="form-control" value="<?= e($salidaInicial) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <label for="total_noches" class="form-label text-nowrap">Total noches</label>
                                    <input type="text" id="total_noches" class="form-control bg-body-secondary fw-bold text-center" value="1 noche" readonly>
                                </div>

                                <!-- Presets de Duración Rápida -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center flex-wrap gap-2 pt-1 pb-1">
                                        <span class="small text-muted fw-semibold me-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Duración sugerida:</span>
                                        <button type="button" class="btn btn-preset-noche active" data-noches="1">1 noche</button>
                                        <button type="button" class="btn btn-preset-noche" data-noches="2">2 noches</button>
                                        <button type="button" class="btn btn-preset-noche" data-noches="3">3 noches</button>
                                        <button type="button" class="btn btn-preset-noche" data-noches="finsemana">Fin de semana (Vie - Dom)</button>
                                        <button type="button" class="btn btn-preset-noche" data-noches="7">1 semana (7 noches)</button>
                                    </div>
                                </div>

                                <div class="col-6 col-md-4">
                                    <label for="numAdultos" class="form-label">Adultos <span class="text-danger">*</span></label>
                                    <input type="number" name="adultos" id="numAdultos" class="form-control" value="1" min="1" max="15">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label for="numNinos" class="form-label">Niños</label>
                                    <input type="number" name="ninos" id="numNinos" class="form-control" value="0" min="0" max="10">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="origen" class="form-label">Canal / Origen</label>
                                    <select id="origen" name="origen" class="form-select">
                                        <option value="manual">Manual (Recepción)</option>
                                        <option value="telefonica">Telefónica</option>
                                        <option value="web">Página Web</option>
                                    </select>
                                </div>

                                <!-- Early Check-in & Late Check-out -->
                                <div class="col-12 pt-2 border-top">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="early_checkin" id="early_checkin" value="1">
                                                <label class="form-check-label small fw-semibold cursor-pointer" for="early_checkin">
                                                    <i class="bi bi-sunrise text-warning me-1"></i>Solicitar Early Check-in (antes de las 14:00)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="late_checkout" id="late_checkout" value="1">
                                                <label class="form-check-label small fw-semibold cursor-pointer" for="late_checkout">
                                                    <i class="bi bi-sunset text-info me-1"></i>Solicitar Late Check-out (después de las 12:00)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
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

                        $conteoPorTipo = [];
                        foreach ($habitaciones as $h) {
                            $t = $h['tipo_nombre'] ?? 'Sin tipo';
                            $conteoPorTipo[$t] = ($conteoPorTipo[$t] ?? 0) + 1;
                        }
                    ?>
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-door-open-fill text-warning me-2"></i>3. Seleccionar Habitación(es) <span class="text-danger">*</span>
                                </h5>
                                <small class="text-muted">Elige una o varias habitaciones disponibles en catálogo</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-soft-primary" id="contadorHabitacionesVisibles">
                                    <i class="bi bi-door-closed me-1"></i><span id="numVisibles"><?= count($habitaciones) ?></span> disponibles
                                </span>
                                <span class="badge badge-soft-success" id="contadorHabitacionesSeleccionadas">
                                    <i class="bi bi-check-circle me-1"></i><span id="numSeleccionadas">0</span> seleccionadas
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Barra de Búsqueda y Filtros en Tiempo Real -->
                            <div class="p-3 hab-filter-panel rounded-3 mb-3">
                                <div class="row g-2 align-items-center">
                                    <!-- Input de Búsqueda Rápida -->
                                    <div class="col-12 col-md-7">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text border-end-0"><i class="bi bi-search text-muted"></i></span>
                                            <input type="text" id="filtroHabitacionTexto" class="form-control form-control-sm border-start-0" placeholder="Buscar por número (ej: 101), tipo o piso...">
                                            <button type="button" class="btn btn-outline-secondary border border-start-0" id="btnLimpiarBuscar" title="Limpiar búsqueda" style="display: none;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Selector de Ordenamiento -->
                                    <div class="col-12 col-md-5">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-sort-down text-muted"></i></span>
                                            <select id="ordenarHabitaciones" class="form-select form-select-sm">
                                                <option value="num-asc">Número (Menor a Mayor)</option>
                                                <option value="num-desc">Número (Mayor a Menor)</option>
                                                <option value="precio-asc">Precio: Más Económico</option>
                                                <option value="precio-desc">Precio: Más Exclusivo</option>
                                                <option value="piso-asc">Por Piso (1 a 5)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Chips de Filtro por Tipo de Habitación -->
                                    <div class="col-12 mt-2">
                                        <div class="small fw-semibold text-muted mb-1"><i class="bi bi-tag me-1"></i>Filtrar por Categoría:</div>
                                        <div class="filter-chip-container" id="chipsFiltroTipo">
                                            <button type="button" class="btn-chip-filter active" data-tipo="">
                                                Todos <span class="badge badge-soft-secondary ms-1"><?= count($habitaciones) ?></span>
                                            </button>
                                            <?php foreach ($tiposHab as $t): ?>
                                                <button type="button" class="btn-chip-filter" data-tipo="<?= htmlspecialchars(strtolower($t)) ?>">
                                                    <?= htmlspecialchars($t) ?>
                                                    <span class="badge badge-soft-secondary ms-1"><?= $conteoPorTipo[$t] ?? 0 ?></span>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Chips de Filtro por Piso -->
                                    <div class="col-12 mt-2">
                                        <div class="small fw-semibold text-muted mb-1"><i class="bi bi-layers me-1"></i>Filtrar por Piso:</div>
                                        <div class="filter-chip-container" id="chipsFiltroPiso">
                                            <button type="button" class="btn-chip-filter active" data-piso="">
                                                Todos los Pisos
                                            </button>
                                            <?php foreach ($pisos as $p): ?>
                                                <button type="button" class="btn-chip-filter" data-piso="<?= htmlspecialchars(strtolower($p)) ?>">
                                                    Piso <?= htmlspecialchars($p) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Barra de selección rápida múltiple -->
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle flex-wrap gap-2">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Haz clic en cualquier tarjeta para seleccionarla</small>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="text-sm btn btn-outline-primary btn-sm" id="btnSeleccionarVisibles">
                                            <i class="bi bi-check-all me-1"></i> Seleccionar visibles
                                        </button>
                                        <button type="button" class="text-sm btn btn-outline-secondary btn-sm" id="btnDeseleccionarTodas">
                                            <i class="bi bi-x-circle me-1"></i> Deseleccionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aquí aparece el error "elige una habitación" (las casillas están ocultas) -->
                            <div data-error-para="habitaciones"></div>

                            <!-- Grilla de Habitaciones -->
                            <div class="row g-3" id="contenedorHabitaciones">
                                <?php foreach ($habitaciones as $hab): ?>
                                    <div class="col-12 col-md-6 col-xl-4 hab-card-item" 
                                         id="item_hab_<?= $hab['id'] ?>"
                                         data-id="<?= $hab['id'] ?>"
                                         data-numero="<?= htmlspecialchars(strtolower($hab['numero'])) ?>"
                                         data-piso="<?= htmlspecialchars(strtolower($hab['piso'])) ?>"
                                         data-tipo="<?= htmlspecialchars(strtolower($hab['tipo_nombre'])) ?>"
                                         data-precio="<?= $hab['precio_diario'] ?>"
                                         data-capacidad="<?= (int)($hab['capacidad'] ?? 2) ?>">
                                        <div class="card-room-selectable h-100 shadow-xs position-relative">
                                            <!-- Badge de no disponible (manejado vía AJAX) -->
                                            <span class="badge bg-danger position-absolute top-0 end-0 m-2 d-none badge-ocupada-ajax text-2xs z-2">
                                                <i class="bi bi-x-circle me-1"></i>Ocupada
                                            </span>

                                            <div class="d-flex align-items-start gap-3">
                                                <!-- Indicador Circular de Check -->
                                                <div class="pt-1">
                                                    <span class="check-indicator"><i class="bi bi-check-lg"></i></span>
                                                    <input class="form-check-input check-hab d-none" type="checkbox" name="habitaciones[]" value="<?= $hab['id'] ?>" id="hab_<?= $hab['id'] ?>" data-precio="<?= $hab['precio_diario'] ?>" data-numero="<?= htmlspecialchars($hab['numero']) ?>" data-capacidad="<?= (int)($hab['capacidad'] ?? 2) ?>">
                                                </div>
                                                
                                                <!-- Foto de Habitación con Zoom -->
                                                <div class="hab-img-container">
                                                    <img src="<?= e(foto_habitacion($hab['foto'] ?? null, true)) ?>" alt="Hab. <?= e($hab['numero']) ?>" class="hab-img-thumb border shadow-xs" width="68" height="68" loading="lazy" decoding="async">
                                                </div>

                                                <!-- Detalles de la Habitación -->
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <strong class="fs-5 text-primary">Hab. <?= htmlspecialchars($hab['numero']) ?></strong>
                                                        <span class="room-floor-badge">Piso <?= htmlspecialchars($hab['piso']) ?></span>
                                                    </div>
                                                    <div class="text-muted small fw-semibold text-truncate">
                                                        <?= htmlspecialchars($hab['tipo_nombre']) ?> • <span class="text-secondary"><i class="bi bi-person me-1"></i><?= $hab['capacidad'] ?? 2 ?> pers.</span>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light-subtle">
                                                        <div>
                                                            <span class="fw-bold text-success fs-6"><?= dinero($hab['precio_diario']) ?></span>
                                                            <small class="text-xs text-muted">/ noche</small>
                                                        </div>
                                                        <div class="text-end">
                                                            <span class="badge badge-soft-primary precio-estadia-hab text-2xs" style="display: none;"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Mensaje cuando la búsqueda no arroja resultados -->
                                <div id="noHabitacionesMsg" class="col-12 text-center py-5 text-muted" style="display: none;">
                                    <i class="bi bi-search fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <h6 class="fw-bold text-body">No se encontraron habitaciones</h6>
                                    <p class="mb-2 text-muted small">No hay habitaciones disponibles que coincidan con los filtros aplicados.</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnRestablecerFiltros">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Restablecer todos los filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Servicios Adicionales & Extras (Opcional) -->
                    <?php if (!empty($servicios)): ?>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-plus-circle text-success me-2"></i>4. Servicios Adicionales & Paquetes (Opcional)
                                    </h5>
                                    <small class="text-muted">Añade comodidades y extras a la estadía del huésped</small>
                                </div>
                                <span class="badge badge-soft-success" id="badgeTotalServicios">0 extras seleccionados</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <?php foreach ($servicios as $s): ?>
                                        <div class="col-12 col-md-6">
                                            <div class="service-check-card h-100" id="card_srv_<?= $s['id'] ?>">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="form-check mb-0 flex-grow-1 me-2">
                                                        <input class="form-check-input check-servicio" type="checkbox" name="servicios[<?= $s['id'] ?>]" id="srv_<?= $s['id'] ?>" value="1" data-precio="<?= $s['precio'] ?>" data-nombre="<?= htmlspecialchars($s['nombre']) ?>">
                                                        <label class="form-check-label fw-bold cursor-pointer text-body small" for="srv_<?= $s['id'] ?>">
                                                            <?= htmlspecialchars($s['nombre']) ?>
                                                        </label>
                                                        <small class="text-xs text-muted d-block">
                                                            <?= htmlspecialchars($s['categoria']) ?> • <?= htmlspecialchars(mb_strimwidth($s['descripcion'] ?? '', 0, 48, '...')) ?>
                                                        </small>
                                                    </div>
                                                    <div class="text-end flex-shrink-0">
                                                        <span class="fw-bold text-success fs-6"><?= dinero($s['precio']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Columna Derecha: Resumen de Costo, Capacidad y Facturación (Panel Fijo con Scroll Interno) -->
                <div class="col-12 col-lg-5 col-xl-4 pos-split-right">
                    <div class="card pos-split-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fs-6">
                                <i class="bi bi-receipt-cutoff me-2"></i>Resumen y Cobro
                            </h5>
                            <span class="badge badge-soft-primary" id="badgeTotalNochesResumen">1 noche</span>
                        </div>
                        <div class="card-body pos-split-card-body">
                            <!-- Fechas Programadas -->
                            <div class="mb-3 p-2 resumen-fechas-box rounded-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted"><i class="bi bi-box-arrow-in-right text-success me-1"></i>Check-in:</span>
                                    <strong id="resumenFechaEntrada"><?= fecha_es($entradaInicial) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted"><i class="bi bi-box-arrow-right text-danger me-1"></i>Check-out:</span>
                                    <strong id="resumenFechaSalida"><?= fecha_es($salidaInicial) ?></strong>
                                </div>
                            </div>

                            <!-- Semáforo de Capacidad Ocupantes vs Habitaciones -->
                            <div id="alertaCapacidad" class="alert alert-secondary py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill fs-5" id="iconoCapacidad"></i>
                                <span id="textoCapacidad">Selecciona habitaciones para cubrir 1 persona</span>
                            </div>

                            <!-- Habitaciones Seleccionadas en Tags Removibles -->
                            <div class="mb-3">
                                <div class="form-label d-flex justify-content-between">
                                    <span>Habitaciones:</span>
                                    <span id="contadorHabTag" class="badge badge-soft-primary">0</span>
                                </div>
                                <div id="listaHabitacionesTags" class="d-flex flex-wrap gap-1">
                                    <span class="text-muted small fst-italic py-1" id="sinHabitacionesMsg">Ninguna habitación seleccionada</span>
                                </div>
                            </div>

                            <!-- Servicios Adicionales en Tags -->
                            <div class="mb-3" id="bloqueServiciosTags" style="display: none;">
                                <div class="form-label d-flex justify-content-between">
                                    <span>Extras Contratados:</span>
                                    <span id="contadorSrvTag" class="badge badge-soft-success">0</span>
                                </div>
                                <div id="listaServiciosTags" class="d-flex flex-wrap gap-1"></div>
                            </div>

                            <!-- Acordeón Desplegable para Descuentos / Tarifas Especiales -->
                            <div class="mb-3">
                                <a class="small text-decoration-none fw-semibold d-inline-flex align-items-center gap-1 text-primary" data-bs-toggle="collapse" href="#boxDescuento" role="button">
                                    <i class="bi bi-tag-fill"></i> ¿Aplicar descuento o tarifa corporativa?
                                </a>
                                <div class="collapse mt-2" id="boxDescuento">
                                    <div class="p-2 bg-body-tertiary rounded-3 border">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label for="inputDescuento" class="form-label mb-1">Descuento (S/.)</label>
                                                <input type="number" step="1.00" min="0" name="descuento" id="inputDescuento" class="form-control form-control-sm text-danger fw-bold" value="0.00">
                                            </div>
                                            <div class="col-6">
                                                <label for="motivoDescuento" class="form-label mb-1">Motivo</label>
                                                <input type="text" name="motivo_descuento" id="motivoDescuento" class="form-control form-control-sm" placeholder="Ej: Corporativo, VIP">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- Desglose Económico -->
                            <div class="small mb-2 text-muted">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Hospedaje:</span>
                                    <span id="subtotalHospedajeDisplay" class="fw-semibold">S/. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1" id="filaServiciosSubtotal" style="display: none;">
                                    <span>Servicios Extras:</span>
                                    <span id="subtotalServiciosDisplay" class="fw-semibold text-success">+S/. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-danger" id="filaDescuentoSubtotal" style="display: none;">
                                    <span>Descuento aplicado:</span>
                                    <span id="descuentoDisplay" class="fw-bold">-S/. 0.00</span>
                                </div>
                            </div>

                            <!-- Botones de Porcentaje de Anticipo Rápido -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="inputAnticipo" class="form-label mb-0">Anticipo / Adelanto (S/.)</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-outline-secondary btn-anticipo-preset" data-pct="0">0%</button>
                                        <button type="button" class="btn btn-outline-secondary btn-anticipo-preset" data-pct="30">30%</button>
                                        <button type="button" class="btn btn-outline-secondary btn-anticipo-preset" data-pct="50">50%</button>
                                        <button type="button" class="btn btn-outline-primary btn-anticipo-preset" data-pct="100">100%</button>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary fw-semibold">S/.</span>
                                    <input type="number" step="0.01" name="anticipo" id="inputAnticipo" class="form-control form-control-lg fw-bold text-primary" value="0.00" min="0">
                                </div>
                                <div class="form-text small">El anticipo genera un recibo de caja de ingreso inmediato.</div>
                            </div>

                            <!-- Alerta Dinámica de Saldo Restante -->
                            <div id="boxAlertaSaldo" class="alert alert-secondary py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill fs-5" id="iconoSaldo"></i>
                                <span id="textoSaldo">Selecciona al menos una habitación para calcular el cobro.</span>
                            </div>

                            <div class="mb-3">
                                <label for="metodo_pago" class="form-label">Método de Pago del Anticipo</label>
                                <select id="metodo_pago" name="metodo_pago" class="form-select form-select-sm">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                    <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                    <option value="Yape/Plin">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="selectEstado" class="form-label">Estado de la Reserva</label>
                                <select name="estado" id="selectEstado" class="form-select form-select-sm">
                                    <option value="confirmada" selected>Confirmada</option>
                                    <option value="pendiente">Pendiente de pago</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label for="notas" class="form-label">Notas / Requerimientos</label>
                                <textarea id="notas" name="notas" class="form-control form-control-sm" rows="2" placeholder="Ej: Cama adicional, llegada tardía, vista exterior..."></textarea>
                            </div>
                        </div>

                        <!-- Footer Fijo de Cobro y Confirmación (Siempre Visible tipo Terminal POS) -->
                        <div class="card-footer pos-split-card-footer">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="fs-6 fw-bold d-block text-body">Monto Total:</span>
                                    <small class="text-muted" id="subtotalDetalle">0 hab. × 1 noche</small>
                                </div>
                                <span class="fs-3 fw-bolder text-primary" id="montoTotalDisplay">S/. 0.00</span>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="btnSubmitReserva">
                                    <i class="bi bi-check-circle-fill me-2"></i>Confirmar Reserva
                                </button>
                                <div class="text-center">
                                    <a href="<?= url('reservas') ?>" class="text-muted small text-decoration-none">
                                        <i class="bi bi-arrow-left me-1"></i>Cancelar y volver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- MODAL: REGISTRO RÁPIDO DE HUÉSPED (POPUP SIN RECARGAR PÁGINA) -->
<div class="modal fade" id="modalNuevoHuesped" tabindex="-1" aria-labelledby="modalNuevoHuespedLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoHuespedLabel">
                    <i class="bi bi-person-plus-fill me-2 text-primary"></i>Registrar Nuevo Huésped
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formModalHuesped">
                <div class="modal-body p-4">
                    <div class="alert alert-danger d-none py-2 px-3 small" id="alertModalHuesped" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label for="tipo_documento" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                            <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                                <option value="DNI" selected>DNI (Documento Nacional)</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Carnet Extranjeria">Carné de Extranjería</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="numero_documento" class="form-label">Número de Documento <span class="text-danger">*</span></label>
                            <input id="numero_documento" type="text" name="numero_documento" class="form-control" placeholder="Ej: 71829341" required autofocus>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input id="nombres" type="text" name="nombres" class="form-control" placeholder="Ej: Juan Carlos" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input id="apellidos" type="text" name="apellidos" class="form-control" placeholder="Ej: Pérez Rodríguez" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="telefono" class="form-label">Teléfono / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input id="telefono" type="tel" name="telefono" class="form-control" placeholder="+51 987 654 321">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input id="correo" type="email" name="correo" class="form-control" placeholder="cliente@correo.com">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="nacionalidad" class="form-label">Nacionalidad</label>
                            <input id="nacionalidad" type="text" name="nacionalidad" class="form-control" value="Perú" placeholder="Ej: Perú, Chile, Argentina">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="direccion" class="form-label">Dirección / Procedencia</label>
                            <input id="direccion" type="text" name="direccion" class="form-control" placeholder="Ciudad o dirección de origen">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarHuespedModal">
                        <i class="bi bi-check-circle me-1"></i> Guardar y Seleccionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
    let habitacionesOcupadasSet = new Set();

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
                    badgeEstadia.textContent = `S/. ${totalEstadiaHab.toFixed(2)} (${noches} nts)`;
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
                        <i class="bi bi-check2 me-1"></i>${nombre} (S/. ${precio.toFixed(2)})
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
            subtotalServiciosDisplay.textContent = `+S/. ${subtotalServicios.toFixed(2)}`;
        } else {
            bloqueSrvTags.style.display = 'none';
            filaServiciosSubtotal.style.display = 'none';
        }

        // Fila de descuento
        if (descuento > 0) {
            filaDescuentoSubtotal.style.display = 'flex';
            descuentoDisplay.textContent = `-S/. ${descuento.toFixed(2)}`;
        } else {
            filaDescuentoSubtotal.style.display = 'none';
        }

        if (subtotalHospedajeDisplay) subtotalHospedajeDisplay.textContent = `S/. ${subtotalHabitaciones.toFixed(2)}`;
        if (subtotalDetalle) subtotalDetalle.textContent = `${countSelected} hab. × ${noches} ${noches === 1 ? 'noche' : 'noches'}`;
        if (montoTotalDisplay) montoTotalDisplay.textContent = 'S/. ' + totalNeto.toFixed(2);

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

        fetch(`<?= url("reservas/apiDisponibilidad") ?>?entrada=${entradaInput.value}&salida=${salidaInput.value}`)
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
            textoSaldo.innerHTML = `Anticipo abonado: <strong>S/. ${anticipo.toFixed(2)}</strong>. Saldo a cobrar en Check-in: <strong class="text-danger">S/. ${saldoRestante.toFixed(2)}</strong>`;
        } else {
            boxAlertaSaldo.className = 'alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-info-subtle';
            iconoSaldo.className = 'bi bi-wallet2 fs-5 text-info';
            textoSaldo.innerHTML = `Sin anticipo. Se cobrará la totalidad de <strong class="text-body">S/. ${total.toFixed(2)}</strong> en el Check-in.`;
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

            fetch('<?= url("huespedes/apiGuardar") ?>', {
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
</script>
