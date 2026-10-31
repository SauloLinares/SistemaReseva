<!-- FullCalendar CSS & JS desde CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<?php partial('page_header', [
    'titulo'    => 'Calendario de Reservas',
    'icono'     => 'bi-calendar3',
    'subtitulo' => 'Visualización gráfica de ocupación, check-in y reservas por fecha',
    'acciones'  => [
        ['texto' => 'Nueva Reserva', 'url' => 'reservas/crear', 'icono' => 'bi-plus-circle'],
        ['texto' => 'Ver Listado', 'url' => 'reservas', 'icono' => 'bi-list-check', 'variante' => 'outline-secondary'],
    ],
]) ?>

<div class="app-content">
    <div class="container-fluid">

        <!-- Leyenda de colores -->
        <div class="card mb-4">
            <div class="card-body py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="fw-semibold small text-muted"><i class="bi bi-info-circle me-1"></i>Leyenda de Estados:</span>
                <div class="d-flex flex-wrap gap-3 small">
                    <span><span class="badge" style="background-color: #0d6efd;">&nbsp;</span> Confirmada</span>
                    <span><span class="badge" style="background-color: #198754;">&nbsp;</span> En Estadía (Alojado)</span>
                    <span><span class="badge" style="background-color: #ffc107; color: #000;">&nbsp;</span> Pendiente de Pago</span>
                    <span><span class="badge" style="background-color: #6c757d;">&nbsp;</span> Finalizada</span>
                </div>
            </div>
        </div>

        <!-- Contenedor del Calendario -->
        <div class="card border-0">
            <div class="card-body p-3">
                <div id="calendar" style="min-height: 650px;"></div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Detalle Rápido de Reserva en Calendario -->
<div class="modal fade" id="modalDetalleEvento" tabindex="-1" aria-labelledby="modalDetalleEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle avatar-primary flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="mCalCodigo">Reserva ---</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge" id="mCalEstadoBadge">Estado</span>
                            <span class="text-muted small" id="mCalHabitaciones">Hab: ---</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle avatar-secondary flex-shrink-0" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Huésped Titular</small>
                            <h6 class="fw-bold mb-0" id="mCalHuesped">---</h6>
                        </div>
                    </div>
                    <div class="row g-2 pt-2 border-top small">
                        <div class="col-6">
                            <span class="text-muted d-block">Check-in:</span>
                            <strong id="mCalEntrada">--/--/----</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Check-out:</span>
                            <strong id="mCalSalida">--/--/----</strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block">Habitación(es):</span>
                            <strong id="mCalHabTexto" class="text-primary">---</strong>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted d-block">Total Reserva:</span>
                            <strong id="mCalTotal" class="text-success">$0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="#" id="mCalBtnCheckin" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Realizar Check-in Directo
                    </a>
                    <div class="d-flex gap-2">
                        <a href="#" id="mCalBtnEditar" class="btn btn-outline-primary w-100">
                            <i class="bi bi-pencil-square me-1"></i> Modificar Reserva
                        </a>
                        <button type="button" class="btn btn-light border w-100" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventos = <?= json_encode($eventos, JSON_UNESCAPED_UNICODE) ?>;
    const modalEventoEl = document.getElementById('modalDetalleEvento');
    const modalEvento = new bootstrap.Modal(modalEventoEl);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            list: 'Lista'
        },
        events: eventos,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const props = info.event.extendedProps || {};
            
            document.getElementById('mCalCodigo').textContent = props.codigo || info.event.title;
            document.getElementById('mCalHuesped').textContent = props.huesped || 'No especificado';
            document.getElementById('mCalHabitaciones').textContent = 'Hab: ' + (props.habitaciones || 'Sin asignar');
            document.getElementById('mCalHabTexto').textContent = props.habitaciones || 'Sin asignar';
            document.getElementById('mCalEntrada').textContent = props.fecha_entrada || '';
            document.getElementById('mCalSalida').textContent = props.fecha_salida || '';
            document.getElementById('mCalTotal').textContent = props.total ? '$' + Number(props.total).toFixed(2) : '$0.00';

            const badge = document.getElementById('mCalEstadoBadge');
            const estado = props.estado || '';
            badge.className = 'badge text-uppercase';
            badge.textContent = estado;
            if (estado === 'confirmada') badge.classList.add('bg-primary');
            else if (estado === 'en_estadia') badge.classList.add('bg-success');
            else if (estado === 'pendiente') badge.classList.add('bg-warning', 'text-dark');
            else badge.classList.add('bg-secondary');

            const btnCheckin = document.getElementById('mCalBtnCheckin');
            if (estado === 'confirmada' || estado === 'pendiente') {
                btnCheckin.classList.remove('d-none');
                btnCheckin.href = props.checkin_url || '#';
            } else {
                btnCheckin.classList.add('d-none');
            }

            const btnEditar = document.getElementById('mCalBtnEditar');
            btnEditar.href = props.edit_url || '#';

            modalEvento.show();
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        }
    });

    calendar.render();
});
</script>
