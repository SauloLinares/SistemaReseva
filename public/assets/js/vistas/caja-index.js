// JavaScript de la vista caja/index.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const inputReal = document.getElementById('monto_real_efectivo');
    const esperado = parseFloat(inputReal ? inputReal.dataset.esperado : 0) || 0;
    const alertaDif = document.getElementById('alertaDiferencia');
    const iconoDif = document.getElementById('iconoDif');
    const textoDif = document.getElementById('textoDif');
    const btnCopiar = document.getElementById('btnCopiarEsperado');

    function calcularDiferencia() {
        const real = parseFloat(inputReal.value || 0);
        const dif = real - esperado;

        if (Math.abs(dif) < 0.01) {
            alertaDif.className = 'alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoDif.className = 'bi bi-check-circle-fill fs-5 text-success';
            textoDif.innerHTML = '<strong>¡Caja Cuadrada!</strong> El conteo coincide exactamente con los registros del sistema (Diferencia: S/. 0.00).';
        } else if (dif > 0) {
            alertaDif.className = 'alert alert-warning py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoDif.className = 'bi bi-plus-circle-fill fs-5 text-warning';
            textoDif.innerHTML = `<strong>Sobrante de caja: +S/. ${dif.toFixed(2)}</strong>. Hay más dinero físico en gaveta del registrado.`;
        } else {
            alertaDif.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2';
            iconoDif.className = 'bi bi-exclamation-triangle-fill fs-5 text-danger';
            textoDif.innerHTML = `<strong>Faltante de caja: -S/. ${Math.abs(dif).toFixed(2)}</strong>. Falta dinero físico respecto a lo esperado.`;
        }
    }

    if (inputReal) {
        inputReal.addEventListener('input', calcularDiferencia);
    }

    if (btnCopiar) {
        btnCopiar.addEventListener('click', function() {
            inputReal.value = esperado.toFixed(2);
            calcularDiferencia();
        });
    }
});
