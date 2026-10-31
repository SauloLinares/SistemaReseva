<?= script_js('vistas/reservas-crear.js') ?>

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

        <form action="<?= url('reservas/guardar') ?>" method="POST" id="formReserva" class="pos-split-form" data-borrador>
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


