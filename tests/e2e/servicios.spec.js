// Flujo completo con datos: crear un servicio desde el modal y eliminarlo con confirmación.
// Deja la base de datos como estaba.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('crear y eliminar un servicio', async ({ page }) => {
    const nombre = 'ZZ Prueba E2E ' + Date.now();
    await page.goto(ruta('servicios'));

    await page.getByRole('button', { name: /Nuevo Servicio/ }).click();
    await page.fill('#servNombre', nombre);
    await page.fill('#servPrecio', '1.50');
    await page.locator('#modalServicio [type="submit"]').click();
    await expect(page.locator('#sessionFlashToast')).toContainText('registrado');

    // Buscar el servicio en la tabla y eliminarlo (SweetAlert -> POST con token)
    await page.fill('.datatable-input', nombre);
    const fila = page.locator('table tbody tr', { hasText: nombre });
    await fila.locator('a[data-confirm]').click();
    await page.locator('.swal2-confirm').click();
    await expect(page.locator('#sessionFlashToast')).toContainText('eliminado');
    await page.fill('.datatable-input', nombre);
    await expect(page.locator('table tbody tr', { hasText: nombre })).toHaveCount(0);
});
