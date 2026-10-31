// Protección CSRF, borrados solo por POST y cabeceras de seguridad.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('un POST sin token CSRF se rechaza', async ({ page }) => {
    await page.goto(ruta('servicios'));
    const estado = await page.evaluate(async () => {
        const r = await new Promise((ok) => {
            const x = new XMLHttpRequest();
            x.open('POST', 'index.php?url=servicios/guardar');
            x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            x.onload = () => ok(x.status);
            const fd = new FormData();
            fd.append('nombre', 'No debe guardarse');
            x.send(fd);
        });
        return r;
    });
    expect(estado).toBe(403);
});

test('una ruta de borrado no se ejecuta por GET', async ({ page }) => {
    await page.goto(ruta('servicios/eliminar/999999'));
    await expect(page.locator('#sessionFlashToast')).toContainText('solo puede ejecutarse desde el botón');
});

test('las cabeceras de seguridad están presentes', async ({ request }) => {
    const r = await request.get(ruta('login'));
    const h = r.headers();
    expect(h['content-security-policy']).toContain("default-src 'self'");
    expect(h['x-content-type-options']).toBe('nosniff');
    expect(h['x-frame-options']).toBe('SAMEORIGIN');
});

test('el logo solo acepta imágenes reales', async ({ page }) => {
    await page.goto(ruta('configuracion/hotel'));
    await page.setInputFiles('#inputLogoHotel', { name: 'logo.png', mimeType: 'image/png', buffer: Buffer.from('no es una imagen') });
    await page.locator('form[action*="guardarHotel"] [type="submit"]').click();
    await expect(page.locator('.invalid-feedback')).toContainText('PNG, JPG o WEBP');
});

// Regresión: parámetros de la URL que se imprimían sin escapar (XSS reflejado)
const XSS = '<img\tsrc=x\tonerror=window.__xss=1>';

test('la página 404 escapa el nombre de la ruta', async ({ page }) => {
    await page.goto(ruta(encodeURIComponent(XSS)));
    expect(await page.evaluate(() => window.__xss)).toBeUndefined();
    await expect(page.locator('code')).toContainText('<img');
});

test('Disponibilidad no imprime fechas inválidas de la URL', async ({ page }) => {
    await page.goto(ruta('reservas/disponibilidad') + '&entrada=' + encodeURIComponent(XSS)
        + '&salida=' + encodeURIComponent('2026-11-05" onmouseover="window.__xss=1'));
    expect(await page.evaluate(() => window.__xss)).toBeUndefined();
    expect(await page.locator('img[onerror], [onmouseover]').count()).toBe(0);
});
