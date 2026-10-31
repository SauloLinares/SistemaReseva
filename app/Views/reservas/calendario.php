<!-- FullCalendar 6 (local): inyecta sus propios estilos, no tiene archivo CSS -->
<script src="<?= asset('vendor/fullcalendar/index.global.min.js') ?>"></script>

<?php partial('page_header', [
    'titulo'    => 'Calendario de Reservas',
    'icono'     => 'bi-calendar3',
    'subtitulo' => 'Ocupación por fecha: cada barra cubre las noches de la estadía',
    'acciones'  => [
        ['texto' => 'Por habitación', 'url' => 'reservas/planificacion', 'icono' => 'bi-grid-3x3', 'variante' => 'outline-secondary'],
        ['texto' => 'Ver Listado', 'url' => 'reservas', 'icono' => 'bi-list-check', 'variante' => 'outline-secondary'],
        ['texto' => 'Nueva Reserva', 'url' => 'reservas/crear', 'icono' => 'bi-plus-lg'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body p-3 p-md-4">
                <!-- Leyenda: mismos colores que las barras del calendario -->
                <div class="calendario-leyenda mb-3" aria-label="Leyenda de estados">
                    <span class="ev-chip ev-estado-confirmada">Confirmada</span>
                    <span class="ev-chip ev-estado-pendiente">Pendiente</span>
                    <span class="ev-chip ev-estado-en_estadia">En estadía</span>
                    <span class="ev-chip ev-estado-finalizada">Finalizada</span>
                    <span class="text-body-secondary small ms-md-auto"><i class="bi bi-hand-index me-1"></i>Haz clic en una reserva para ver el detalle</span>
                </div>

                <div id="calendar" class="calendario-hotel"></div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Detalle Rápido de Reserva en Calendario -->
<div class="modal fade" id="modalDetalleEvento" tabindex="-1" aria-labelledby="mCalCodigo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle avatar-primary flex-shrink-0 avatar-md">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 font-monospace" id="mCalCodigo">Reserva ---</h5>
                        <span class="badge badge-soft-secondary mt-1" id="mCalEstadoBadge">Estado</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <small class="text-body-secondary d-block">Huésped titular</small>
                    <div class="fw-bold fs-6" id="mCalHuesped">---</div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 border rounded bg-body-secondary h-100">
                            <small class="text-body-secondary d-block">Check-in</small>
                            <strong><i class="bi bi-box-arrow-in-right text-success me-1"></i><span id="mCalEntrada">--</span></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-body-secondary h-100">
                            <small class="text-body-secondary d-block">Check-out</small>
                            <strong><i class="bi bi-box-arrow-right text-danger me-1"></i><span id="mCalSalida">--</span></strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 small">
                    <span class="text-body-secondary">Habitación(es)</span>
                    <strong id="mCalHabTexto">---</strong>
                </div>
                <div class="d-flex justify-content-between pt-1 small">
                    <span class="text-body-secondary">Noches</span>
                    <strong id="mCalNoches">--</strong>
                </div>
                <div class="d-flex justify-content-between pt-1">
                    <span class="text-body-secondary small">Total de la reserva</span>
                    <strong id="mCalTotal">S/. 0.00</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" id="mCalBtnEditar" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square me-1"></i>Ver / modificar
                </a>
                <a href="#" id="mCalBtnCheckin" class="btn btn-success">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Check-in
                </a>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="eventos-calendario"><?= json_encode($eventos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?= script_js('vistas/reservas-calendario.js') ?>
