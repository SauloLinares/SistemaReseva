// Nueva Reserva → "+ Nuevo Huésped": al guardar, la ficha muestra el nombre del huésped
// recién creado (antes salía "undefined undefined"). Se simula la respuesta del servidor
// para no crear huéspedes de prueba en la base.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('el huésped creado desde el modal aparece con su nombre en la ficha', async ({ page }) => {
    await page.route('**/*huespedes/apiGuardar*', ruta => ruta.fulfill({
        contentType: 'application/json',
        body: JSON.stringify({
            success: true,
            mensaje: '¡Huésped registrado correctamente!',
            huesped: {
                id: 99999, nombre_completo: 'Rosa Prueba Vega', // como respondía antes: sin nombres ni apellidos
                tipo_documento: 'DNI', numero_documento: '40000001', telefono: '', correo: '', nacionalidad: 'Perú',
                texto_select: 'Rosa Prueba Vega (Doc: DNI 40000001 - Tel: S/N)',
            },
        }),
    }));

    await page.goto(ruta('reservas/crear'));
    await page.locator('[data-bs-target="#modalNuevoHuesped"]').click();
    const modal = page.locator('#modalNuevoHuesped');
    await expect(modal).toBeVisible();
    await modal.locator('#numero_documento').fill('40000001');
    await modal.locator('#nombres').fill('Rosa');
    await modal.locator('#apellidos').fill('Prueba Vega');
    await modal.locator('#formModalHuesped [type="submit"], #modalNuevoHuesped [type="submit"]').first().click();

    await expect(modal).toBeHidden();
    const ficha = page.locator('#cardPreviewHuesped');
    await expect(ficha).toBeVisible();
    await expect(ficha).toContainText('Rosa Prueba Vega');
    await expect(page.locator('body')).not.toContainText('undefined');
});
