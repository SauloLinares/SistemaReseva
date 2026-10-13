/**
 * Scripts JavaScript personalizados para los alumnos
 * Aquí pueden agregar validaciones del lado del cliente, alertas interactivas o llamadas AJAX.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Auto-cerrar alertas después de 5 segundos si lo desean
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 6000);
    });
});
