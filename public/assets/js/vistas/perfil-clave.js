// JavaScript de la vista perfil/clave.php: mostrar u ocultar contraseñas y comprobar las
// reglas mientras se escribe (el servidor vuelve a validarlas en PerfilController).
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formClave');
    if (!form) return;

    const actual = document.getElementById('clave_actual');
    const nueva = document.getElementById('clave_nueva');
    const confirmacion = document.getElementById('clave_confirmacion');
    const avisoCoincide = document.getElementById('avisoCoincide');
    const minimo = parseInt(form.dataset.minimo, 10) || 8;

    // Botones del ojo
    form.querySelectorAll('[data-ver-clave]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const campo = document.getElementById(boton.dataset.verClave);
            const ver = campo.type === 'password';
            campo.type = ver ? 'text' : 'password';
            boton.querySelector('i').className = ver ? 'bi bi-eye-slash' : 'bi bi-eye';
            boton.setAttribute('aria-pressed', ver ? 'true' : 'false');
        });
    });

    const reglas = {
        largo: v => v.length >= minimo,
        mezcla: v => /\p{L}/u.test(v) && /\d/.test(v),
        distinta: v => v !== '' && v !== actual.value,
    };

    function revisar() {
        const v = nueva.value;
        Object.entries(reglas).forEach(function ([nombre, cumple]) {
            const item = form.querySelector('[data-regla="' + nombre + '"]');
            const ok = cumple(v);
            item.classList.toggle('cumple', ok);
            item.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
        });

        if (confirmacion.value === '') {
            avisoCoincide.textContent = '';
            avisoCoincide.className = 'form-text';
        } else if (confirmacion.value === v) {
            avisoCoincide.textContent = 'Coinciden.';
            avisoCoincide.className = 'form-text text-success';
        } else {
            avisoCoincide.textContent = 'Todavía no coinciden.';
            avisoCoincide.className = 'form-text text-danger';
        }
    }

    [actual, nueva, confirmacion].forEach(el => el.addEventListener('input', revisar));
    revisar();
});
