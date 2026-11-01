// Seguridad de la sesión: cookie protegida, límite de intentos de contraseña, cerrar sesión
// solo por POST y aviso antes del cierre por inactividad.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const nuevoContexto = (browser) => browser.newContext({ storageState: { cookies: [], origins: [] } });

async function intentar(page, email, clave) {
    await page.goto(ruta('login'));
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', clave);
    await page.locator('form[action*="login/autenticar"] [type="submit"]').click();
    await page.waitForLoadState('domcontentloaded');
    return page.locator('body');
}

test('la cookie de sesión no se puede leer desde JavaScript ni viaja desde otros sitios', async ({ page, context }) => {
    await page.goto(ruta('home'));
    const sesion = (await context.cookies()).find(c => /sess/i.test(c.name));
    expect(sesion, 'cookie de sesión').toBeTruthy();
    expect(sesion.httpOnly).toBe(true);
    expect(sesion.sameSite).toBe('Lax');
    expect(await page.evaluate(() => document.cookie)).not.toContain(sesion.value);
});

test('tras 5 contraseñas equivocadas el ingreso con ese correo se bloquea', async ({ browser }) => {
    const contexto = await nuevoContexto(browser);
    const page = await contexto.newPage();
    const email = `bloqueo.${Date.now()}@hotel.test`;

    await intentar(page, email, 'equivocada-1');
    await intentar(page, email, 'equivocada-2');
    await expect(await intentar(page, email, 'equivocada-3')).toContainText('Te quedan 2 intentos');
    await expect(await intentar(page, email, 'equivocada-4')).toContainText('Te queda 1 intento');
    await expect(await intentar(page, email, 'equivocada-5')).toContainText('queda bloqueado 15 minutos');
    // Bloqueado: ni siquiera se revisa la contraseña
    await expect(await intentar(page, email, 'equivocada-6')).toContainText('Demasiados intentos fallidos');
    await expect(page).toHaveURL(/url=login/);
    // El correo se conserva en el formulario
    await expect(page.locator('input[name="email"]')).toHaveValue(email);
    await contexto.close();
});

test('cerrar sesión: un enlace no la cierra, el botón sí', async ({ browser }) => {
    const contexto = await nuevoContexto(browser);
    const page = await contexto.newPage();
    await intentar(page, process.env.E2E_EMAIL || 'admin@admin.com', process.env.E2E_PASSWORD || 'admin123');
    await expect(page).not.toHaveURL(/url=login/);

    // Abrir la dirección (lo que haría un enlace o una imagen de otra página) no cierra la sesión
    await page.goto(ruta('login/logout'));
    await page.goto(ruta('reservas'));
    await expect(page).toHaveURL(/url=reservas/);

    // El botón del menú envía el formulario con token
    await page.locator('.app-sidebar button[form="formCerrarSesion"]').click();
    await expect(page).toHaveURL(/url=login/);
    await expect(page.locator('body')).toContainText('cerrado tu sesión');
    await page.goto(ruta('reservas'));
    await expect(page).toHaveURL(/url=login/);
    await contexto.close();
});

test('antes del cierre por inactividad avisa y permite seguir trabajando', async ({ page }) => {
    await page.clock.install();
    await page.goto(ruta('reservas'));
    const minutos = await page.evaluate(() => window.leerJson('app-config').sesionMinutos);
    expect(minutos).toBeGreaterThanOrEqual(5);

    // Dos minutos antes del cierre aparece el aviso con la cuenta regresiva
    await page.clock.fastForward((minutos - 2) * 60000 + 15000);
    await expect(page.locator('.swal2-title')).toHaveText('¿Sigues ahí?');
    await expect(page.locator('[data-cuenta-sesion]')).toHaveText(/^1:\d\d$/);

    // "Seguir trabajando" renueva el plazo en el servidor
    const pulso = page.waitForResponse(r => r.url().includes('perfil/pulso'));
    await page.locator('.swal2-confirm').click();
    // (con el reloj simulado, Playwright no llega a leer el cuerpo: basta con el estado)
    const respuesta = await pulso;
    expect(respuesta.status()).toBe(200);
    expect(respuesta.headers()['content-type']).toContain('application/json');
    await expect(page.locator('.swal2-popup')).toBeHidden();
});
