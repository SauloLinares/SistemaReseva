// JavaScript de la vista caja/historial.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const inputFiltro = document.getElementById('filtroHistorialCaja');
    const tabla = document.getElementById('tablaHistorialCaja');

    if (inputFiltro && tabla) {
        inputFiltro.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const filas = tabla.querySelectorAll('tbody tr');

            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(query) ? '' : 'none';
            });
        });
    }
});
