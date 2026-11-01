// JavaScript de la vista recepcion/estadias.php (antes estaba inline; la CSP no lo permite).

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalCargarConsumo');
    const selectServicio = document.getElementById('modalSelectServicio');
    const inputCantidad = document.getElementById('modalConsumoCantidad');
    const inputPrecio = document.getElementById('modalConsumoPrecio');
    const totalText = document.getElementById('modalConsumoTotalText');
    const formModal = document.getElementById('formModalConsumo');
    const btnGuardar = document.getElementById('btnGuardarConsumoModal');
    const alertModal = document.getElementById('alertModalConsumo');

    function actualizarTotal() {
        const cant = parseFloat(inputCantidad.value) || 0;
        const precio = parseFloat(inputPrecio.value) || 0;
        const total = (cant * precio).toFixed(2);
        totalText.textContent = window.moneda + ' ' + total;
    }

    if (selectServicio) {
        selectServicio.addEventListener('change', function() {
            const opt = selectServicio.options[selectServicio.selectedIndex];
            if (opt && opt.dataset.precio) {
                inputPrecio.value = parseFloat(opt.dataset.precio).toFixed(2);
            } else {
                inputPrecio.value = '';
            }
            actualizarTotal();
        });
    }

    if (inputCantidad) inputCantidad.addEventListener('input', actualizarTotal);
    if (inputPrecio) inputPrecio.addEventListener('input', actualizarTotal);

    // Al abrir el modal desde un botón de fila
    document.querySelectorAll('.btn-abrir-consumo').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalConsumoReservaId').value = this.dataset.reservaId || '';
            document.getElementById('modalConsumoHabitacion').textContent = 'Hab. ' + (this.dataset.habitacion || '');
            document.getElementById('modalConsumoHuesped').textContent = this.dataset.huesped || '';
            alertModal.classList.add('d-none');
            alertModal.textContent = '';
            formModal.reset();
            inputCantidad.value = 1;
            totalText.textContent = window.moneda + ' 0.00';
        });
    });

    if (formModal && btnGuardar) {
        formModal.addEventListener('submit', function(e) {
            e.preventDefault();
            alertModal.classList.add('d-none');
            alertModal.textContent = '';

            const formData = new FormData(formModal);
            const originalBtnHtml = btnGuardar.innerHTML;

            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cargando...';

            fetch(appUrl('servicios/apiGuardarConsumo'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = originalBtnHtml;

                if (!data.success) {
                    alertModal.textContent = data.mensaje || 'Error al cargar el consumo.';
                    alertModal.classList.remove('d-none');
                    return;
                }

                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.mensaje || '¡Consumo cargado exitosamente!',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    window.location.reload();
                }
            })
            .catch(err => {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = originalBtnHtml;
                alertModal.textContent = 'Ocurrió un error inesperado al conectar con el servidor.';
                alertModal.classList.remove('d-none');
            });
        });
    }
});
