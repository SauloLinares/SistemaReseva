// JavaScript de la pantalla de acceso (antes inline; la CSP no lo permite).

// Funcionalidad para alternar ver/ocultar contraseña
const toggleBtn = document.getElementById('togglePasswordBtn');
const passInput = document.getElementById('password');
const toggleIcon = document.getElementById('togglePasswordIcon');

if (toggleBtn && passInput && toggleIcon) {
    toggleBtn.addEventListener('click', function() {
        const isPassword = passInput.type === 'password';
        passInput.type = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('bi-eye', !isPassword);
        toggleIcon.classList.toggle('bi-eye-slash', isPassword);
    });
}

// Autocompletar credenciales de prueba (el botón y sus datos solo existen en desarrollo)
const btnAuto = document.getElementById('btnAutocompletar');
if (btnAuto) {
    btnAuto.addEventListener('click', function() {
        document.getElementById('email').value = btnAuto.dataset.email;
        document.getElementById('password').value = btnAuto.dataset.clave;
        document.getElementById('password').focus();
    });
}

// Manejador del tema claro/oscuro
document.querySelectorAll('[data-bs-theme-value]').forEach(button => {
    button.addEventListener('click', () => {
        const theme = button.getAttribute('data-bs-theme-value');
        document.documentElement.setAttribute('data-bs-theme', theme);
        try {
            localStorage.setItem('lte-theme', theme);
        } catch {}
    });
});

// Al enviar: "Verificando credenciales…" y campos bloqueados (readonly, no disabled: un campo
// deshabilitado no se envía). Si el servidor devuelve la página con un error, todo vuelve a la normalidad.
const formLogin = document.getElementById('formLogin');
const btnIngresar = document.getElementById('btnIngresar');
if (formLogin && btnIngresar) {
    formLogin.addEventListener('submit', function () {
        formLogin.querySelectorAll('input').forEach(i => { i.readOnly = true; });
        setTimeout(function () {
            btnIngresar.disabled = true;
            btnIngresar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Verificando credenciales…';
        });
    });
    // Volver con "Atrás" a esta página no la deja bloqueada
    window.addEventListener('pageshow', function () {
        formLogin.querySelectorAll('input').forEach(i => { i.readOnly = false; });
        btnIngresar.disabled = false;
        btnIngresar.innerHTML = '<i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión';
    });
}
