// Respaldo descargable (Configuración → Respaldo de datos): solo el Administrador.
// La restauración completa se verificó a mano: importar el .sql en otra base deja las tablas idénticas.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');
const fs = require('fs');

test('el administrador descarga un .zip con la base de datos y las fotos', async ({ page }) => {
    await page.goto(ruta('configuracion/respaldo'));
    await expect(page.locator('h1')).toContainText('Respaldo de Datos');

    const [descarga] = await Promise.all([
        page.waitForEvent('download'),
        page.locator('#formRespaldo [type="submit"]').click(),
    ]);
    expect(descarga.suggestedFilename()).toMatch(/^respaldo-[a-z0-9-]+-\d{4}-\d{2}-\d{2}-\d{4}\.zip$/);
    const contenido = fs.readFileSync(await descarga.path());
    expect(contenido.subarray(0, 2).toString()).toBe('PK'); // es un .zip
    for (const nombre of ['base-de-datos.sql', 'LEEME.txt', 'uploads/hotel/']) {
        expect(contenido.includes(Buffer.from(nombre)), nombre).toBe(true);
    }

    // Queda registrado cuándo y quién
    await page.goto(ruta('configuracion/respaldo'));
    await expect(page.locator('[data-estado-respaldo="al-dia"]')).toContainText('Último respaldo: hoy');
});

test('la recepcionista no ve ni puede descargar el respaldo', async ({ browser }) => {
    const contexto = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const page = await contexto.newPage();
    await page.goto(ruta('login'));
    await page.fill('input[name="email"]', process.env.E2E_RECEPCION_EMAIL || 'recepcion@hotel.com');
    await page.fill('input[name="password"]', process.env.E2E_RECEPCION_PASSWORD || 'admin123');
    await Promise.all([
        page.waitForURL(url => !url.href.includes('url=login')),
        page.locator('form[action*="login/autenticar"] [type="submit"]').click(),
    ]);

    await expect(page.locator('.app-sidebar')).not.toContainText('Respaldo');
    await page.goto(ruta('configuracion/respaldo'));
    await expect(page.locator('#sessionFlashToast')).toContainText('solo el Administrador');

    const estado = await page.evaluate(async (url) => {
        const r = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
        });
        return r.status;
    }, ruta('configuracion/descargarRespaldo'));
    expect(estado).toBe(403);
    await contexto.close();
});
