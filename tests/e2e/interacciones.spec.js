// Interacciones que antes eran onclick/onchange en el HTML y ahora son escuchas en
// public/assets/js (la CSP ya no permite JavaScript inline). Ninguna guarda datos.
const { test, expect } = require('@playwright/test');
const { ruta } = require('./utilidades');

const botonesEditar = [
    { pantalla: 'servicios', campo: '#servNombre', titulo: '#modalServTitle' },
    { pantalla: 'habitaciones/tipos', campo: '#tipoNombre', titulo: '#modalTipoTitle' },
    { pantalla: 'seguridad/roles', campo: '#rolNombre', titulo: '#modalRolTitle' },
    { pantalla: 'seguridad/usuarios', campo: '#userNombre', titulo: '#modalUserTitle' },
];
for (const caso of botonesEditar) {
    test(`Editar en ${caso.pantalla} abre el modal con los datos`, async ({ page }) => {
        await page.goto(ruta(caso.pantalla));
        const boton = page.locator('[data-editar]').first();
        const registro = JSON.parse(await boton.getAttribute('data-editar'));
        await boton.click();
        await expect(page.locator(caso.campo)).toHaveValue(registro.nombre);
        await expect(page.locator(caso.titulo)).toContainText(registro.nombre);
    });
}

test('Pagos: el correlativo cambia con el tipo y el monto con la reserva', async ({ page }) => {
    await page.goto(ruta('pagos/crear'));
    const correlativos = JSON.parse(await page.locator('#comprobante_tipo').getAttribute('data-correlativos'));
    await page.selectOption('#comprobante_tipo', 'Factura');
    await expect(page.locator('#comprobante_numero')).toHaveValue(correlativos['Factura']);

    const opcion = page.locator('#selectReserva option[data-saldo]').first();
    const saldo = await opcion.getAttribute('data-saldo');
    await page.selectOption('#selectReserva', await opcion.getAttribute('value'));
    await expect(page.locator('#inputMonto')).toHaveValue(saldo);
});

test('Consumo: el total se recalcula al elegir servicio y cantidad', async ({ page }) => {
    await page.goto(ruta('servicios/consumo'));
    const opcion = page.locator('#servicio_id option[data-precio]').first();
    const precio = parseFloat(await opcion.getAttribute('data-precio'));
    await page.selectOption('#servicio_id', await opcion.getAttribute('value'));
    await page.fill('#inputCantidad', '3');
    await expect(page.locator('#displayTotalConsumo')).toHaveText('S/. ' + (precio * 3).toFixed(2));
});

test('Disponibilidad: el botón de 7 noches busca con ese rango', async ({ page }) => {
    await page.goto(ruta('reservas/disponibilidad'));
    // El botón cambia las fechas y envía el formulario de búsqueda
    await Promise.all([page.waitForURL(/url=reservas%2Fdisponibilidad|url=reservas\/disponibilidad.*entrada=/), page.locator('[data-rango="7"]').first().click()]);
    const params = new URL(page.url()).searchParams;
    const dias = Math.round((new Date(params.get('salida')) - new Date(params.get('entrada'))) / 86400000);
    expect(dias).toBe(7);
    await expect(page.locator('.page-title')).toContainText('Disponibilidad');
});

// Regresión: los formularios GET perdían la ruta (?url=...) y llevaban al Panel
const filtrosGet = [
    { pantalla: 'huespedes', llenar: (p) => p.fill('input[name="q"]', 'a'), titulo: 'Huéspedes' },
    { pantalla: 'reservas', llenar: (p) => p.selectOption('#filtroEstado', 'confirmada'), titulo: 'Reservas' },
    { pantalla: 'habitaciones', llenar: (p) => p.selectOption('select[name="estado"]', 'disponible'), titulo: 'Habitaciones' },
    { pantalla: 'reportes/ingresos', llenar: (p) => p.fill('#desde', '2026-10-01'), titulo: 'Ingresos' },
];
for (const caso of filtrosGet) {
    test(`el filtro de ${caso.pantalla} se queda en la misma pantalla`, async ({ page }) => {
        await page.goto(ruta(caso.pantalla));
        await caso.llenar(page);
        await Promise.all([
            page.waitForLoadState('load'),
            page.evaluate(() => document.querySelector('.app-content form[method="GET"], .app-content form[method="get"]').submit()),
        ]);
        await page.waitForLoadState('networkidle');
        await expect(page.locator('.page-title')).toContainText(caso.titulo);
    });
}

test('Permisos: elegir un rol navega a su configuración', async ({ page }) => {
    await page.goto(ruta('seguridad/permisos'));
    const valores = await page.locator('#rolConfigurar option').evaluateAll(os => os.map(o => o.value).filter(Boolean));
    const otro = valores[valores.length - 1];
    await Promise.all([page.waitForURL(new RegExp('rol_id=' + otro)), page.selectOption('#rolConfigurar', otro)]);
});

test('Ocupación: cambiar el mes recarga el reporte', async ({ page }) => {
    await page.goto(ruta('reportes/ocupacion'));
    await Promise.all([page.waitForURL(/mes=2026-09/), page.fill('input[name="mes"]', '2026-09')
        .then(() => page.locator('input[name="mes"]').dispatchEvent('change'))]);
});

test('Imprimir usa data-accion en lugar de onclick', async ({ page }) => {
    await page.goto(ruta('reportes/ingresos'));
    await page.evaluate(() => { window.__impreso = false; window.print = () => { window.__impreso = true; }; });
    await page.locator('[data-accion="imprimir"]').first().click();
    expect(await page.evaluate(() => window.__impreso)).toBe(true);
});

test('Caja: el cierre calcula la diferencia al escribir el conteo', async ({ page }) => {
    await page.goto(ruta('caja'));
    test.skip(!(await page.locator('#monto_real_efectivo').count()), 'No hay turno de caja abierto');
    await page.locator('[data-bs-target="#modalCierreCaja"]').first().click();
    const esperado = parseFloat(await page.locator('#monto_real_efectivo').getAttribute('data-esperado'));
    await page.fill('#monto_real_efectivo', String(esperado + 10));
    await page.locator('#monto_real_efectivo').dispatchEvent('input');
    await expect(page.locator('#textoDif')).toContainText('Sobrante');
});

test('Login: el botón del ojo muestra la contraseña', async ({ browser }) => {
    const contexto = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const pagina = await contexto.newPage();
    await pagina.goto(ruta('login'));
    await pagina.fill('#password', 'secreto');
    await pagina.click('#togglePasswordBtn');
    await expect(pagina.locator('#password')).toHaveAttribute('type', 'text');
    await contexto.close();
});

test('Login: en desarrollo, Autocompletar rellena las credenciales de prueba', async ({ browser }) => {
    // Las pruebas corren desde el propio equipo contra un host .test, que cuenta como desarrollo
    const contexto = await browser.newContext({ storageState: { cookies: [], origins: [] } });
    const pagina = await contexto.newPage();
    await pagina.goto(ruta('login'));
    await pagina.click('#btnAutocompletar');
    await expect(pagina.locator('#email')).not.toHaveValue('');
    await expect(pagina.locator('#password')).not.toHaveValue('');
    await contexto.close();
});
