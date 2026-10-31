// Fase 4: listados y reportes. Menú «⋯» de acciones, exportar a CSV, indicadores de los
// reportes y el aviso de lo pendiente en el Inicio.
const fs = require('fs');
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

// "S/. 1,750.00" -> 1750 (el punto de "S/." no es decimal)
const soles = (texto) => parseFloat(texto.replace(/[^\d.,]/g, '').replace(/^[.,]+/, '').replace(/,/g, '')) || 0;

test('menú «⋯» de una reserva: acciones del día y cancelar al final', async ({ page }) => {
    await page.goto(ruta('reservas'));
    const fila = page.locator('#tablaReservas tbody tr', { has: page.locator('.badge', { hasText: 'Confirmada' }) }).first();
    await fila.locator('[data-menu-acciones]').click();
    const menu = fila.locator('.dropdown-menu.show');
    await expect(menu).toBeVisible();
    await expect(menu.locator('.dropdown-item').first()).toContainText('Voucher');
    await expect(menu.locator('.dropdown-item').last()).toContainText('Cancelar reserva');
    await expect(menu.locator('.dropdown-item').last()).toHaveClass(/text-danger/);

    // Esc lo cierra (lo maneja Bootstrap) y el foco vuelve al botón
    await page.keyboard.press('Escape');
    await expect(menu).toBeHidden();

    // "Hacer check-in" lleva al check-in con la reserva elegida
    await fila.locator('[data-menu-acciones]').click();
    await fila.locator('.dropdown-item', { hasText: 'Hacer check-in' }).click();
    await expect(page).toHaveURL(/url=recepcion\/checkin&reserva_id=\d+/);
});

test('exportar reservas a CSV: todas las filas, títulos originales y sin la columna de acciones', async ({ page }) => {
    await page.goto(ruta('reservas'));
    const filas = await page.evaluate(() => document.getElementById('tablaReservas').tablaDatos.data.data.length);

    const [descarga] = await Promise.all([
        page.waitForEvent('download'),
        page.getByRole('button', { name: 'Exportar' }).click(),
    ]);
    expect(descarga.suggestedFilename()).toMatch(/^reservas-\d{4}-\d{2}-\d{2}\.csv$/);
    const csv = fs.readFileSync(await descarga.path(), 'utf8');
    expect(csv.charCodeAt(0)).toBe(0xfeff); // BOM: Excel respeta las tildes
    const lineas = csv.slice(1).split('\r\n');
    expect(lineas[0].split(';')[0]).toBe('Código');
    expect(lineas[0]).not.toMatch(/Acciones/i);
    expect(lineas).toHaveLength(filas + 1);
    await expect(page.locator('#sessionFlashToast, .toast').last()).toContainText('Se exportaron');
});

test('reporte de reservas: lo facturado no incluye las canceladas', async ({ page }) => {
    await page.goto(ruta('reportes/reservas'));
    const filas = page.locator('#tablaReporteReservas tbody tr');
    let esperado = 0;
    for (let i = 0; i < await filas.count(); i++) {
        const fila = filas.nth(i);
        if (!(await fila.textContent()).includes('Cancelada')) {
            esperado += soles(await fila.locator('td').last().textContent());
        }
    }
    const facturado = page.locator('.cash-stat', { hasText: 'Total facturado' }).locator('.cash-stat-valor');
    expect(soles(await facturado.textContent())).toBeCloseTo(esperado, 2);
});

test('ranking de habitaciones: barras y exportación', async ({ page }) => {
    await page.goto(ruta('reportes/ocupacion'));
    const ranking = page.locator('#tablaRankingHabitaciones');
    await expect(ranking.locator('.barra-ranking').first()).toBeVisible();
    // La primera es la de más recaudación del ranking: su barra es la referencia (100 %)
    await expect(ranking.locator('tbody tr:not(.fila-sin-actividad) .barra-ranking > span').first()).toHaveAttribute('style', /width: \d+%/);

    const [descarga] = await Promise.all([
        page.waitForEvent('download'),
        page.locator('[data-exportar="#tablaRankingHabitaciones"]').click(),
    ]);
    expect(descarga.suggestedFilename()).toMatch(/^ranking-habitaciones-/);
});

test('aviso del Inicio: se cierra hasta el día siguiente', async ({ page }) => {
    await page.goto(ruta('home'));
    const aviso = page.locator('#avisoInicio');
    test.skip(await aviso.count() === 0, 'Hoy no hay llegadas ni salidas pendientes');

    await expect(aviso).toBeVisible();
    await aviso.locator('[data-cerrar-aviso]').click();
    await expect(aviso).toBeHidden();
    await page.reload();
    await expect(page.locator('#avisoInicio')).toBeHidden();

    // Con otro día guardado vuelve a aparecer
    await page.evaluate(() => Object.keys(localStorage).filter(k => k.startsWith('aviso-inicio-cerrado')).forEach(k => localStorage.setItem(k, '2000-01-01')));
    await page.reload();
    await expect(page.locator('#avisoInicio')).toBeVisible();
});
