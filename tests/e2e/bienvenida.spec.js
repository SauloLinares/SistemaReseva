// Bienvenida tras iniciar sesión: tarjeta "¡Acceso concedido!" con una barra que avanza durante
// unos segundos (no de golpe) y luego deja ver el panel. Solo en la primera pantalla.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

async function ingresar(browser, opciones = {}) {
    const contexto = await browser.newContext({ storageState: { cookies: [], origins: [] }, ...opciones });
    const page = await contexto.newPage();
    await page.goto(ruta('login'));
    await page.fill('input[name="email"]', process.env.E2E_EMAIL || 'admin@admin.com');
    await page.fill('input[name="password"]', process.env.E2E_PASSWORD || 'admin123');
    await page.locator('#btnIngresar').click();
    await page.waitForURL(url => !url.href.includes('url=login'));
    return { contexto, page };
}

test('al ingresar se ve la bienvenida y la barra avanza durante unos segundos', async ({ browser }) => {
    const { contexto, page } = await ingresar(browser);
    const capa = page.locator('#bienvenida');
    await expect(capa).toBeVisible();
    await expect(capa).toContainText('¡Acceso concedido!');
    await expect(capa).toContainText('Bienvenido(a)');

    // Al segundo todavía va por la mitad: el proceso se nota
    await page.waitForTimeout(1000);
    const aLaMitad = Number(await page.locator('[data-bienvenida-porcentaje]').innerText());
    expect(aLaMitad).toBeGreaterThan(10);
    expect(aLaMitad).toBeLessThan(80);

    // Termina, se desvanece y deja usar el panel
    await expect(capa).toHaveCount(0, { timeout: 6000 });
    await expect(page.locator('body')).not.toHaveClass(/bienvenida-activa/);

    // Solo la primera vez
    await page.reload();
    await expect(page.locator('#bienvenida')).toHaveCount(0);
    await contexto.close();
});

test('en el teléfono la tarjeta cabe en la pantalla', async ({ browser }) => {
    const { contexto, page } = await ingresar(browser, { viewport: { width: 375, height: 812 } });
    const tarjeta = page.locator('.bienvenida-tarjeta');
    await expect(tarjeta).toBeVisible();
    const caja = await tarjeta.boundingBox();
    expect(caja.x).toBeGreaterThanOrEqual(0);
    expect(caja.x + caja.width).toBeLessThanOrEqual(375);
    await page.screenshot({ path: 'test-results/bienvenida-movil.png' });
    await contexto.close();
});

test('con "reducir movimiento" activado los aros siguen girando', async ({ browser }) => {
    const { contexto, page } = await ingresar(browser, { reducedMotion: 'reduce' });
    const aro = page.locator('.bienvenida-aro-1');
    await expect(aro).toBeVisible();
    const estilo = await aro.evaluate(el => {
        const c = getComputedStyle(el);
        return { nombre: c.animationName, duracion: c.animationDuration, vueltas: c.animationIterationCount };
    });
    expect(estilo.nombre).toBe('bienvenida-gira');
    expect(parseFloat(estilo.duracion)).toBeGreaterThan(1);
    expect(estilo.vueltas).toBe('infinite');
    // El ángulo cambia con el tiempo: de verdad gira
    const angulo = () => aro.evaluate(el => getComputedStyle(el).transform);
    const antes = await angulo();
    await page.waitForTimeout(300);
    expect(await angulo()).not.toBe(antes);
    await contexto.close();
});
