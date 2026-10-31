// Interruptor de estado en las tablas y barra fina de progreso. Cada prueba deja el
// estado como estaba.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

async function cambiarYEsperar(page, interruptor, mensaje) {
    await interruptor.click();
    await expect(page.locator('.toast').last()).toContainText(mensaje);
    await expect(interruptor).toBeEnabled();
}

test('servicios: el interruptor guarda el estado al momento', async ({ page }) => {
    await page.goto(ruta('servicios'));
    const interruptor = page.locator('[data-interruptor="servicios/cambiarEstado"]').first();
    const id = await interruptor.getAttribute('data-id');
    const estabaActivo = await interruptor.isChecked();
    const deNuevo = () => page.locator(`[data-interruptor="servicios/cambiarEstado"][data-id="${id}"]`);

    await cambiarYEsperar(page, interruptor, estabaActivo ? 'deshabilitado' : 'disponible');
    await page.reload();
    await expect(deNuevo()).toBeChecked({ checked: !estabaActivo });
    await expect(deNuevo().locator('xpath=..')).toContainText(estabaActivo ? 'Deshabilitado' : 'Disponible');

    // Dejarlo como estaba
    await cambiarYEsperar(page, deNuevo(), estabaActivo ? 'disponible' : 'deshabilitado');
    await page.reload();
    await expect(deNuevo()).toBeChecked({ checked: estabaActivo });
});

test('tipos de habitación: editar un tipo inactivo no lo vuelve a activar', async ({ page }) => {
    await page.goto(ruta('habitaciones/tipos'));
    const interruptor = page.locator('[data-interruptor="habitaciones/cambiarEstadoTipo"]').first();
    const id = await interruptor.getAttribute('data-id');
    const delTipo = () => page.locator(`[data-interruptor="habitaciones/cambiarEstadoTipo"][data-id="${id}"]`);
    await expect(interruptor).toBeChecked();

    await cambiarYEsperar(page, interruptor, 'inactivo');
    try {
        // Guardar el tipo desde su modal (que no tiene campo de estado)
        await delTipo().locator('xpath=ancestor::div[contains(@class,"card-body")]').locator('[data-editar]').click();
        await page.locator('#modalTipo [type="submit"]').click();
        await expect(page.locator('#sessionFlashToast')).toContainText('actualizado');
        await expect(delTipo()).not.toBeChecked();
    } finally {
        await page.goto(ruta('habitaciones/tipos'));
        if (!(await delTipo().isChecked())) await cambiarYEsperar(page, delTipo(), 'activo');
    }
    await page.reload();
    await expect(delTipo()).toBeChecked();
});

test('usuarios: no se puede desactivar al propio usuario ni al administrador principal', async ({ page }) => {
    await page.goto(ruta('seguridad/usuarios'));
    await expect(page.locator('[data-interruptor="seguridad/cambiarEstadoUsuario"][data-id="1"]')).toBeDisabled();

    // Aunque se envíe a mano, el servidor lo rechaza
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    const respuesta = await page.request.post(ruta('seguridad/cambiarEstadoUsuario'), {
        headers: { 'X-CSRF-Token': token },
        form: { id: '1', estado: '0' },
    }).then(r => r.json());
    expect(respuesta.success).toBe(false);
    await page.reload();
    await expect(page.locator('[data-interruptor="seguridad/cambiarEstadoUsuario"][data-id="1"]')).toBeChecked();
});

test('si el servidor rechaza el cambio, el interruptor vuelve a como estaba', async ({ page }) => {
    await page.goto(ruta('servicios'));
    const interruptor = page.locator('[data-interruptor="servicios/cambiarEstado"]').first();
    const estaba = await interruptor.isChecked();
    await page.route(/url=servicios\/cambiarEstado/, route => route.fulfill({
        contentType: 'application/json',
        body: JSON.stringify({ success: false, mensaje: 'Rechazado en la prueba' }),
    }));
    await interruptor.click();
    await expect(page.locator('.swal2-html-container')).toHaveText('Rechazado en la prueba');
    await expect(interruptor).toBeChecked({ checked: estaba });
});

test('barra fina de progreso mientras una petición tarda', async ({ page }) => {
    await page.goto(ruta('servicios'));
    let soltar;
    const espera = new Promise(r => { soltar = r; });
    await page.route(/url=servicios\/cambiarEstado/, async route => {
        await espera;
        await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: false, mensaje: 'x' }) });
    });
    await page.locator('[data-interruptor="servicios/cambiarEstado"]').first().click();
    await expect(page.locator('.barra-progreso')).toBeVisible();
    soltar();
    await expect(page.locator('.barra-progreso')).toBeHidden();
});
