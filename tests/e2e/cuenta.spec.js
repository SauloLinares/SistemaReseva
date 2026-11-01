// Fase 2: cuenta del usuario. Mi perfil, cambio de contraseña y el cambio obligatorio
// cuando la contraseña la asignó el administrador. Deja la base de datos como estaba.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const PNG_1x1 = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'base64');

test('Mi perfil: el menú de la cabecera lleva a Mi perfil y a Cambiar contraseña', async ({ page }) => {
    await page.goto(ruta('home'));
    await page.locator('.user-menu > a').click();
    await expect(page.locator('.menu-cuenta')).toBeVisible();
    await page.locator('.menu-cuenta a', { hasText: 'Mi perfil' }).click();
    await expect(page).toHaveURL(/url=perfil$/);
    await page.locator('.user-menu > a').click();
    await page.locator('.menu-cuenta a', { hasText: 'Cambiar contraseña' }).click();
    await expect(page).toHaveURL(/url=perfil\/clave/);
});

test('Mi perfil: valida, guarda el teléfono y lo deja como estaba', async ({ page }) => {
    await page.goto(ruta('perfil'));
    const telefonoOriginal = await page.inputValue('#telefono');

    // Correo inválido: vuelve con lo escrito y el campo marcado
    await page.fill('#email', 'no-es-correo');
    await page.fill('#telefono', '987 654 321');
    await page.evaluate(() => document.querySelector('#email').type = 'text'); // saltar la validación del navegador
    await page.getByRole('button', { name: /Guardar mis datos/ }).click();
    await expect(page.locator('#email')).toHaveClass(/is-invalid/);
    await expect(page.locator('#telefono')).toHaveValue('987 654 321');

    // Con un correo válido se guarda
    await page.goto(ruta('perfil'));
    await page.fill('#telefono', '987 654 321');
    await page.getByRole('button', { name: /Guardar mis datos/ }).click();
    await expect(page.locator('#sessionFlashToast')).toContainText('Tus datos se guardaron');
    await expect(page.locator('#telefono')).toHaveValue('987 654 321');

    await page.fill('#telefono', telefonoOriginal);
    await page.getByRole('button', { name: /Guardar mis datos/ }).click();
    await expect(page.locator('#telefono')).toHaveValue(telefonoOriginal);
});

test('Mi perfil: la foto solo acepta imágenes y se ve en la cabecera', async ({ page }) => {
    await page.goto(ruta('perfil'));
    await page.setInputFiles('#foto', { name: 'foto.png', mimeType: 'image/png', buffer: Buffer.from('<?php echo 1; ?>') });
    await page.getByRole('button', { name: /Guardar mis datos/ }).click();
    await expect(page.locator('#foto')).toHaveClass(/is-invalid/);

    await page.setInputFiles('#foto', { name: 'foto.png', mimeType: 'image/png', buffer: PNG_1x1 });
    await page.getByRole('button', { name: /Guardar mis datos/ }).click();
    await expect(page.locator('#sessionFlashToast')).toContainText('Tus datos se guardaron');
    await expect(page.locator('.user-menu > a img.avatar-usuario')).toHaveAttribute('src', /uploads\/usuarios\/usuario_\d+_[0-9a-f]+\.png$/);

    // Quitar la foto vuelve a las iniciales
    await page.check('#quitar_foto');
    await page.getByRole('button', { name: /Guardar mis datos/ }).click();
    await expect(page.locator('.user-menu > a .avatar-iniciales')).toBeVisible();
});

test('Cambiar contraseña: reglas en vivo y errores por campo', async ({ page }) => {
    await page.goto(ruta('perfil/clave'));
    await page.fill('#clave_nueva', 'corta1');
    await expect(page.locator('[data-regla="largo"]')).not.toHaveClass(/cumple/);
    await page.fill('#clave_nueva', 'hotelsol2026');
    await expect(page.locator('[data-regla="largo"]')).toHaveClass(/cumple/);
    await expect(page.locator('[data-regla="mezcla"]')).toHaveClass(/cumple/);

    // Contraseña actual equivocada: el servidor la marca y no cambia nada
    await page.fill('#clave_actual', 'equivocada-123');
    await page.fill('#clave_confirmacion', 'hotelsol2026');
    await page.getByRole('button', { name: /Guardar contraseña/ }).click();
    await expect(page.locator('#clave_actual')).toHaveClass(/is-invalid/);
    await expect(page.locator('#error-clave_actual')).toContainText('no es correcta');
});

test('usuario con contraseña asignada: debe cambiarla antes de usar el sistema', async ({ page, browser }) => {
    const sello = Date.now();
    const email = `e2e.${sello}@hotel.test`;
    const temporal = `Temporal${sello}`;
    const propia = `Propia${sello}x`;

    // El administrador crea el usuario
    await page.goto(ruta('seguridad/usuarios'));
    await page.getByRole('button', { name: /Nuevo Usuario/ }).click();
    await page.fill('#userNombre', 'ZZ Prueba Cuenta');
    await page.fill('#userEmail', email);
    await page.fill('#userPassword', temporal);
    // Con su rol (Recepcionista) puede abrir Reservas: rol_id se guarda y se respeta
    await page.selectOption('#userRol', { label: 'Recepcionista' });
    await page.locator('#modalUsuario [type="submit"]').click();
    await expect(page.locator('#sessionFlashToast')).toContainText('Deberá cambiar la contraseña');

    const ctx = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const otra = await ctx.newPage();
    try {
        // Ingresa con la temporal: va directo a crear su contraseña
        await otra.goto(ruta('login'));
        await otra.fill('#email', email);
        await otra.fill('#password', temporal);
        await otra.locator('button[type="submit"]').click();
        await expect(otra).toHaveURL(/url=perfil\/clave/);
        await expect(otra.locator('.alert-warning')).toContainText('asignó el administrador');

        // No puede ir a otra pantalla hasta cambiarla
        await otra.goto(ruta('reservas'));
        await expect(otra).toHaveURL(/url=perfil\/clave/);

        await otra.fill('#clave_actual', temporal);
        await otra.fill('#clave_nueva', propia);
        await otra.fill('#clave_confirmacion', propia);
        await otra.getByRole('button', { name: /Guardar contraseña/ }).click();
        await expect(otra.locator('#sessionFlashToast')).toContainText('nueva contraseña');
        await otra.goto(ruta('reservas'));
        await expect(otra).toHaveURL(/url=reservas/);

        // La contraseña nueva es la que vale
        await otra.locator('.app-sidebar button[form="formCerrarSesion"]').click();
        await expect(otra).toHaveURL(/url=login/);
        await otra.fill('#email', email);
        await otra.fill('#password', propia);
        await otra.locator('button[type="submit"]').click();
        await expect(otra).toHaveURL(/public\/(index\.php)?$/);
    } finally {
        await ctx.close();
        // Limpieza: el administrador elimina el usuario de prueba
        await page.goto(ruta('seguridad/usuarios'));
        await page.locator('table tbody tr', { hasText: email }).locator('a[data-confirm]').click();
        await page.locator('.swal2-confirm').click();
        await expect(page.locator('#sessionFlashToast')).toContainText('eliminado');
    }
});
