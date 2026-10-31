// Fase 1: envíos seguros. Un cobro no se registra dos veces, el usuario sabe cuándo no hay
// conexión, una sesión vencida lleva al login y lo que se estaba llenando no se pierde.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

async function nuevoServicio(page, nombre) {
    await page.getByRole('button', { name: /Nuevo Servicio/ }).click();
    await page.fill('#servNombre', nombre);
    await page.fill('#servPrecio', '1.50');
    await page.locator('#modalServicio [type="submit"]').click();
}

async function eliminarServicio(page, nombre) {
    await page.goto(ruta('servicios'));
    await page.fill('.datatable-input', nombre);
    const filas = page.locator('table tbody tr', { hasText: nombre });
    while (await filas.count() > 0) {
        await filas.first().locator('a[data-confirm]').click();
        await page.locator('.swal2-confirm').click();
        await expect(page.locator('#sessionFlashToast')).toContainText('eliminado');
        await page.fill('.datatable-input', nombre);
    }
}

test('reenviar tras un corte de red no registra la operación dos veces', async ({ page }) => {
    const nombre = 'ZZ Corte E2E ' + Date.now();
    await page.goto(ruta('servicios'));

    // 1er envío: el servidor lo registra, pero la respuesta nunca llega al navegador
    let cortar = true;
    await page.route(/url=servicios\/guardar/, async (route) => {
        if (!cortar) return route.continue();
        cortar = false;
        await route.fetch({ maxRedirects: 0 });
        await route.abort('connectionreset');
    });
    await nuevoServicio(page, nombre).catch(() => {});
    await page.waitForTimeout(500);

    // El recepcionista vuelve y envía exactamente lo mismo
    await page.goto(ruta('servicios'));
    await nuevoServicio(page, nombre);
    await expect(page.locator('#sessionFlashToast')).toContainText('ya se había registrado');
    await page.fill('.datatable-input', nombre);
    await expect(page.locator('table tbody tr', { hasText: nombre })).toHaveCount(1);

    await eliminarServicio(page, nombre);
});

test('repetir a propósito una operación ya confirmada sí se registra', async ({ page }) => {
    const nombre = 'ZZ Repetido E2E ' + Date.now();
    await page.goto(ruta('servicios'));
    await nuevoServicio(page, nombre);
    await expect(page.locator('#sessionFlashToast')).toContainText('registrado correctamente');
    await nuevoServicio(page, nombre);
    await expect(page.locator('#sessionFlashToast')).toContainText('registrado correctamente');
    await page.fill('.datatable-input', nombre);
    await expect(page.locator('table tbody tr', { hasText: nombre })).toHaveCount(2);

    await eliminarServicio(page, nombre);
});

test('el servidor responde igual a la misma clave de operación (fetch)', async ({ page }) => {
    await page.goto(ruta('habitaciones/estados'));
    const form = page.locator('form[action*="cambiarEstado"]').first();
    const id = await form.locator('input[name="id"]').inputValue();
    const estado = await form.locator('select[name="estado"]').inputValue(); // el mismo estado: no cambia nada
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');

    const enviar = (clave) => page.request.post(ruta('habitaciones/cambiarEstado'), {
        headers: { 'X-CSRF-Token': token, 'X-Operacion': clave },
        form: { id, estado, ajax: '1' },
    }).then(r => r.json());

    const clave = 'prueba-e2e-' + Date.now() + '-aaaa';
    const primera = await enviar(clave);
    expect(primera.success).toBe(true);
    expect(primera.repetida).toBeUndefined();
    expect((await enviar(clave)).repetida).toBe(true);
    expect((await enviar(clave + 'b')).repetida).toBeUndefined();
});

test('sin conexión: aviso arriba y el formulario no se envía', async ({ page, context }) => {
    await page.goto(ruta('servicios'));
    await context.setOffline(true);
    await expect(page.locator('.barra-sin-conexion')).toBeVisible();

    await nuevoServicio(page, 'ZZ Sin red');
    await expect(page.locator('.swal2-title')).toHaveText('Sin conexión');
    await expect(page).toHaveURL(/url=servicios$/);
    await page.locator('.swal2-confirm').click();

    await context.setOffline(false);
    await expect(page.locator('.barra-sin-conexion')).toBeHidden();
});

test('sesión vencida: una petición lleva al login en lugar de fallar en silencio', async ({ page, context }) => {
    await page.goto(ruta('servicios'));
    await context.clearCookies();
    await page.evaluate(() => { fetch(window.appUrl('servicios/apiGuardarConsumo'), { method: 'POST', body: new FormData() }); });
    await expect(page.locator('.swal2-title')).toHaveText('Tu sesión se cerró');
    await page.locator('.swal2-confirm').click();
    await expect(page).toHaveURL(/url=login/);
});

test('el POS de reserva recupera lo que se estaba llenando', async ({ page }) => {
    await page.goto(ruta('reservas/crear'));
    await page.fill('textarea[name="notas"]', 'Llega tarde, cuna en la habitación');
    await page.fill('input[name="adultos"]', '3');

    // Se va a otra pantalla y vuelve
    await page.goto(ruta('home'));
    await page.goto(ruta('reservas/crear'));
    await expect(page.locator('.aviso-borrador')).toContainText('Recuperamos');
    await expect(page.locator('textarea[name="notas"]')).toHaveValue('Llega tarde, cuna en la habitación');
    await expect(page.locator('input[name="adultos"]')).toHaveValue('3');

    // Descartar deja el formulario limpio
    await page.locator('.aviso-borrador button').click();
    await expect(page.locator('.aviso-borrador')).toHaveCount(0);
    await expect(page.locator('textarea[name="notas"]')).toHaveValue('');
});
