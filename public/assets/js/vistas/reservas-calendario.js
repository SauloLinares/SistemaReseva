// JavaScript de la vista reservas/calendario.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventos = leerJson('eventos-calendario') || [];
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
