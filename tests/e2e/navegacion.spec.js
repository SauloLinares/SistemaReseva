// Enlaces que deben llevar al registro correcto con sus datos ya cargados.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('el botón Salida del panel abre el check-out de ese huésped', async ({ page }) => {
    await page.goto(ruta('home'));
    const boton = page.locator('a[title^="Check-out de Hab."]').first();
    const habitacion = (await boton.getAttribute('title')).replace('Check-out de Hab. ', '');
    await boton.click();
    await expect(page).toHaveURL(/recepcion\/checkout&id=\d+/);
    await expect(page.locator('#estancia_id option[selected]')).toContainText('Hab. ' + habitacion);
});

test('un hueco libre de la planificación abre el POS con habitación y fechas', async ({ page }) => {
    await page.goto(ruta('reservas/planificacion'));
    const hueco = page.locator('.plan-libre').nth(20);
    const href = await hueco.getAttribute('href');
    const params = new URL(href, 'http://x/').searchParams;
    await page.goto(href);
    await page.waitForLoadState('networkidle');

    await expect(page.locator('#fecha_entrada')).toHaveValue(params.get('entrada'));
    await expect(page.locator('#fecha_salida')).toHaveValue(params.get('salida'));
    await expect(page.locator(`#hab_${params.get('habitacion_id')}`)).toBeChecked();
});

test('Reservar desde Disponibilidad preselecciona la habitación', async ({ page }) => {
    await page.goto(ruta('reservas/disponibilidad'));
    const enlace = page.locator('a[href*="hab_id="]').first();
    const href = await enlace.getAttribute('href');
    const habId = new URL(href, 'http://x/').searchParams.get('hab_id');
    await enlace.click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator(`#hab_${habId}`)).toBeChecked();
});

test('el detalle del calendario muestra soles y fechas legibles', async ({ page }) => {
    await page.goto(ruta('reservas/calendario'));
    await page.locator('.fc-daygrid-event').first().click();
    await expect(page.locator('#mCalTotal')).toHaveText(/^S\/\. [\d,]+\.\d{2}$/);
    await expect(page.locator('#mCalEntrada')).toHaveText(/^\d{2}\/\d{2}\/\d{4}$/);
});

test('el modal de habitación ocupada enlaza a la reserva de esa habitación', async ({ page }) => {
    await page.goto(ruta('home'));
    const tarjeta = page.locator('.room-card-interactive[data-estado="ocupada"]').first();
    const reservaId = await tarjeta.getAttribute('data-reserva-id');
    await tarjeta.click();
    await expect(page.locator('#mBtnCargarConsumo')).toHaveAttribute('href', new RegExp('reserva_id=' + reservaId + '$'));
    await expect(page.locator('#mBtnFichaEstadia')).toHaveAttribute('href', new RegExp('reservas/editar/' + reservaId + '$'));
});
