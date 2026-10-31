// JavaScript de la vista reservas/disponibilidad.php (antes estaba inline; la CSP no lo permite).

document.querySelectorAll('[data-rango]').forEach(b => b.addEventListener('click', () => {
    aplicarRangoFecha(b.dataset.rango === 'finde' ? 'finde' : parseInt(b.dataset.rango, 10));
}));

function aplicarRangoFecha(tipo) {
    const hoy = new Date();
    const formatear = d => d.toISOString().split('T')[0];
    const entradaInput = document.querySelector('input[name="entrada"]');
    const salidaInput = document.querySelector('input[name="salida"]');
    
    if (tipo === 'finde') {
        const dia = hoy.getDay();
        const diasHastaViernes = (5 - dia + 7) % 7 || 7;
        const viernes = new Date(hoy);
        viernes.setDate(hoy.getDate() + (dia === 5 ? 0 : diasHastaViernes));
        const domingo = new Date(viernes);
        domingo.setDate(viernes.getDate() + 2);
        entradaInput.value = formatear(viernes);
        salidaInput.value = formatear(domingo);
    } else {
        const dias = parseInt(tipo, 10) || 1;
        const salida = new Date(hoy);
        salida.setDate(hoy.getDate() + dias);
        entradaInput.value = formatear(hoy);
        salidaInput.value = formatear(salida);
    }
    
    document.getElementById('formDisponibilidad').submit();
}
