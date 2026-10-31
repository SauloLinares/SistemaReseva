<?php partial('page_header', [
    'titulo'    => 'Gestión de Caja y Turnos',
    'icono'     => 'bi-calculator',
    'subtitulo' => 'Apertura de turno, control de efectivo y arqueo diario',
    'acciones'  => [
        ['texto' => 'Ver Historial de Cierres', 'url' => 'caja/historial', 'icono' => 'bi-clock-history', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Pantalla de Estado: No hay turno abierto -->
        <div class="row justify-content-center py-4">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card border-0">
                    <div class="card-header py-3 text-center">
                        <i class="bi bi-unlock fs-2 d-block mb-1 text-success"></i>
                        <h5 class="card-title float-none mb-0">Apertura de Turno de Recepción</h5>
                        <small class="d-block text-body-secondary">Inicia tu jornada registrando el fondo inicial de caja</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check fs-4 text-info flex-shrink-0"></i>
                            <div>
                                <strong>Turno cerrado actualmente.</strong> Para registrar cobros en efectivo o emitir recibos de caja, debes iniciar tu sesión de turno.
                            </div>
                        </div>

                        <form action="<?= url('caja/abrir') ?>" method="POST" id="formAperturaCaja">
                            <!-- Datos del recepcionista -->
                            <div class="mb-3">
                                <label for="recepcionistaApertura" class="form-label">Recepcionista Responsable</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" id="recepcionistaApertura" class="form-control bg-body-secondary fw-semibold" value="<?= htmlspecialchars($usuario['nombre'] ?? 'Administrador') ?> (<?= htmlspecialchars($usuario['email'] ?? 'Recepcion') ?>)" readonly>
                                </div>
                            </div>

                            <!-- Fecha y Hora -->
                            <div class="mb-3">
                                <label for="fechaApertura" class="form-label">Fecha y Hora de Apertura</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                    <input type="text" id="fechaApertura" class="form-control bg-body-secondary" value="<?= date('d/m/Y - h:i A') ?>" readonly>
                                </div>
                            </div>

                            <!-- Monto inicial / Fondo de sencillo -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="monto_inicial" class="form-label mb-0">Fondo Inicial en Efectivo (S/.) <span class="text-danger">*</span></label>
                                    <small class="text-muted">Sencillo para dar vuelto</small>
                                </div>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-success text-white fw-bold">S/.</span>
                                    <input type="number" step="0.50" min="0" name="monto_inicial" id="monto_inicial" class="form-control fw-bold text-success" value="100.00" required autofocus>
                                </div>
                                <div class="form-text small">Sugerencias rápidas de fondo:</div>
                                <div class="d-flex gap-2 mt-1">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-sugerido" data-monto="0">S/. 0.00</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-sugerido" data-monto="50">S/. 50.00</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-sugerido active" data-monto="100">S/. 100.00</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-sugerido" data-monto="150">S/. 150.00</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-sugerido" data-monto="200">S/. 200.00</button>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-4">
                                <label for="observaciones" class="form-label">Observaciones / Novedades de Entrada</label>
                                <textarea id="observaciones" name="observaciones" class="form-control form-control-sm" rows="2" placeholder="Ej: Se recibe caja con S/. 100 en monedas de S/. 2 y S/. 5..."></textarea>
                            </div>

                            <!-- Botón de apertura -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-unlock-fill me-2"></i>Abrir Caja e Iniciar Turno
                                </button>
                                <a href="<?= url('home') ?>" class="btn btn-outline-secondary btn-sm">Volver al Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= script_js('vistas/caja-apertura.js') ?>
