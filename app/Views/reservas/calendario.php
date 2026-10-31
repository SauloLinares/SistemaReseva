<!-- FullCalendar CSS & JS desde CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<?php partial('page_header', [
    'titulo'    => 'Calendario de Reservas',
    'icono'     => 'bi-calendar3',
    'subtitulo' => 'Ocupación por fecha: cada barra cubre las noches de la estadía',
    'acciones'  => [
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventos = <?= json_encode($eventos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const modalEvento = new bootstrap.Modal(document.getElementById('modalDetalleEvento'));

    const fechaEs = iso => iso ? iso.split('-').reverse().join('/') : '--';
    const soles = n => 'S/. ' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const noches = (a, b) => Math.round((new Date(b) - new Date(a)) / 86400000);
    const badgeEstado = {
        confirmada: 'badge-soft-primary',
        pendiente: 'badge-soft-warning',
        en_estadia: 'badge-soft-success',
        finalizada: 'badge-soft-secondary'
    };

    // En pantallas angostas la cuadrícula del mes no se lee: se usa la lista
    const esMovil = () => window.innerWidth < 768;
    const barraMovil = { left: 'prev,next', center: 'title', right: 'today' };
    const barraEscritorio = { left: 'prev,next today', center: 'title', right: 'dayGridMonth,dayGridWeek,listMonth' };

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: esMovil() ? 'listMonth' : 'dayGridMonth',
        locale: 'es',
        firstDay: 1,
        height: 'auto',
        headerToolbar: esMovil() ? barraMovil : barraEscritorio,
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Lista' },
        buttonHints: { prev: 'Mes anterior', next: 'Mes siguiente', today: 'Ir a hoy' },
        noEventsText: 'No hay reservas en este período',
        dayMaxEvents: 3,
        moreLinkText: n => '+' + n + ' más',
        displayEventTime: false,
        events: eventos,
        eventDidMount: function(info) {
            const p = info.event.extendedProps;
            info.el.setAttribute('title', p.codigo + ' · ' + p.estado_texto + ' · ' + fechaEs(p.fecha_entrada) + ' → ' + fechaEs(p.fecha_salida));
        },
        windowResize: function() {
            const movil = esMovil();
            calendar.setOption('headerToolbar', movil ? barraMovil : barraEscritorio);
            if (movil && calendar.view.type !== 'listMonth') calendar.changeView('listMonth');
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const p = info.event.extendedProps || {};

            document.getElementById('mCalCodigo').textContent = p.codigo || info.event.title;
            document.getElementById('mCalHuesped').textContent = p.huesped || 'No especificado';
            document.getElementById('mCalHabTexto').textContent = p.habitaciones || 'Sin asignar';
            document.getElementById('mCalEntrada').textContent = fechaEs(p.fecha_entrada);
            document.getElementById('mCalSalida').textContent = fechaEs(p.fecha_salida);
            document.getElementById('mCalNoches').textContent = noches(p.fecha_entrada, p.fecha_salida);
            document.getElementById('mCalTotal').textContent = soles(p.total);

            const badge = document.getElementById('mCalEstadoBadge');
            badge.className = 'badge mt-1 ' + (badgeEstado[p.estado] || 'badge-soft-secondary');
            badge.textContent = p.estado_texto || p.estado;

            // Check-in solo para reservas que aún no ingresan
            const btnCheckin = document.getElementById('mCalBtnCheckin');
            const puedeIngresar = p.estado === 'confirmada' || p.estado === 'pendiente';
            btnCheckin.classList.toggle('d-none', !puedeIngresar);
            btnCheckin.href = puedeIngresar ? p.checkin_url : '#';

            document.getElementById('mCalBtnEditar').href = p.edit_url || '#';
            modalEvento.show();
        }
    });

    calendar.render();
});
</script>
