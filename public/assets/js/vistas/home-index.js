// JavaScript de la vista home/index.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    // 1. Filtrado instantáneo de tarjetas de habitaciones en Dashboard
    const botonesFiltro = document.querySelectorAll('#filtroHabitacionesDashboard .btn-filtro-dash');
    const itemsHabitacion = document.querySelectorAll('.item-hab-card');

    botonesFiltro.forEach(btn => {
        btn.addEventListener('click', function() {
            botonesFiltro.forEach(b => {
                b.classList.toggle('active', b === this);
                b.setAttribute('aria-pressed', b === this ? 'true' : 'false');
            });
            const filtro = this.dataset.filtro;

            itemsHabitacion.forEach(card => {
                if (filtro === 'todos' || card.dataset.estado === filtro) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // 2. Apertura del Modal Interactivo de Habitación
    let habActivaId = null;
    const modalEl = document.getElementById('modalDetalleHabitacion');
    const modalHab = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.room-card-interactive').forEach(card => {
        card.addEventListener('click', function() {
            habActivaId = this.dataset.id;
            const num = this.dataset.numero;
            const tipo = this.dataset.tipo;
            const piso = this.dataset.piso;
            const precio = this.dataset.precio;
            const estado = this.dataset.estado;
            const carac = this.dataset.caracteristicas;
            const huesped = this.dataset.huesped;
            const doc = this.dataset.doc;
            const entrada = this.dataset.entrada;
            const salida = this.dataset.salida;
            const codigo = this.dataset.codigo;
            const saldo = this.dataset.saldo;
            const estanciaId = this.dataset.estanciaId;
            const reservaId = this.dataset.reservaId;

            document.getElementById('mHabNumeroTitulo').textContent = 'Habitación ' + num;
            document.getElementById('mHabTipo').textContent = tipo;
            document.getElementById('mHabPiso').textContent = 'Piso ' + piso;
            document.getElementById('mHabPrecio').textContent = precio;
            document.getElementById('mHabCaracteristicas').textContent = carac || 'Wi-Fi, TV por cable, Baño Privado';
            document.getElementById('mHabFotoBanner').src = this.dataset.foto || assetUrl('uploads/habitaciones/simple.jpg');

            const badge = document.getElementById('mHabEstadoBadge');
            badge.className = 'badge text-uppercase';
            badge.textContent = estado;
            if (estado === 'disponible') badge.classList.add('bg-success');
            else if (estado === 'ocupada') badge.classList.add('bg-danger');
            else if (estado === 'limpieza') badge.classList.add('bg-warning', 'text-dark');
            else badge.classList.add('bg-secondary');

            // Ocultar todas las secciones
            document.getElementById('mSeccionDisponible').classList.add('d-none');
            document.getElementById('mSeccionOcupada').classList.add('d-none');
            document.getElementById('mSeccionLimpieza').classList.add('d-none');
            document.getElementById('mSeccionMantenimiento').classList.add('d-none');

            if (estado === 'disponible') {
                document.getElementById('mSeccionDisponible').classList.remove('d-none');
                document.getElementById('mBtnCrearReserva').href = appUrl('reservas/crear?habitacion_id=' + habActivaId);
            } else if (estado === 'ocupada') {
                document.getElementById('mSeccionOcupada').classList.remove('d-none');
                document.getElementById('mHuespedNombre').textContent = huesped || 'Huésped registrado';
                document.getElementById('mHuespedDoc').textContent = 'Doc: ' + (doc || 'No registrado');
                document.getElementById('mFechaEntrada').textContent = entrada || '--/--/----';
                document.getElementById('mFechaSalida').textContent = salida || '--/--/----';
                document.getElementById('mCodigoReserva').textContent = codigo || 'N/A';
                document.getElementById('mSaldoPendiente').textContent = saldo || '$0.00';
                document.getElementById('mBtnProcesarCheckout').href = appUrl('recepcion/checkout' + (estanciaId ? '?id=' + estanciaId : ''));
                // Consumo y ficha de la reserva de esta habitación (sin reserva, a la pantalla general)
                document.getElementById('mBtnCargarConsumo').href = appUrl('servicios/consumo' + (reservaId ? '?reserva_id=' + reservaId : ''));
                document.getElementById('mBtnFichaEstadia').href = appUrl(reservaId ? 'reservas/editar/' + reservaId : 'recepcion/estadias');
            } else if (estado === 'limpieza') {
                document.getElementById('mSeccionLimpieza').classList.remove('d-none');
            } else if (estado === 'mantenimiento') {
                document.getElementById('mSeccionMantenimiento').classList.remove('d-none');
            }

            modalHab.show();
        });
    });

    // 3. Cambio de estado directo via AJAX desde el Modal
    function actualizarEstado(nuevoEstado) {
        if (!habActivaId) return;

        const fd = new FormData();
        fd.append('id', habActivaId);
        fd.append('estado', nuevoEstado);
        fd.append('ajax', '1');

        fetch(appUrl('habitaciones/cambiarEstado'), {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                modalHab.hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Estado actualizado a ' + nuevoEstado,
                    showConfirmButton: false,
                    timer: 2000
                });
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                Swal.fire('Error', 'No se pudo cambiar el estado de la habitación', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Hubo un error de conexión al servidor', 'error');
        });
    }

    document.getElementById('mBtnEnviarLimpieza')?.addEventListener('click', () => actualizarEstado('limpieza'));
    document.getElementById('mBtnEnviarMantenimiento')?.addEventListener('click', () => actualizarEstado('mantenimiento'));
    document.getElementById('mBtnLiberarLimpieza')?.addEventListener('click', () => actualizarEstado('disponible'));
    document.getElementById('mBtnMantenimientoDesdeLimpieza')?.addEventListener('click', () => actualizarEstado('mantenimiento'));
    document.getElementById('mBtnLiberarMantenimiento')?.addEventListener('click', () => actualizarEstado('disponible'));
    document.getElementById('mBtnLimpiezaDesdeMantenimiento')?.addEventListener('click', () => actualizarEstado('limpieza'));
});

// Aviso de lo pendiente hoy: se muestra al entrar y, si se cierra, no vuelve hasta mañana
(function () {
    const aviso = document.getElementById('avisoInicio');
    if (!aviso) return;
    const usuario = (window.leerJson('app-config') || {}).usuario || 0;
    const clave = 'aviso-inicio-cerrado:' + usuario;
    let cerradoHoy = false;
    try { cerradoHoy = localStorage.getItem(clave) === aviso.dataset.dia; } catch (e) { /* sin almacenamiento */ }
    if (cerradoHoy) return;

    aviso.hidden = false;
    aviso.querySelector('[data-cerrar-aviso]').addEventListener('click', function () {
        aviso.hidden = true;
        try { localStorage.setItem(clave, aviso.dataset.dia); } catch (e) { /* sin almacenamiento */ }
    });
})();
