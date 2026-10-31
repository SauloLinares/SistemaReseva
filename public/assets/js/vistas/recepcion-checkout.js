// JavaScript de la vista recepcion/checkout.php (antes estaba inline; la CSP no lo permite).

document.getElementById('estancia_id').addEventListener('change', (e) => cambiarEstancia(e.target.value));

function cambiarEstancia(id) {
    if (id) {
        window.location.href = appUrl('recepcion/checkout?id=' + encodeURIComponent(id));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const formModalCobro = document.getElementById('formModalCobro');
    const btnGuardarCobro = document.getElementById('btnGuardarCobroModal');
    const modalCobroEl = document.getElementById('modalCobroRapido');
    const alertModalCobro = document.getElementById('alertModalCobro');

    if (formModalCobro && btnGuardarCobro) {
        formModalCobro.addEventListener('submit', function(e) {
            e.preventDefault();
            alertModalCobro.classList.add('d-none');
            alertModalCobro.textContent = '';

            const formData = new FormData(formModalCobro);
            const originalBtnHtml = btnGuardarCobro.innerHTML;

            btnGuardarCobro.disabled = true;
            btnGuardarCobro.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

            fetch(appUrl('pagos/apiGuardar'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnGuardarCobro.disabled = false;
                btnGuardarCobro.innerHTML = originalBtnHtml;

                if (!data.success) {
                    alertModalCobro.textContent = data.mensaje || 'Error al procesar el pago.';
                    alertModalCobro.classList.remove('d-none');
                    return;
                }

                // Cerrar modal
                const modalInstance = bootstrap.Modal.getInstance(modalCobroEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Cobro Registrado!',
                        text: `Se emitió el comprobante ${data.comprobante_numero} con éxito.`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-printer me-1"></i> Ver Comprobante',
                        cancelButtonText: 'Continuar Check-out',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#16a34a'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(data.url_comprobante, '_blank');
                        }
                        // Recargar para reflejar saldo 0 y cuenta al día
                        window.location.reload();
                    });
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                btnGuardarCobro.disabled = false;
                btnGuardarCobro.innerHTML = originalBtnHtml;
                alertModalCobro.textContent = 'Ocurrió un error inesperado al procesar el pago.';
                alertModalCobro.classList.remove('d-none');
            });
        });
    }
});
