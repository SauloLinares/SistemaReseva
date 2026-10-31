// Validación por campo: ante un error el formulario conserva lo escrito y marca el campo.
// Ninguna de estas pruebas guarda datos (el servidor rechaza el envío).
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

test('huésped con documento repetido conserva lo escrito y marca el campo', async ({ page }) => {
    await page.goto(ruta('huespedes'));
    // Un documento que ya existe, tomado de la primera fila del listado
    const docExistente = (await page.locator('table tbody tr').first().locator('td small').first().textContent()).trim().split(/\s+/).pop();

    await page.goto(ruta('huespedes/crear'));
    await page.fill('#numero_documento', docExistente);
    await page.fill('#nombres', 'Prueba Automática');
    await page.fill('#apellidos', 'No Guardar');
    await page.fill('#notas', 'Texto que no debe perderse');
    await page.locator('form[action*="huespedes/guardar"] [type="submit"]').click();

    await expect(page).toHaveURL(/huespedes\/crear/);
    await expect(page.locator('#numero_documento')).toHaveClass(/is-invalid/);
    await expect(page.locator('#error-numero_documento')).toContainText('ya pertenece a');
    await expect(page.locator('#nombres')).toHaveValue('Prueba Automática');
    await expect(page.locator('#notas')).toHaveValue('Texto que no debe perderse');
    await expect(page.locator('#numero_documento')).toBeFocused();
});

test('reserva con fechas inválidas conserva la habitación elegida', async ({ page }) => {
    await page.goto(ruta('reservas/crear'));
    await page.waitForLoadState('networkidle');
    const habitacion = page.locator('.check-hab:not(:disabled)').first();
    const valorHab = await habitacion.getAttribute('value');

    await page.evaluate((valor) => {
        const f = document.querySelector('form[action*="reservas/guardar"]');
        const opt = [...f.huesped_id.options].find(o => o.value);
        f.huesped_id.tomselect ? f.huesped_id.tomselect.setValue(opt.value) : (f.huesped_id.value = opt.value);
        const cb = f.querySelector(`.check-hab[value="${valor}"]`);
        cb.checked = true;
        cb.dispatchEvent(new Event('change', { bubbles: true }));
        f.fecha_salida.value = f.fecha_entrada.value; // salida = entrada: inválido
        // Saltar la validación del navegador para que decida el servidor
        HTMLFormElement.prototype.submit.call(f);
    }, valorHab);

    await page.waitForURL(/reservas\/crear/);
    await page.waitForLoadState('networkidle');
    await expect(page.locator('#fecha_salida')).toHaveClass(/is-invalid/);
    await expect(page.locator(`.check-hab[value="${valorHab}"]`)).toBeChecked();
});

test('reserva sin habitaciones muestra el aviso sobre la cuadrícula', async ({ page }) => {
    await page.goto(ruta('reservas/crear'));
    await page.waitForLoadState('networkidle');
    await page.evaluate(() => {
        const f = document.querySelector('form[action*="reservas/guardar"]');
        const opt = [...f.huesped_id.options].find(o => o.value);
        f.huesped_id.tomselect ? f.huesped_id.tomselect.setValue(opt.value) : (f.huesped_id.value = opt.value);
        HTMLFormElement.prototype.submit.call(f);
    });
    await page.waitForURL(/reservas\/crear/);
    await expect(page.locator('[data-error-para="habitaciones"] .alert')).toContainText('Elige al menos una habitación');
});
