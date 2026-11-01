// Configuración → Métodos de pago: los métodos reales del sistema (Pago::METODOS) y lo cobrado
// con cada uno en el mes elegido. Antes era una lista fija con comisiones de ejemplo.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const METODOS = ['Efectivo', 'Tarjeta de Crédito', 'Tarjeta de Débito', 'Transferencia Bancaria', 'Yape/Plin'];
// "S/. 1,450.00" → 1450 (el punto de "S/." no es decimal)
const numero = (texto) => parseFloat((texto.match(/[\d,]+\.\d{2}/) || ['0'])[0].replace(/,/g, ''));

test('muestra los métodos que acepta el sistema, sin datos de ejemplo', async ({ page }) => {
    await page.goto(ruta('configuracion/metodosPago'));
    await expect(page.locator('#tablaMetodos tbody tr')).toHaveCount(METODOS.length);
    for (const metodo of METODOS) {
        await expect(page.locator(`#tablaMetodos tr[data-metodo="${metodo}"]`)).toBeVisible();
    }
    // La lista anterior inventaba comisiones y bancos
    await expect(page.locator('.app-main')).not.toContainText('3.5%');
    await expect(page.locator('.app-main')).not.toContainText('BCP / BBVA');
});

test('lo cobrado por método suma el total del mes elegido', async ({ page }) => {
    await page.goto(ruta('configuracion/metodosPago&mes=2026-10'));
    await expect(page.locator('input[name="mes"]')).toHaveValue('2026-10');
    await expect(page.locator('.cash-stat-label').first()).toContainText('octubre de 2026');

    const total = numero(await page.locator('[data-total-cobrado]').innerText());
    const filas = await page.locator('#tablaMetodos tbody td:nth-child(3)').evaluateAll(tds =>
        tds.map(td => td.childNodes[0].textContent));
    const suma = filas.map(numero).reduce((a, b) => a + b, 0);
    expect(total).toBeGreaterThan(0);
    expect(Math.round(suma * 100)).toBe(Math.round(total * 100));
});

test('un mes no válido en la dirección vuelve al mes actual', async ({ page }) => {
    await page.goto(ruta('configuracion/metodosPago&mes=2026-13'));
    const hoy = await page.evaluate(() => { const d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0'); });
    await expect(page.locator('input[name="mes"]')).toHaveValue(hoy);
});
