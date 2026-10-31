// JavaScript de la vista habitaciones/estados.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar  = document.getElementById('buscarHabitacion');
    const selectPiso   = document.getElementById('filtroPiso');
    const selectEstado = document.getElementById('filtroEstado');
    const contador     = document.getElementById('contadorHabitaciones');
    const sinResultados = document.getElementById('sinResultados');
    const items        = document.querySelectorAll('.item-habitacion');
    const stats        = document.querySelectorAll('.estado-stat');

    // Quita tildes y mayúsculas para que "estandar" encuentre "Estándar"
    const normalizar = texto => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

    function aplicarFiltros() {
        const termino = normalizar(inputBuscar.value);
        const piso    = selectPiso.value;
        const estado  = selectEstado.value;
        let visibles  = 0;

        items.forEach(item => {
            const texto = normalizar(item.dataset.numero + ' ' + item.dataset.tipo);
            const coincide = (!termino || texto.includes(termino))
                && (!piso || item.dataset.piso === piso)
                && (!estado || item.dataset.estado === estado);
            item.classList.toggle('d-none', !coincide);
            if (coincide) visibles++;
        });

        stats.forEach(stat => {
            const activo = stat.dataset.estado === estado;
            stat.classList.toggle('active', activo);
            stat.setAttribute('aria-pressed', activo ? 'true' : 'false');
        });

        contador.textContent = visibles + ' de ' + items.length + ' habitaciones';
        sinResultados.classList.toggle('d-none', visibles > 0);
    }

    function limpiarFiltros() {
        inputBuscar.value = '';
        selectPiso.value = '';
        selectEstado.value = '';
        aplicarFiltros();
        inputBuscar.focus();
    }

    inputBuscar.addEventListener('input', aplicarFiltros);
    selectPiso.addEventListener('change', aplicarFiltros);
    selectEstado.addEventListener('change', aplicarFiltros);
    document.getElementById('limpiarFiltros').addEventListener('click', limpiarFiltros);
    document.querySelector('[data-limpiar-filtros]').addEventListener('click', limpiarFiltros);

    // Clic en una tarjeta de resumen: filtra por ese estado (un segundo clic lo quita)
    stats.forEach(stat => {
        stat.addEventListener('click', function () {
            selectEstado.value = selectEstado.value === this.dataset.estado ? '' : this.dataset.estado;
            aplicarFiltros();
        });
    });

    aplicarFiltros();
});
