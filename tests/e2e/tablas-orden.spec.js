// Las flechas para ordenar las columnas deben verse en ambos temas: toman el color del texto de
// la cabecera (en modo oscuro, claras) y la columna ordenada resalta su flecha.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const brillo = color => {
    const [r, g, b] = color.match(/\d+(\.\d+)?/g).map(Number);
    return (0.299 * r + 0.587 * g + 0.114 * b) / 255;
};

for (const tema of ['light', 'dark']) {
    test(`las flechas de orden se distinguen del fondo (${tema})`, async ({ page }) => {
        await page.addInitScript(t => localStorage.setItem('theme', t), tema);
        await page.goto(ruta('reservas'));
        await page.evaluate(t => document.documentElement.setAttribute('data-bs-theme', t), tema);
        const sorter = page.locator('.datatable-sorter').first();
        await expect(sorter).toBeVisible();

        const estilo = await sorter.evaluate(el => ({
            flecha: getComputedStyle(el, '::before').borderTopColor,
            opacidad: Number(getComputedStyle(el, '::before').opacity),
            fondo: getComputedStyle(document.body).backgroundColor,
        }));
        expect(Math.abs(brillo(estilo.flecha) - brillo(estilo.fondo))).toBeGreaterThan(0.4);
        expect(estilo.opacidad).toBeGreaterThanOrEqual(0.3);

        await sorter.click();
        const activa = await sorter.evaluate(el => Math.max(
            Number(getComputedStyle(el, '::before').opacity), Number(getComputedStyle(el, '::after').opacity)));
        expect(activa).toBe(1);
        await page.locator('table thead').first().screenshot({ path: `test-results/flechas-orden-${tema}.png` });
    });
}
