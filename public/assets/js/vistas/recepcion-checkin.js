// JavaScript de la vista recepcion/checkin.php (antes estaba inline; la CSP no lo permite).

document.getElementById('selectReserva').addEventListener('change', (e) => cambiarReserva(e.target.value));

function cambiarReserva(id) {
    if (id) {
        window.location.href = appUrl('recepcion/checkin?reserva_id=' + encodeURIComponent(id));
    }
}
