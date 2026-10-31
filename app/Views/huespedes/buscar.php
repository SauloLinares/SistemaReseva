<?php partial('page_header', [
    'titulo'    => 'Terminal de Consulta Rápida de Huéspedes',
    'icono'     => 'bi-person-bounding-box',
    'subtitulo' => 'Búsqueda directa por DNI o pasaporte para verificación en mostrador, check-in y reservas express',
    'acciones'  => [
        ['texto' => 'Nuevo Huésped', 'url' => 'huespedes/crear', 'icono' => 'bi-person-plus'],
        ['texto' => 'Directorio Completo', 'url' => 'huespedes', 'icono' => 'bi-card-list', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">

                <!-- Buscador Principal de Ventanilla -->
                <div class="card border-top border-4 border-primary mb-4">
                    <div class="card-body p-4">
                        <form method="GET" action="<?= url('huespedes/buscar') ?>" id="formBuscarHuesped">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <label class="form-label fs-5 text-body-emphasis mb-0" for="inputDocHuesped">
                                    <i class="bi bi-person-vcard text-primary me-2"></i>Ingresa el número de DNI, Pasaporte o Carnet:
                                </label>
                                <span class="badge badge-soft-info py-1 px-2">
                                    <i class="bi bi-keyboard me-1"></i>Atajo rápido: <kbd class="bg-body-secondary text-body border ms-1">F3</kbd>
                                </span>
                            </div>

                            <div class="input-group input-group-lg mb-2 shadow-xs">
                                <span class="input-group-text text-muted border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="documento" id="inputDocHuesped" class="form-control border-start-0" 
                                       placeholder="Ej: 71829341 ó PAS902188" 
                                       value="<?= htmlspecialchars($documento) ?>" 
                                       autocomplete="off" autofocus required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Consultar Huésped
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted">
                                <span><i class="bi bi-info-circle me-1"></i>Consulta instantánea en base de datos de <?= $totalHuespedes ?? 0 ?> huéspedes registrados.</span>
                                <?php if (!empty($documento)): ?>
                                    <a href="<?= url('huespedes/buscar') ?>" class="text-decoration-none">
                                        <i class="bi bi-x-circle me-1"></i>Limpiar búsqueda
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Resultado de la Búsqueda -->
                <?php if (!empty($documento)): ?>
                    <?php if ($huesped): ?>
                        <!-- Ficha de Huésped Encontrado -->
                        <?php 
                            $iniciales = mb_strtoupper(mb_substr($huesped['nombres'], 0, 1) . mb_substr($huesped['apellidos'], 0, 1));
                        ?>
                        <div class="card border-success mb-4">
                            <div class="card-header bg-success-subtle text-success-emphasis d-flex justify-content-between align-items-center py-2.5">
                                <span class="fw-bold fs-6"><i class="bi bi-check-circle-fill me-2"></i>Huésped Registrado en el Sistema</span>
                                <span class="badge bg-success">Cliente Activo</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-center g-3">
                                    <div class="col-auto">
                                        <div class="avatar-circle avatar-primary fs-3 shadow-xs" style="width: 75px; height: 75px;">
                                            <?= $iniciales ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md">
                                        <h4 class="fw-bold mb-1 text-body-emphasis"><?= htmlspecialchars($huesped['nombres'] . ' ' . $huesped['apellidos']) ?></h4>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <span class="badge bg-body-secondary text-body border">
                                                <i class="bi bi-person-vcard text-primary me-1"></i><?= htmlspecialchars($huesped['tipo_documento']) ?>: <strong><?= htmlspecialchars($huesped['numero_documento']) ?></strong>
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($huesped['nacionalidad'] ?: 'Perú') ?>
                                            </span>
                                        </div>
                                        <div class="row g-2 small pt-1 border-top border-light-subtle">
                                            <div class="col-12 col-sm-4 text-body-secondary">
                                                <i class="bi bi-telephone text-primary me-1"></i> Tel: <strong class="text-body"><?= htmlspecialchars($huesped['telefono'] ?: 'No registrado') ?></strong>
                                            </div>
                                            <div class="col-12 col-sm-4 text-body-secondary">
                                                <i class="bi bi-envelope text-primary me-1"></i> Correo: <strong class="text-body"><?= htmlspecialchars($huesped['correo'] ?: 'No registrado') ?></strong>
                                            </div>
                                            <div class="col-12 col-sm-4 text-body-secondary">
                                                <i class="bi bi-pin-map text-primary me-1"></i> Ciudad/Dirección: <strong class="text-body"><?= htmlspecialchars($huesped['direccion'] ?: 'No registrada') ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2 py-2.5">
                                <div class="small text-muted">
                                    <i class="bi bi-lightning-charge text-warning me-1"></i>Acciones inmediatas de recepción:
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= url('huespedes/historial/' . $huesped['id']) ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-clock-history me-1"></i> Ver Historial de Estadías
                                    </a>
                                    <a href="<?= url('huespedes/editar/' . $huesped['id']) ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pencil-square me-1"></i> Editar Ficha
                                    </a>
                                    <a href="<?= url('reservas/crear?huesped_id=' . $huesped['id']) ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-calendar-plus me-1"></i> + Crear Nueva Reserva
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Huésped No Encontrado -->
                        <div class="card border-warning mb-4 p-4 text-center">
                            <div class="avatar-circle avatar-warning mx-auto mb-3" style="width: 65px; height: 65px; font-size: 1.75rem;">
                                <i class="bi bi-person-x"></i>
                            </div>
                            <h5 class="fw-bold mb-1">No se encontró ningún huésped con el documento: "<?= htmlspecialchars($documento) ?>"</h5>
                            <p class="text-muted small mb-3">Este cliente aún no está registrado en el hotel. Puedes darlo de alta en menos de un minuto con el número de documento ya precargado.</p>
                            <div>
                                <a href="<?= url('huespedes/crear?num_doc=' . urlencode($documento)) ?>" class="btn btn-success">
                                    <i class="bi bi-person-plus me-1"></i> Registrar como Nuevo Huésped
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Sección: Huéspedes Habituales / VIP (Acceso Rápido en Mostrador) -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1 d-block">
                                <i class="bi bi-star-fill text-warning me-2"></i>Huéspedes Frecuentes / Clientes Registrados
                            </h6>
                            <small class="text-muted d-block">Acceso rápido de un clic para consultar fichas frecuentes</small>
                        </div>
                        <span class="badge badge-soft-warning">Acceso Directo</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <?php if (empty($frecuentes)): ?>
                                <div class="col-12 text-center text-muted py-3">No hay registros disponibles.</div>
                            <?php else: ?>
                                <?php foreach ($frecuentes as $hf): ?>
                                    <?php 
                                        $iniHf = mb_strtoupper(mb_substr($hf['nombres'], 0, 1) . mb_substr($hf['apellidos'], 0, 1));
                                        $totalRes = (int)($hf['total_reservas'] ?? 0);
                                        $esVip = $totalRes >= 2;
                                    ?>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div class="card-frecuente-item p-3 rounded-3 border h-100 d-flex flex-column justify-content-between shadow-xs">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.82rem; background: <?= $esVip ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #3b82f6, #1d4ed8)' ?>;">
                                                            <?= $iniHf ?>
                                                        </div>
                                                        <h6 class="fw-bold mb-0 text-truncate text-body" style="max-width: 145px;" title="<?= htmlspecialchars($hf['nombres'] . ' ' . $hf['apellidos']) ?>">
                                                            <?= htmlspecialchars($hf['nombres'] . ' ' . $hf['apellidos']) ?>
                                                        </h6>
                                                    </div>
                                                    <span class="badge <?= $esVip ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?>" style="font-size: 0.72rem;">
                                                        <?= $totalRes ?> <?= $totalRes === 1 ? 'visita' : 'visitas' ?>
                                                    </span>
                                                </div>
                                                <div class="small text-muted mb-2 font-monospace">
                                                    <?= htmlspecialchars($hf['tipo_documento']) ?>: <strong class="text-body"><?= htmlspecialchars($hf['numero_documento']) ?></strong>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                                                <span class="text-secondary small">
                                                    <i class="bi bi-geo-alt me-1 text-info"></i><?= htmlspecialchars($hf['nacionalidad'] ?: 'Perú') ?>
                                                </span>
                                                <a href="<?= url('huespedes/buscar?documento=' . urlencode($hf['numero_documento'])) ?>" class="btn btn-outline-primary btn-xs" style="font-size: 0.75rem;">
                                                    <i class="bi bi-search me-1"></i>Consultar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Protocolo de Atención en Recepción (Guía Operativa) -->
                <div class="card bg-body-tertiary border shadow-xs">
                    <div class="card-body py-3">
                        <div class="row align-items-center g-3 small">
                            <div class="col-12 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-check-fill text-success fs-4 flex-shrink-0"></i>
                                    <div>
                                        <strong class="text-body-emphasis d-block">1. Verificación de Identidad</strong>
                                        <span class="text-body-secondary">Solicita DNI o Pasaporte antes de confirmar la asignación de habitación.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-clock-history text-primary fs-4 flex-shrink-0"></i>
                                    <div>
                                        <strong class="text-body-emphasis d-block">2. Historial de Visitas</strong>
                                        <span class="text-body-secondary">Revisa estadías anteriores para consultar sus habitaciones preferidas.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-lightning-charge-fill text-warning fs-4 flex-shrink-0"></i>
                                    <div>
                                        <strong class="text-body-emphasis d-block">3. Reserva en 1 Clic</strong>
                                        <span class="text-body-secondary">Al presionar "+ Crear Nueva Reserva", todos los datos se autocompletan.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
