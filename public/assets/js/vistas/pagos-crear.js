// JavaScript de la vista pagos/crear.php (antes estaba inline; la CSP no lo permite).

const selectComprobante = document.getElementById('comprobante_tipo');
const correlativos = JSON.parse(selectComprobante.dataset.correlativos || '{}');
selectComprobante.addEventListener('change', () => actualizarNumeroComprobante(selectComprobante.value));
document.getElementById('selectReserva').addEventListener('change', (e) => actualizarSaldo(e.target));

function actualizarNumeroComprobante(tipo) {
    document.getElementById('comprobante_numero').value = correlativos[tipo] || '001-000001';
}

function actualizarSaldo(sel) {
    if (sel.form && sel.form.dataset.restaurando) return; // se repone un envío con errores: respetar lo escrito
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.saldo) {
        document.querySelector('[data-cobro]').fijarMonto(opt.dataset.saldo);
        document.getElementById('textoSaldoAyuda').textContent = 'Saldo total adeudado: S/. ' + opt.dataset.saldo;
    }
}
