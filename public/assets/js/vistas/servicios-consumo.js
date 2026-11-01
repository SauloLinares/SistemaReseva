// JavaScript de la vista servicios/consumo.php (antes estaba inline; la CSP no lo permite).

['change', 'input'].forEach(ev => {
    document.getElementById('servicio_id').addEventListener(ev, calcularTotal);
    document.getElementById('inputCantidad').addEventListener(ev, calcularTotal);
});

function calcularTotal() {
    const sel = document.getElementById('servicio_id');
    const cantInput = document.getElementById('inputCantidad');
    const opt = sel.options[sel.selectedIndex];
    const precio = opt ? parseFloat(opt.dataset.precio || 0) : 0;
    const cant = parseInt(cantInput.value || 1);
    const total = precio * cant;
    document.getElementById('displayTotalConsumo').textContent = window.moneda + ' ' + total.toFixed(2);
}
