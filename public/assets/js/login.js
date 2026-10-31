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

// Funcionalidad para autocompletar credenciales de prueba
const btnAuto = document.getElementById('btnAutocompletar');
if (btnAuto) {
    btnAuto.addEventListener('click', function() {
        document.getElementById('email').value = 'admin@admin.com';
        document.getElementById('password').value = 'admin123';
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
