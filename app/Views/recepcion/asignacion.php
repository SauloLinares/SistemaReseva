<?php partial('page_header', [
    'titulo'    => 'Asignación y Cambio de Habitación',
    'icono'     => 'bi-arrow-left-right',
    'subtitulo' => 'Reubicación de huéspedes en estadía a nuevas habitaciones disponibles',
    'acciones'  => [
        ['texto' => 'Control de Estadías', 'url' => 'recepcion/estadias', 'icono' => 'bi-arrow-left', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-shuffle me-2"></i>Mover Huésped a Otra Habitación</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('recepcion/procesarCambio') ?>">
                            <div class="mb-3">
                                <label for="estancia_id" class="form-label">1. Seleccionar Huésped y Habitación Actual <span class="text-danger">*</span></label>
                                <select id="estancia_id" name="estancia_id" class="form-select form-select-lg" required>
                                    <option value="">-- Elige la habitación a desocupar --</option>
                                    <?php foreach ($activas as $act): ?>
                                        <option value="<?= $act['id'] ?>">
                                            Hab. <?= htmlspecialchars($act['habitacion_numero']) ?> (Piso <?= $act['piso'] ?> - <?= htmlspecialchars($act['tipo_nombre']) ?>) - Huésped: <?= htmlspecialchars($act['huesped_nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="nueva_habitacion_id" class="form-label">2. Seleccionar Nueva Habitación de Destino (Disponible) <span class="text-danger">*</span></label>
                                <select id="nueva_habitacion_id" name="nueva_habitacion_id" class="form-select form-select-lg" required>
                                    <option value="">-- Elige la nueva habitación disponible --</option>
                                    <?php foreach ($disponibles as $d): ?>
                                        <option value="<?= $d['id'] ?>">
                                            Hab. <?= htmlspecialchars($d['numero']) ?> - <?= htmlspecialchars($d['tipo_nombre']) ?> (Piso <?= $d['piso'] ?> - <?= dinero($d['precio_diario']) ?>/noche)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="motivo" class="form-label">Motivo del Cambio o Reubicación</label>
                                <textarea id="motivo" name="motivo" class="form-control" rows="2" placeholder="Ej: Falla en aire acondicionado, ascenso a suite, requerimiento de piso bajo..."></textarea>
                            </div>

                            <div class="alert alert-secondary py-2 small d-flex align-items-center">
                                <i class="bi bi-info-circle me-2 fs-5"></i>
                                <div>La habitación anterior cambiará automáticamente a estado <strong>LIMPIEZA</strong> para que el personal de servicio la acondicione de inmediato.</div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-left-right me-2"></i>Ejecutar Reubicación de Habitación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
