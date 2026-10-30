<!-- FullCalendar CSS & JS desde CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i>Calendario de Reservas</h3>
                <p class="text-muted mb-0 small">Visualización gráfica de ocupación, check-in y reservas por fecha</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= url('reservas/crear') ?>" class="btn btn-primary btn-sm shadow-sm me-1">
                    <i class="bi bi-plus-circle me-1"></i> Nueva Reserva
                </a>
                <a href="<?= url('reservas') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-list-check me-1"></i> Ver Listado
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Leyenda de colores -->
        <div class="card shadow-sm mb-4">
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
        <div class="card shadow-sm">
            <div class="card-body p-3">
                <div id="calendar" style="min-height: 650px;"></div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventos = <?= json_encode($eventos, JSON_UNESCAPED_UNICODE) ?>;

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
            if (info.event.url) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
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
