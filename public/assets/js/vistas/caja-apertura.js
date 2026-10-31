// JavaScript de la vista caja/apertura.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const inputMonto = document.getElementById('monto_inicial');
    const btnsSugeridos = document.querySelectorAll('.btn-sugerido');

    btnsSugeridos.forEach(btn => {
        btn.addEventListener('click', function() {
            btnsSugeridos.forEach(b => b.classList.remove('active', 'btn-success', 'text-white'));
            this.classList.add('active', 'btn-success', 'text-white');
            inputMonto.value = parseFloat(this.dataset.monto).toFixed(2);
            inputMonto.focus();
        });
    });
});
