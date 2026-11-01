// Fallos encontrados en la auditoría general: alertas que llevaban a un 404, moneda e IGV de
// Configuración que no se usaban, tablas que ensanchaban la página en el teléfono, el comprobante
// en modo oscuro y nombres accesibles.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('los enlaces de las alertas de la campana no llevan a páginas inexistentes', async ({ page, request }) => {
    await page.goto(ruta('home'));
    const enlaces = await page.locator('.alert-dropdown-menu a.dropdown-item[href]').evaluateAll(as => as.map(a => a.href));
    for (const href of enlaces) {
        const resp = await request.get(href);
        expect(resp.status(), href).toBeLessThan(400);
    }
});

test('el símbolo de moneda y el IGV salen de Configuración → Parámetros', async ({ page }) => {
    await page.goto(ruta('configuracion/parametros'));
    const moneda = await page.locator('#hotel_moneda_simbolo').inputValue();
    const igv = parseFloat(await page.locator('#hotel_igv_porcentaje').inputValue());

    await page.goto(ruta('pagos/comprobantes'));
    expect(await page.evaluate(() => window.moneda)).toBe(moneda);
    await page.goto(ruta('pagos/comprobante/5'));
    await expect(page.locator('#imprimible')).toContainText(`I.G.V. (${igv}%)`);
    await expect(page.locator('#imprimible')).toContainText(moneda + ' ');
});

test.describe('en el teléfono', () => {
    test.use({ viewport: { width: 375, height: 812 } });
    for (const pantalla of ['seguridad/usuarios', 'servicios', 'reservas', 'pagos']) {
        test(`${pantalla} no se desborda hacia los lados`, async ({ page }) => {
            await page.goto(ruta(pantalla));
            await page.waitForLoadState('networkidle');
            expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(375);
        });
    }
});

test('el comprobante sigue en blanco en modo oscuro', async ({ page }) => {
    await page.addInitScript(() => localStorage.setItem('lte-theme', 'dark'));
    await page.goto(ruta('pagos/comprobante/5'));
    const fondo = await page.locator('#imprimible tbody td').first().evaluate(td => getComputedStyle(td).backgroundColor);
    const [r, g, b, a = 1] = fondo.match(/[\d.]+/g).map(Number);
    expect(a === 0 || r + g + b > 600, fondo).toBe(true);
});

test('cada pantalla tiene un título principal y los botones de icono tienen nombre', async ({ page }) => {
    for (const pantalla of ['habitaciones/tipos', 'seguridad/roles', 'reservas']) {
        await page.goto(ruta(pantalla));
        await expect(page.locator('h1')).toHaveCount(1);
        const sinNombre = await page.locator('.app-main a.btn, .app-main button').evaluateAll(bs => bs
            .filter(b => b.offsetParent && !(b.getAttribute('aria-label') || b.getAttribute('title') || b.textContent.trim()))
            .map(b => b.outerHTML.slice(0, 80)));
        expect(sinNombre, pantalla).toEqual([]);
    }
    await expect(page.locator('.datatable-input').first()).toHaveAttribute('title', 'Buscar en la tabla');
});
